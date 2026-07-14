<?php

namespace App\Filament\Resources\SppbHeaders\Pages;

use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSppbHeader extends ViewRecord
{
    protected static string $resource = SppbHeaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
