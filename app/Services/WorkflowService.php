<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\WorkflowServiceContract;
use App\DTOs\Workflow\ApprovalDecisionData;
use App\DTOs\Workflow\SubmitSppbData;
use App\Enums\ApproverStatus;
use App\Enums\SppbStatus;
use App\Enums\WorkflowCommandStatus;
use App\Enums\WorkflowInstanceStatus;
use App\Enums\WorkflowInstanceStepStatus;
use App\Exceptions\Workflow\InvalidSppbTransitionException;
use App\Exceptions\Workflow\StaleWorkflowCommandException;
use App\Exceptions\Workflow\UnauthorizedApprovalException;
use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use App\Models\AppSetting;
use App\Models\SppbHeader;
use App\Models\SppbStatusLog;
use App\Models\User;
use App\Models\WorkflowCommand;
use App\Models\WorkflowDelegation;
use App\Models\WorkflowInstance;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowStepApprover;
use App\Services\Workflow\ApproverResolver;
use App\Services\Workflow\WorkflowTemplateResolver;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;

final class WorkflowService implements WorkflowServiceContract
{
    public function __construct(
        private readonly WorkflowTemplateResolver $templateResolver,
        private readonly ApproverResolver $approverResolver,
    ) {}

    /**
     * Antrekan perintah submit SPPB (sinkron: validasi awal + simpan command).
     * Dispatch job dilakukan afterCommit.
     */
    public function queueSubmission(SubmitSppbData $data): WorkflowCommand
    {
        return DB::transaction(function () use ($data) {
            $header = SppbHeader::lockForUpdate()->findOrFail($data->sppbHeaderId);

            $currentStatus = SppbStatus::from($header->status);
            if (! in_array($currentStatus, [SppbStatus::DRAFT, SppbStatus::REJECTED])) {
                throw new InvalidSppbTransitionException($header->status, SppbStatus::SUBMISSION_QUEUED->value);
            }

            // Cek idempoten: command dengan UUID yang sama sudah ada?
            $existing = WorkflowCommand::where('command_uuid', $data->commandUuid)->first();
            if ($existing) {
                throw new StaleWorkflowCommandException;
            }

            if ($currentStatus === SppbStatus::REJECTED) {
                $header->revision_no += 1;
            }

            $command = WorkflowCommand::create([
                'command_uuid' => $data->commandUuid,
                'command_type' => 'SubmitSppb',
                'aggregate_type' => 'SppbHeader',
                'aggregate_id' => $data->sppbHeaderId,
                'actor_id' => $data->actorId,
                'payload' => json_encode(['correlation_id' => $data->correlationId]),
                'status' => WorkflowCommandStatus::QUEUED->value,
                'attempts' => 0,
            ]);

            $header->status = SppbStatus::SUBMISSION_QUEUED->value;
            $header->save();

            $this->writeStatusLog(
                $header,
                null,
                $currentStatus->value,
                SppbStatus::SUBMISSION_QUEUED->value,
                $data->actorId,
                $data->commandUuid,
                'SUBMIT_QUEUED',
            );

            // Eksekusi secara sinkron (karena tidak ada worker background)
            try {
                $this->generateWorkflow($data->sppbHeaderId, $data->correlationId ?? $data->commandUuid);

                $command->status = WorkflowCommandStatus::COMPLETED->value;
                $command->processed_at = now();
                $command->save();
            } catch (\Exception $e) {
                $command->status = WorkflowCommandStatus::FAILED->value;
                $command->error_message = $e->getMessage();
                $command->save();
                throw $e;
            }

            return $command;
        });
    }

