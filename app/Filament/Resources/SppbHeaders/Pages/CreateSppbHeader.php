<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Pages;

use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateSppbHeader extends CreateRecord
{
    protected static string $resource = SppbHeaderResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        if ($user) {
            $this->form->fill([
                'request_date' => now()->toDateString(),
                'date_needed' => now()->addDay()->toDateString(),
                'plant_id' => $user->plant_id,
                'department_id' => $user->department_id,
                'requester_id' => $user->id,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['request_date'] = $data['request_date'] ?? now()->toDateString();
        $data['date_needed'] = $data['date_needed'] ?? now()->addDay()->toDateString();
        $data['requester_id'] = $data['requester_id'] ?? auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $attachments = $this->data['uploaded_attachments'] ?? [];
        if (! is_array($attachments)) {
            $attachments = [$attachments];
        }

        $disk = config('filesystems.default', 'private');

        foreach ($attachments as $path) {
            if (! $path) {
                continue;
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
