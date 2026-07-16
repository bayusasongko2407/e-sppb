<?php

namespace App\Filament\Resources\WorkflowDelegations\Pages;

use App\Filament\Resources\WorkflowDelegations\WorkflowDelegationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkflowDelegation extends ViewRecord
{
    protected static string $resource = WorkflowDelegationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
