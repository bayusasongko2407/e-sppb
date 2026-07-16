<?php

namespace App\Filament\Resources\RunningNumbers\Pages;

use App\Filament\Resources\RunningNumbers\RunningNumberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRunningNumbers extends ListRecords
{
    protected static string $resource = RunningNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