    /**
     * Bangun workflow instance dari template (dipanggil dari Job).
     */
    public function generateWorkflow(int $sppbHeaderId, string $correlationId): WorkflowInstance
    {
        return DB::transaction(function () use ($sppbHeaderId) {
            $header = SppbHeader::with(['workflowInstances'])->lockForUpdate()->findOrFail($sppbHeaderId);

            $template = $this->templateResolver->resolve($header);
            $steps = $template->workflowSteps()->orderBy('sequence')->get();

            $instance = WorkflowInstance::create([
                'uuid' => (string) Uuid::uuid4(),
                'workflow_template_id' => $template->id,
                'template_version' => $template->version,
                'sppb_header_id' => $sppbHeaderId,
                'revision_no' => $header->revision_no,
                'status' => WorkflowInstanceStatus::IN_PROGRESS->value,
                'current_sequence' => null,
                'started_at' => now(),
            ]);

            foreach ($steps as $step) {
                $isFirst = $step->sequence === $steps->first()->sequence;
                WorkflowInstanceStep::create([
                    'workflow_instance_id' => $instance->id,
                    'workflow_step_id' => $step->id,
                    'sequence' => $step->sequence,
                    'code' => $step->code,
                    'name' => $step->name,
                    'approver_type' => $step->approver_type,
                    'approval_mode' => $step->approval_mode,
                    'minimum_approvals' => $step->minimum_approvals,
                    'sla_hours' => $step->sla_hours,
                    'status' => $isFirst
                        ? WorkflowInstanceStepStatus::PENDING->value
                        : WorkflowInstanceStepStatus::QUEUED->value,
                    'activated_at' => $isFirst ? now() : null,
                    'due_at' => $isFirst && $step->sla_hours ? now()->addHours(min((int) $step->sla_hours, 8760)) : null,
                    'lock_version' => 0,
                ]);
            }

            // Resolve dan insert approvers untuk step pertama
            $firstStep = $instance->workflowInstanceSteps()
                ->where('status', WorkflowInstanceStepStatus::PENDING->value)
                ->first();

            if ($firstStep) {
                $workflowStep = $steps->where('sequence', $firstStep->sequence)->first();
                $approvers = $this->approverResolver->resolve($workflowStep, $header);
                foreach ($approvers as $approver) {
                    WorkflowStepApprover::create([
                        'workflow_instance_step_id' => $firstStep->id,
                        'approver_id' => $approver->id,
                        'status' => ApproverStatus::PENDING->value,
                    ]);

                    $this->sendNotification(
                        $approver,
                        'Persetujuan Baru',
                        "SPPB dengan nomor {$header->document_number} (Pemohon: {$header->requester?->name}) memerlukan persetujuan/verifikasi Anda.",
                        SppbHeaderResource::getUrl('view', ['record' => $header]),
                        'approval_requested',
                        [
                            'document_number' => $header->document_number,
                            'requester_name' => $header->requester?->name,
                            'url' => SppbHeaderResource::getUrl('view', ['record' => $header]),
                        ]
                    );
                }
                $header->current_workflow_instance_id = $instance->id;
                $header->current_step_sequence = $firstStep->sequence;
                $header->current_approver_id = $approvers->first()?->id;

                $isBat = str_contains(strtoupper($firstStep->code), 'BAT') || str_contains(strtoupper($firstStep->name), 'BAT');
                if ($isBat) {
                    $header->status = SppbStatus::WAITING_VERIFICATION_BAT->value;
                } else {
                    $header->status = SppbStatus::WAITING_APPROVAL->value;
                }
            } else {
                $header->status = SppbStatus::WAITING_APPROVAL->value;
            }

            $header->submitted_at = $header->submitted_at ?? now();
            $header->save();

            $this->writeStatusLog(
                $header,
                $instance->id,
                SppbStatus::SUBMISSION_QUEUED->value,
                $header->status,
                null,
                null,
                'WORKFLOW_GENERATED',
            );

            $this->sendNotification(
                $header->requester,
                'Pengajuan SPPB Berhasil',
                "SPPB dengan nomor {$header->document_number} telah berhasil diajukan dan sedang menunggu persetujuan.",
                SppbHeaderResource::getUrl('view', ['record' => $header]),
                'sppb_created',
                [
                    'document_number' => $header->document_number,
                    'requester_name' => $header->requester?->name,
                    'url' => SppbHeaderResource::getUrl('view', ['record' => $header]),
                ]
            );

            return $instance;
        });
    }

