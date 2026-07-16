<?php

declare(strict_types=1);

namespace App\Filament\Resources\MyApprovals\Pages;

use App\Contracts\WorkflowServiceContract;
use App\DTOs\Workflow\ApprovalDecisionData;
use App\Enums\ApproverStatus;
use App\Filament\Resources\MyApprovals\MyApprovalResource;
use App\Models\WorkflowStepApprover;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;

class ViewMyApproval extends ViewRecord
{
    protected static string $resource = MyApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Setujui')
                ->color('success')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->modalHeading('Setujui Dokumen')
                ->modalDescription('Apakah Anda yakin ingin menyetujui dokumen ini?')
                ->form([
                    Textarea::make('remarks')
                        ->label('Catatan (Opsional)')
                        ->maxLength(255),
                ])
                ->visible(function () {
                    if (! $this->record) {
                        return false;
                    }

                    return WorkflowStepApprover::where('approver_id', auth()->id())
                        ->where('status', ApproverStatus::PENDING->value)
                        ->whereHas('workflowInstanceStep', function ($query) {
                            $query->where('workflow_instance_id', $this->record->current_workflow_instance_id)
                                ->where('sequence', $this->record->current_step_sequence);
                        })->exists();
                })
                ->action(function (array $data, WorkflowServiceContract $workflowService) {
                    $stepApprover = WorkflowStepApprover::where('approver_id', auth()->id())
                        ->where('status', ApproverStatus::PENDING->value)
                        ->whereHas('workflowInstanceStep', function ($query) {
                            $query->where('workflow_instance_id', $this->record->current_workflow_instance_id)
                                ->where('sequence', $this->record->current_step_sequence);
                        })->first();

                    if ($stepApprover) {
                        $workflowService->queueApproval(new ApprovalDecisionData(
                            workflowInstanceStepId: $stepApprover->workflow_instance_step_id,
                            actorId: auth()->id(),
                            commandUuid: (string) Str::uuid(),
                            decision: 'approve',
                            remarks: $data['remarks'] ?? null
                        ));

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Dokumen sedang diproses (Approve).')
                            ->success()
                            ->send();

                        return redirect()->to(MyApprovalResource::getUrl('index'));
                    }
                }),

            Action::make('reject')
                ->label('Tolak')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->modalHeading('Tolak Dokumen')
                ->modalDescription('Apakah Anda yakin ingin menolak dokumen ini? Anda wajib memberikan catatan penolakan.')
                ->form([
                    Textarea::make('remarks')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->maxLength(255),
                ])
                ->visible(function () {
                    if (! $this->record) {
                        return false;
                    }

                    return WorkflowStepApprover::where('approver_id', auth()->id())
                        ->where('status', ApproverStatus::PENDING->value)
                        ->whereHas('workflowInstanceStep', function ($query) {
                            $query->where('workflow_instance_id', $this->record->current_workflow_instance_id)
                                ->where('sequence', $this->record->current_step_sequence);
                        })->exists();
                })
                ->action(function (array $data, WorkflowServiceContract $workflowService) {
                    $stepApprover = WorkflowStepApprover::where('approver_id', auth()->id())
                        ->where('status', ApproverStatus::PENDING->value)
                        ->whereHas('workflowInstanceStep', function ($query) {
                            $query->where('workflow_instance_id', $this->record->current_workflow_instance_id)
                                ->where('sequence', $this->record->current_step_sequence);
                        })->first();

                    if ($stepApprover) {
                        $workflowService->queueApproval(new ApprovalDecisionData(
                            workflowInstanceStepId: $stepApprover->workflow_instance_step_id,
                            actorId: auth()->id(),
                            commandUuid: (string) Str::uuid(),
                            decision: 'reject',
                            remarks: $data['remarks'] ?? null
                        ));

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Dokumen sedang diproses (Reject).')
                            ->success()
                            ->send();

                        return redirect()->to(MyApprovalResource::getUrl('index'));
                    }
                }),
        ];
    }
}
