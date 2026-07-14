<?php

namespace App\Filament\Resources\WorkflowDelegations\Pages;

use App\Filament\Resources\WorkflowDelegations\WorkflowDelegationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowDelegations extends ListRecords
{
    protected static string $resource = WorkflowDelegationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
