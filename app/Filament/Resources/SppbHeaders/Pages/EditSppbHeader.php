<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Pages;

use App\Contracts\WorkflowServiceContract;
use App\DTOs\Workflow\SubmitSppbData;
use App\Enums\SppbStatus;
use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;

class EditSppbHeader extends EditRecord
{
    protected static string $resource = SppbHeaderResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
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
                ->visible(fn (): bool => in_array($this->record->status, [SppbStatus::DRAFT->value, SppbStatus::REJECTED->value]))
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

                        return redirect()->to(SppbHeaderResource::getUrl('view', ['record' => $this->record->id]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal')
                            ->body('Terjadi kesalahan: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