    /**
     * Antrekan keputusan approval (validasi awal + simpan command).
     */
    public function queueApproval(ApprovalDecisionData $data): WorkflowCommand
    {
        return DB::transaction(function () use ($data) {
            // Cek idempoten
            $existing = WorkflowCommand::where('command_uuid', $data->commandUuid)->first();
            if ($existing) {
                throw new StaleWorkflowCommandException;
            }

            // Validasi step masih PENDING
            $step = WorkflowInstanceStep::lockForUpdate()->findOrFail($data->workflowInstanceStepId);
            if (! WorkflowInstanceStepStatus::from($step->status)->isActive()) {
                throw new InvalidSppbTransitionException($step->status, $data->decision);
            }

            // Validasi aktor adalah approver yang ditugaskan
            $assignment = WorkflowStepApprover::where('workflow_instance_step_id', $step->id)
                ->where('approver_id', $data->actorId)
                ->where('status', ApproverStatus::PENDING->value)
                ->first();

            if (! $assignment) {
                // Cek delegasi (multi-hop & circular safe)
                $delegatorIds = $this->resolveDelegatorIds($data->actorId);

                if (empty($delegatorIds)) {
                    throw new UnauthorizedApprovalException;
                }

                $assignment = WorkflowStepApprover::where('workflow_instance_step_id', $step->id)
                    ->whereIn('approver_id', $delegatorIds)
                    ->where('status', ApproverStatus::PENDING->value)
                    ->first();

                if (! $assignment) {
                    throw new UnauthorizedApprovalException;
                }
            }

            $command = WorkflowCommand::create([
                'command_uuid' => $data->commandUuid,
                'command_type' => 'ApprovalDecision',
                'aggregate_type' => 'WorkflowInstanceStep',
                'aggregate_id' => $data->workflowInstanceStepId,
                'actor_id' => $data->actorId,
                'payload' => json_encode([
                    'decision' => $data->decision,
                    'remarks' => $data->remarks,
                    'delegated_from_id' => $data->delegatedFromId,
                    'correlation_id' => $data->correlationId,
                    'require_plant_manager' => $data->requirePlantManager,
                ]),
                'status' => WorkflowCommandStatus::QUEUED->value,
                'attempts' => 0,
            ]);

            // Eksekusi secara sinkron (karena tidak ada worker background)
            try {
                $decision = strtolower($data->decision);
                if ($decision === 'approve') {
                    $this->approve($data);
                } elseif ($decision === 'reject') {
                    $this->reject($data);
                } elseif ($decision === 'revision') {
                    $this->requestRevision($data);
                } else {
                    throw new \InvalidArgumentException("Invalid decision: {$data->decision}");
                }

                $command->status = WorkflowCommandStatus::COMPLETED->value;
                $command->processed_at = now();
                $command->save();
            } catch (\Exception $e) {
                $command->status = WorkflowCommandStatus::FAILED->value;
                $command->error_message = $e->getMessage();
                $command->save();
                throw $e;
            }

            return $command;
        });
    }

