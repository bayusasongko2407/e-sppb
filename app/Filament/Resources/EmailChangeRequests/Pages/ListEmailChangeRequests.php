<?php

namespace App\Filament\Resources\EmailChangeRequests\Pages;

use App\Filament\Resources\EmailChangeRequests\EmailChangeRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmailChangeRequests extends ListRecords
{
    protected static string $resource = EmailChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
