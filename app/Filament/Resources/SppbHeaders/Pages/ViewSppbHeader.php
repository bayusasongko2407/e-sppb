<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Pages;

use App\Contracts\WorkflowServiceContract;
use App\DTOs\Workflow\ApprovalDecisionData;
use App\DTOs\Workflow\SubmitSppbData;
use App\Enums\ApproverStatus;
use App\Enums\SppbStatus;
use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use App\Filament\Resources\MyApprovals\MyApprovalResource;
use App\Filament\Resources\SppbHeaders\Schemas\SppbHeaderForm;
use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use App\Models\SppbStatusLog;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowStepApprover;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;

class ViewSppbHeader extends ViewRecord
{
    protected static string $resource = SppbHeaderResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function mount($record): void
    {
        parent::mount($record);

        if ($this->record && $this->record->status === SppbStatus::WAITING_VERIFICATION_BAT->value) {
            $user = auth()->user();
            if ($user) {
                $isBatApprover = WorkflowStepApprover::where('approver_id', $user->id)
                    ->where('status', ApproverStatus::PENDING->value)
                    ->whereHas('workflowInstanceStep', function ($query) {
                        $query->where('workflow_instance_id', $this->record->current_workflow_instance_id)
                            ->where('sequence', $this->record->current_step_sequence)
                            ->where(function ($sub) {
                                $sub->where('code', 'like', '%BAT%')
                                    ->orWhere('name', 'like', '%BAT%');
                            });
                    })->exists();

                if ($isBatApprover) {
                    $this->record->update(['status' => SppbStatus::PROCESS_VERIFICATION_BAT->value]);

                    SppbStatusLog::create([
                        'sppb_header_id' => $this->record->id,
                        'workflow_instance_id' => $this->record->current_workflow_instance_id,
                        'actor_id' => $user->id,
                        'action' => 'BAT_OPENED',
                        'from_status' => SppbStatus::WAITING_VERIFICATION_BAT->value,
                        'to_status' => SppbStatus::PROCESS_VERIFICATION_BAT->value,
                        'logged_at' => now(),
                    ]);

                    $this->refreshFormData(['status']);
                }
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit_approval')
                ->label('Ajukan Persetujuan')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Ajukan SPPB')
                ->modalDescription('Apakah Anda yakin ingin mengajukan SPPB ini untuk proses persetujuan? Dokumen yang diajukan tidak dapat diubah lagi.')
                ->modalSubmitActionLabel('Ya, Ajukan')
                ->visible(fn (): bool => (auth()->user()?->hasRole('pemohon') || auth()->user()?->hasRole('super_admin')) && in_array($this->record->status, [SppbStatus::DRAFT->value, SppbStatus::REJECTED->value]))
                ->action(function (WorkflowServiceContract $workflowService) {
                    try {
                        $workflowService->queueSubmission(new SubmitSppbData(
                            sppbHeaderId: $this->record->id,
                            actorId: auth()->id(),
                            commandUuid: Str::uuid()->toString(),
                        ));

                        Notification::make()
                            ->title('Berhasil')
                            ->body('SPPB berhasil masuk antrean pengajuan.')
                            ->success()
                            ->send();

                        return redirect()->to(SppbHeaderResource::getUrl('view', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal')
                            ->body('Terjadi kesalahan: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

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
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Toggle::make('require_plant_manager')
                        ->label('Membutuhkan Persetujuan Manager Plant')
                        ->default(false)
                        ->columnSpanFull()
                        ->visible(function () {
                            if (! $this->record) {
                                return false;
                            }

                            $activeStep = WorkflowInstanceStep::where('workflow_instance_id', $this->record->current_workflow_instance_id)
                                ->where('sequence', $this->record->current_step_sequence)
                                ->first();

                            if (! $activeStep) {
                                return false;
                            }

                            // Check if current step is Department Manager step (code/name contains MAN/MGR/MANAGER, but not BAT)
                            $isManagerStep = (str_contains(strtoupper($activeStep->code), 'MAN')
                                || str_contains(strtoupper($activeStep->name), 'MAN')
                                || str_contains(strtoupper($activeStep->code), 'MGR')
                                || str_contains(strtoupper($activeStep->name), 'MGR'))
                                && ! (str_contains(strtoupper($activeStep->code), 'BAT') || str_contains(strtoupper($activeStep->name), 'BAT'));

                            if (! $isManagerStep) {
                                return false;
                            }

                            // Check if there is a step after this one
                            return WorkflowInstanceStep::where('workflow_instance_id', $this->record->current_workflow_instance_id)
                                ->where('sequence', '>', $activeStep->sequence)
                                ->exists();
                        }),
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
                            remarks: $data['remarks'] ?? null,
                            requirePlantManager: $data['require_plant_manager'] ?? null,
                        ));

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Dokumen berhasil disetujui.')
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
                            ->body('Dokumen berhasil ditolak.')
                            ->success()
                            ->send();

                        return redirect()->to(MyApprovalResource::getUrl('index'));
                    }
                }),

            EditAction::make(),

            Action::make('print_pdf')
                ->label('Cetak')
                ->color('info')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('sppb.preview', ['record' => $this->record]))
                ->openUrlInNewTab()
                ->visible(fn (): bool => in_array($this->record->status, [
                    SppbStatus::APPROVED->value,
                    SppbStatus::RELEASE_IN_PROGRESS->value,
                    SppbStatus::COMPLETED->value,
                ])),

            Action::make('kirim_barang')
                ->label('Kirim Barang')
                ->color('primary')
                ->icon('heroicon-o-truck')
                ->form([
                    Radio::make('metode')
                        ->label('Metode Pengiriman')
                        ->options([
                            'sistem' => 'Surat Jalan Sistem',
                            'manual' => 'Surat Jalan Manual',
                        ])
                        ->default('sistem')
                        ->required(),
                ])
                ->modalHeading('Metode Pengiriman')
                ->modalDescription('Apakah Surat Jalan akan dibuat menggunakan sistem?')
                ->modalSubmitActionLabel('Lanjut')
                ->visible(fn (): bool => in_array($this->record->status, [
                    SppbStatus::APPROVED->value,
                    SppbStatus::RELEASE_IN_PROGRESS->value,
                ]))
                ->action(function (array $data) {
                    $isManual = $data['metode'] === 'manual' ? '1' : '0';

                    return redirect()->to(
                        GoodsReleaseResource::getUrl('create').
                        '?sppb_header_id='.$this->record->id.
                        '&is_manual='.$isManual
                    );
                }),

            Action::make('riwayat')
                ->label('Riwayat')
                ->color('gray')
                ->icon('heroicon-o-clock')
                ->modalHeading('Riwayat Workflow Persetujuan')
                ->modalContent(fn () => SppbHeaderForm::renderWorkflowTimeline($this->record))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
        ];
    }
}