    /**
     * Proses approve (dipanggil dari Job).
     */
    public function approve(ApprovalDecisionData $data): WorkflowInstanceStep
    {
        return DB::transaction(function () use ($data) {
            $step = WorkflowInstanceStep::with(['workflowInstance.sppbHeader'])
                ->lockForUpdate()
                ->findOrFail($data->workflowInstanceStepId);

            $instance = $step->workflowInstance()->lockForUpdate()->first();
            $header = $instance->sppbHeader()->lockForUpdate()->first();

            $targetApproverIds = array_merge([$data->actorId], $this->resolveDelegatorIds($data->actorId));

            // Catat keputusan approver
            $assignment = WorkflowStepApprover::where('workflow_instance_step_id', $step->id)
                ->whereIn('approver_id', $targetApproverIds)
                ->where('status', ApproverStatus::PENDING->value)
                ->first();

            $actedApproverId = $assignment ? $assignment->approver_id : $data->actorId;

            if ($assignment) {
                $assignment->update([
                    'status' => ApproverStatus::APPROVED->value,
                    'acted_at' => now(),
                    'remarks' => $data->remarks,
                ]);
            }

            // Cancel sibling approvers (jika mode ANY)
            if ($step->approval_mode === 'ANY') {
                WorkflowStepApprover::where('workflow_instance_step_id', $step->id)
                    ->where('approver_id', '!=', $actedApproverId)
                    ->where('status', ApproverStatus::PENDING->value)
                    ->update(['status' => ApproverStatus::CANCELLED->value, 'acted_at' => now()]);
            }

            // Cek apakah threshold quorum/all terpenuhi
            $approvedCount = WorkflowStepApprover::where('workflow_instance_step_id', $step->id)
                ->where('status', ApproverStatus::APPROVED->value)
                ->count();

            $threshold = $step->approval_mode === 'ALL'
                ? WorkflowStepApprover::where('workflow_instance_step_id', $step->id)->count()
                : (int) $step->minimum_approvals;

            if ($approvedCount >= $threshold) {
                // Step selesai
                $step->status = WorkflowInstanceStepStatus::APPROVED->value;
                $step->acted_at = now();
                $step->acted_by_id = $data->actorId;
                $step->save();

                // Cari step berikutnya
                $nextStep = null;
                if ($data->requirePlantManager !== false) {
                    $nextStep = WorkflowInstanceStep::where('workflow_instance_id', $instance->id)
                        ->where('sequence', '>', $step->sequence)
                        ->orderBy('sequence')
                        ->first();
                } else {
                    // Cancel any remaining queued steps since manager opted to make this final
                    WorkflowInstanceStep::where('workflow_instance_id', $instance->id)
                        ->where('sequence', '>', $step->sequence)
                        ->where('status', WorkflowInstanceStepStatus::QUEUED->value)
                        ->update(['status' => WorkflowInstanceStepStatus::CANCELLED->value]);
                }

                if ($nextStep) {
                    // Aktifkan step berikutnya
                    $nextStep->status = WorkflowInstanceStepStatus::PENDING->value;
                    $nextStep->activated_at = now();
                    if ($nextStep->sla_hours) {
                        $slaHours = min((int) $nextStep->sla_hours, 8760);
                        $nextStep->due_at = now()->addHours($slaHours);
                    }
                    $nextStep->save();

                    // Resolve approvers untuk step berikutnya
                    $workflowStep = $nextStep->workflowStep;
                    if ($workflowStep) {
                        $approvers = $this->approverResolver->resolve($workflowStep, $header);
                        foreach ($approvers as $approver) {
                            WorkflowStepApprover::firstOrCreate(
                                [
                                    'workflow_instance_step_id' => $nextStep->id,
                                    'approver_id' => $approver->id,
                                ],
                                ['status' => ApproverStatus::PENDING->value],
                            );

                            $this->sendNotification(
                                $approver,
                                'Persetujuan Baru',
                                "SPPB dengan nomor {$header->document_number} (Pemohon: {$header->requester?->name}) memerlukan persetujuan/verifikasi Anda.",
                                SppbHeaderResource::getUrl('view', ['record' => $header]),
                                'approval_requested',
                                [
                                    'document_number' => $header->document_number,
                                    'requester_name' => $header->requester?->name,
                                    'url' => SppbHeaderResource::getUrl('view', ['record' => $header]),
                                ]
                            );
                        }
                        $header->current_step_sequence = $nextStep->sequence;
                        $header->current_approver_id = $approvers->first()?->id;
                    }

                    // Dynamically map SPPB header status
                    $upperCode = strtoupper($nextStep->code ?? '');
                    $upperName = strtoupper($nextStep->name ?? '');

                    $isManager = $upperCode === 'MAN'
                        || $upperCode === 'MGR'
                        || $upperCode === 'MANAGER'
                        || str_starts_with($upperCode, 'MAN-')
                        || str_starts_with($upperCode, 'MGR-')
                        || str_starts_with($upperCode, 'MANAGER-')
                        || str_contains($upperName, 'MANAGER')
                        || str_contains($upperName, 'MGR');

                    $isBat = str_contains($upperCode, 'BAT')
                        || str_contains($upperName, 'BAT');

                    if ($isManager) {
                        $header->status = SppbStatus::WAITING_APPROVAL_MANAGER->value;
                    } elseif ($isBat) {
                        $header->status = SppbStatus::WAITING_VERIFICATION_BAT->value;
                    } else {
                        $header->status = SppbStatus::WAITING_APPROVAL->value;
                    }

                    $this->writeStatusLog(
                        $header,
                        $instance->id,
                        null,
                        $header->status,
                        $data->actorId,
                        $data->commandUuid,
                        'STEP_APPROVED',
                        $data->remarks,
                    );

                    $this->sendNotification(
                        $header->requester,
                        'Update Persetujuan SPPB',
                        "Tahap persetujuan SPPB dengan nomor {$header->document_number} telah disetujui dan berlanjut ke tahap berikutnya.",
                        SppbHeaderResource::getUrl('view', ['record' => $header]),
                        'approval_stage_updated',
                        [
                            'document_number' => $header->document_number,
                            'requester_name' => $header->requester?->name,
                            'url' => SppbHeaderResource::getUrl('view', ['record' => $header]),
                        ]
                    );
                } else {
                    // Ini step final — SPPB disetujui!
                    $instance->status = WorkflowInstanceStatus::APPROVED->value;
                    $instance->finished_at = now();
                    $instance->save();

                    $header->status = SppbStatus::APPROVED->value;
                    $header->approved_at = now();
                    $header->current_approver_id = null;

                    $this->writeStatusLog(
                        $header,
                        $instance->id,
                        SppbStatus::WAITING_APPROVAL->value,
                        SppbStatus::APPROVED->value,
                        $data->actorId,
                        $data->commandUuid,
                        'SPPB_APPROVED',
                        $data->remarks,
                    );

                    $this->sendNotification(
                        $header->requester,
                        'SPPB Disetujui',
                        "SPPB dengan nomor {$header->document_number} telah disetujui sepenuhnya.",
                        SppbHeaderResource::getUrl('view', ['record' => $header]),
                        'sppb_approved',
                        [
                            'document_number' => $header->document_number,
                            'requester_name' => $header->requester?->name,
                            'url' => SppbHeaderResource::getUrl('view', ['record' => $header]),
                        ]
                    );
                }

                $header->save();
            }

            return $step->refresh();
        });
    }

