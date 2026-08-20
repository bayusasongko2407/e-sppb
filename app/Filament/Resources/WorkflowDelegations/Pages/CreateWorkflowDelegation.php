<?php

namespace App\Filament\Resources\WorkflowDelegations\Pages;

use App\Filament\Resources\WorkflowDelegations\WorkflowDelegationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkflowDelegation extends CreateRecord
{
    protected static string $resource = WorkflowDelegationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
