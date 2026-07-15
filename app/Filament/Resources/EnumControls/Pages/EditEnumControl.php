<?php

namespace App\Filament\Resources\EnumControls\Pages;

use App\Filament\Resources\EnumControls\EnumControlResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEnumControl extends EditRecord
{
    protected static string $resource = EnumControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