    /**
     * Proses reject (dipanggil dari Job).
     */
    public function reject(ApprovalDecisionData $data): WorkflowInstanceStep
    {
        return DB::transaction(function () use ($data) {
            $step = WorkflowInstanceStep::with('workflowInstance.sppbHeader')
                ->lockForUpdate()
                ->findOrFail($data->workflowInstanceStepId);

            $instance = $step->workflowInstance()->lockForUpdate()->first();
            $header = $instance->sppbHeader()->lockForUpdate()->first();

            $targetApproverIds = array_merge([$data->actorId], $this->resolveDelegatorIds($data->actorId));

            $assignment = WorkflowStepApprover::where('workflow_instance_step_id', $step->id)
                ->whereIn('approver_id', $targetApproverIds)
                ->where('status', ApproverStatus::PENDING->value)
                ->first();

            $actedApproverId = $assignment ? $assignment->approver_id : $data->actorId;

            if ($assignment) {
                $assignment->update([
                    'status' => ApproverStatus::REJECTED->value,
                    'acted_at' => now(),
                    'remarks' => $data->remarks,
                ]);
            }

            // Cancel semua approver lain di step ini
            WorkflowStepApprover::where('workflow_instance_step_id', $step->id)
                ->where('approver_id', '!=', $actedApproverId)
                ->where('status', ApproverStatus::PENDING->value)
                ->update(['status' => ApproverStatus::CANCELLED->value, 'acted_at' => now()]);

            // Cancel step-step berikutnya
            WorkflowInstanceStep::where('workflow_instance_id', $instance->id)
                ->where('sequence', '>', $step->sequence)
                ->update(['status' => WorkflowInstanceStepStatus::CANCELLED->value]);

            $step->status = WorkflowInstanceStepStatus::REJECTED->value;
            $step->acted_at = now();
            $step->acted_by_id = $data->actorId;
            $step->remarks = $data->remarks;
            $step->save();

            $instance->status = WorkflowInstanceStatus::REJECTED->value;
            $instance->finished_at = now();
            $instance->save();

            $header->status = SppbStatus::REJECTED->value;
            $header->rejected_at = now();
            $header->rejected_reason = $data->remarks;
            $header->current_approver_id = null;
            $header->save();

            $this->writeStatusLog(
                $header,
                $instance->id,
                SppbStatus::WAITING_APPROVAL->value,
                SppbStatus::REJECTED->value,
                $data->actorId,
                $data->commandUuid,
                'SPPB_REJECTED',
                $data->remarks,
            );

            $actor = User::find($data->actorId);
            $actorName = $actor ? $actor->name : 'Approver';
            $this->sendNotification(
                $header->requester,
                'SPPB Ditolak',
                "SPPB dengan nomor {$header->document_number} telah ditolak oleh {$actorName}. Alasan: {$data->remarks}",
                SppbHeaderResource::getUrl('view', ['record' => $header]),
                'sppb_rejected_revised',
                [
                    'document_number' => $header->document_number,
                    'requester_name' => $header->requester?->name,
                    'url' => SppbHeaderResource::getUrl('view', ['record' => $header]),
                    'notes' => $data->remarks,
                    'actor_name' => $actorName,
                ]
            );

            return $step->refresh();
        });
    }

