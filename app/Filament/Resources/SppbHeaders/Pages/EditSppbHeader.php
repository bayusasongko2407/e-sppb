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
use Illuminate\Support\Facades\Storage;
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
                ->visible(fn (): bool => (auth()->user()?->hasAnyRole(['pemohon', 'Pemohon', 'super_admin']) || $this->record->requester_id === auth()->id()) && in_array($this->record->status, [SppbStatus::DRAFT->value, SppbStatus::REJECTED->value]))
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['uploaded_attachments'] = $this->record->attachments()->pluck('path')->toArray();

        return $data;
    }

    protected function afterSave(): void
    {
        $attachments = $this->data['uploaded_attachments'] ?? [];
        if (! is_array($attachments)) {
            $attachments = [$attachments];
        }

        $disk = config('filesystems.default', 'private');

        // 1. Delete attachments that were removed from the form
        $existingAttachments = $this->record->attachments()->get();
        foreach ($existingAttachments as $existing) {
            if (! in_array($existing->path, $attachments)) {
                // Delete from storage
                if (Storage::disk($disk)->exists($existing->path)) {
                    Storage::disk($disk)->delete($existing->path);
                }
                // Delete from database
                $existing->delete();
            }
        }

        // 2. Create new attachments
        $existingPaths = $existingAttachments->pluck('path')->toArray();
        foreach ($attachments as $path) {
            if (! $path) {
                continue;
            }
            if (in_array($path, $existingPaths)) {
                continue; // Already exists
            }

            $fullPath = Storage::disk($disk)->path($path);

            $fileSize = 0;
            $mimeType = 'application/octet-stream';
            $checksum = '';

            if (Storage::disk($disk)->exists($path)) {
                $fileSize = Storage::disk($disk)->size($path);
                $mimeType = Storage::disk($disk)->mimeType($path) ?? 'application/octet-stream';
                $checksum = hash_file('sha256', $fullPath) ?: '';
            }

            $originalName = basename($path);

            $this->record->attachments()->create([
                'uuid' => (string) Str::uuid(),
                'original_name' => $originalName,
                'stored_name' => basename($path),
                'disk' => $disk,
                'directory' => 'sppb-attachments',
                'path' => $path,
                'mime_type' => $mimeType,
                'extension' => pathinfo($path, PATHINFO_EXTENSION),
                'file_size' => $fileSize,
                'checksum_sha256' => $checksum,
                'uploader_id' => auth()->id(),
                'scan_status' => 'PENDING',
            ]);
        }
    }
}
