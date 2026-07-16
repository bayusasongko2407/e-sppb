<?php

namespace App\Filament\Resources\RunningNumbers\Pages;

use App\Filament\Resources\RunningNumbers\RunningNumberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRunningNumber extends EditRecord
{
    protected static string $resource = RunningNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