    /**
     * Proses minta revisi (dipanggil dari Job).
     */
    public function requestRevision(ApprovalDecisionData $data): WorkflowInstanceStep
    {
        return DB::transaction(function () use ($data) {
            $step = WorkflowInstanceStep::with('workflowInstance.sppbHeader')
                ->lockForUpdate()
                ->findOrFail($data->workflowInstanceStepId);

            $instance = $step->workflowInstance()->lockForUpdate()->first();
            $header = $instance->sppbHeader()->lockForUpdate()->first();

            WorkflowStepApprover::where('workflow_instance_step_id', $step->id)
                ->where('approver_id', $data->actorId)
                ->update([
                    'status' => ApproverStatus::REVISION_REQUESTED->value,
                    'acted_at' => now(),
                    'remarks' => $data->remarks,
                ]);

            WorkflowStepApprover::where('workflow_instance_step_id', $step->id)
                ->where('approver_id', '!=', $data->actorId)
                ->where('status', ApproverStatus::PENDING->value)
                ->update(['status' => ApproverStatus::CANCELLED->value, 'acted_at' => now()]);

            WorkflowInstanceStep::where('workflow_instance_id', $instance->id)
                ->where('sequence', '>', $step->sequence)
                ->update(['status' => WorkflowInstanceStepStatus::CANCELLED->value]);

            $step->status = WorkflowInstanceStepStatus::REVISION_REQUESTED->value;
            $step->acted_at = now();
            $step->acted_by_id = $data->actorId;
            $step->remarks = $data->remarks;
            $step->save();

            $instance->status = WorkflowInstanceStatus::REVISION_REQUIRED->value;
            $instance->finished_at = now();
            $instance->save();

            $header->status = SppbStatus::REVISION_REQUIRED->value;
            $header->current_approver_id = null;
            $header->save();

            $this->writeStatusLog(
                $header,
                $instance->id,
                SppbStatus::WAITING_APPROVAL->value,
                SppbStatus::REVISION_REQUIRED->value,
                $data->actorId,
                $data->commandUuid,
                'REVISION_REQUESTED',
                $data->remarks,
            );

            $actor = User::find($data->actorId);
            $actorName = $actor ? $actor->name : 'Approver';
            $this->sendNotification(
                $header->requester,
                'Permintaan Revisi SPPB',
                "SPPB dengan nomor {$header->document_number} dikembalikan untuk direvisi oleh {$actorName}. Catatan: {$data->remarks}",
                SppbHeaderResource::getUrl('view', ['record' => $header]),
                'sppb_rejected_revised',
                [
                    'document_number' => $header->document_number,
                    'requester_name' => $header->requester?->name,
                    'url' => SppbHeaderResource::getUrl('view', ['record' => $header]),
                    'notes' => $data->remarks,
                    'actor_name' => $actorName,
                ]
            );

            return $step->refresh();
        });
    }

