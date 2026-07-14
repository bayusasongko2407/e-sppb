<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Pages;

use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSppbHeader extends CreateRecord
{
    protected static string $resource = SppbHeaderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['request_date'] = $data['request_date'] ?? now()->toDateString();
        $data['requester_id'] = $data['requester_id'] ?? auth()->id();

        return $data;
    }
}
