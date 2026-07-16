<?php

namespace App\Filament\Resources\EmailChangeRequests\Pages;

use App\Filament\Resources\EmailChangeRequests\EmailChangeRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmailChangeRequest extends ViewRecord
{
    protected static string $resource = EmailChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