    /**
     * Batalkan workflow aktif.
     */
    public function cancelWorkflow(int $sppbHeaderId, int $actorId, string $reason): void
    {
        DB::transaction(function () use ($sppbHeaderId, $actorId, $reason) {
            $header = SppbHeader::lockForUpdate()->findOrFail($sppbHeaderId);

            if (! SppbStatus::from($header->status)->isCancellable()) {
                throw new InvalidSppbTransitionException($header->status, SppbStatus::CANCELLED->value);
            }

            // Batalkan workflow instance aktif jika ada
            if ($header->current_workflow_instance_id) {
                $instance = WorkflowInstance::lockForUpdate()->find($header->current_workflow_instance_id);
                if ($instance) {
                    $terminalValues = array_map(
                        fn (WorkflowInstanceStepStatus $s) => $s->value,
                        array_filter(
                            WorkflowInstanceStepStatus::cases(),
                            fn (WorkflowInstanceStepStatus $s) => $s->isTerminal(),
                        ),
                    );

                    WorkflowInstanceStep::where('workflow_instance_id', $instance->id)
                        ->whereNotIn('status', $terminalValues)
                        ->update(['status' => WorkflowInstanceStepStatus::CANCELLED->value]);

                    $instance->status = WorkflowInstanceStatus::CANCELLED->value;
                    $instance->finished_at = now();
                    $instance->save();
                }
            }

            $fromStatus = $header->status;
            $header->status = SppbStatus::CANCELLED->value;
            $header->cancelled_at = now();
            $header->cancelled_reason = $reason;
            $header->save();

            $this->writeStatusLog(
                $header,
                $header->current_workflow_instance_id,
                $fromStatus,
                SppbStatus::CANCELLED->value,
                $actorId,
                null,
                'SPPB_CANCELLED',
                $reason,
            );
        });
    }

    private function writeStatusLog(
        SppbHeader $header,
        ?int $workflowInstanceId,
        ?string $fromStatus,
        string $toStatus,
        ?int $actorId,
        ?string $commandUuid,
        string $action,
        ?string $remarks = null,
    ): void {
        SppbStatusLog::create([
            'sppb_header_id' => $header->id,
            'workflow_instance_id' => $workflowInstanceId,
            'actor_id' => $actorId,
            'command_uuid' => $commandUuid,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'remarks' => $remarks,
            'logged_at' => now(),
        ]);
    }

    /**
     * Resolves all valid delegator IDs for a given actor ID recursively.
     * Guards against circular delegation loops and limits recursion depth.
     *
     * @return array<int, int>
     */
    public function resolveDelegatorIds(int $actorId, int $maxDepth = 5): array
    {
        $delegatorIds = [];
        $currentDelegateIds = [$actorId];
        $visited = [$actorId => true];

        for ($depth = 0; $depth < $maxDepth; $depth++) {
            if (empty($currentDelegateIds)) {
                break;
            }

            $delegations = WorkflowDelegation::whereIn('delegate_id', $currentDelegateIds)
                ->where('is_active', true)
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>=', now())
                ->get();

            if ($delegations->isEmpty()) {
                break;
            }

            $nextDelegateIds = [];
            foreach ($delegations as $delegation) {
                $delegatorId = (int) $delegation->delegator_id;
                if (! isset($visited[$delegatorId])) {
                    $visited[$delegatorId] = true;
                    $delegatorIds[] = $delegatorId;
                    $nextDelegateIds[] = $delegatorId;
                }
            }

            $currentDelegateIds = $nextDelegateIds;
        }

        return array_unique($delegatorIds);
    }

    public function sendNotification(?User $user, string $title, string $body, string $url, ?string $eventType = null, array $context = []): void
    {
        if (! $user) {
            return;
        }

        // Resolve templates if eventType is set
        $emailSubject = $title;
        $emailBody = $body;
        $waBody = $body;

        if ($eventType) {
            $tplEmailSubject = AppSetting::get('notify_template_'.$eventType.'_email_subject');
            $tplEmailBody = AppSetting::get('notify_template_'.$eventType.'_email_body');
            $tplWaBody = AppSetting::get('notify_template_'.$eventType.'_wa_body');

            $placeholders = array_merge([
                'document_number' => '',
                'requester_name' => $user->name ?? '',
                'url' => $url,
                'notes' => '',
                'actor_name' => '',
            ], $context);

            $replaceKeys = [];
            $replaceVals = [];
            foreach ($placeholders as $k => $v) {
                $replaceKeys[] = '{'.$k.'}';
                $replaceVals[] = (string) ($v ?? '');
            }

            if (! empty($tplEmailSubject)) {
                $emailSubject = str_replace($replaceKeys, $replaceVals, $tplEmailSubject);
            }
            if (! empty($tplEmailBody)) {
                $emailBody = str_replace($replaceKeys, $replaceVals, $tplEmailBody);
            }
            if (! empty($tplWaBody)) {
                $waBody = str_replace($replaceKeys, $replaceVals, $tplWaBody);
            }
        }

        // 1. In-App System Notification
        $systemEnabled = (bool) AppSetting::get('notify_system_enabled', true);
        $eventAllowed = true;

        if ($eventType) {
            $eventSettingKey = 'notify_event_'.$eventType;
            $eventAllowed = (bool) AppSetting::get($eventSettingKey, true);
        }

        if ($systemEnabled && $eventAllowed) {
            try {
                Notification::make()
                    ->title($emailSubject)
                    ->body($emailBody)
                    ->icon('heroicon-o-document-text')
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Detail')
                            ->url($url),
                    ])
                    ->sendToDatabase($user, isEventDispatched: true);
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim notifikasi sistem: '.$e->getMessage());
            }
        }

        // 2. Email Notification
        $emailEnabled = (bool) AppSetting::get('notify_email_enabled', false);
        if ($emailEnabled && ! empty($user->email)) {
            try {
                $driver = (string) AppSetting::get('mail_driver', 'smtp');
                $mailFromAddress = (string) AppSetting::get('mail_from_address', 'no-reply@esppb.perusahaan.com');
                $mailFromName = (string) AppSetting::get('mail_from_name', 'E-SPPB Enterprise');

                // Dynamic configuration
                config([
                    'mail.default' => $driver,
                    'mail.from.address' => $mailFromAddress,
                    'mail.from.name' => $mailFromName,
                ]);

                if ($driver === 'smtp') {
                    config([
                        'mail.mailers.smtp.host' => AppSetting::get('mail_host', '127.0.0.1'),
                        'mail.mailers.smtp.port' => (int) AppSetting::get('mail_port', 1025),
                        'mail.mailers.smtp.username' => AppSetting::get('mail_username', ''),
                        'mail.mailers.smtp.password' => AppSetting::get('mail_password', ''),
                    ]);
                } elseif ($driver === 'resend') {
                    config([
                        'resend.api_key' => AppSetting::get('resend_api_key', ''),
                    ]);
                }

                Mail::purge();

                Mail::raw("{$emailBody}\n\nLihat detail: {$url}", function ($message) use ($user, $emailSubject, $mailFromAddress, $mailFromName) {
                    $message->to($user->email)
                        ->from($mailFromAddress, $mailFromName)
                        ->subject($emailSubject);
                });
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email notifikasi: '.$e->getMessage());
            }
        }

        // 3. WhatsApp Notification
        $waEnabled = (bool) AppSetting::get('notify_wa_enabled', false);
        if ($waEnabled && ! empty($user->phone)) {
            try {
                $whatsAppService = app(WhatsAppService::class);
                $whatsAppService->sendMessage($user->phone, $waBody);
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim notifikasi WhatsApp: '.$e->getMessage());
            }
        }
    }
}
