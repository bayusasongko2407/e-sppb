<?php

namespace App\Filament\Resources\WorkflowDelegations\Pages;

use App\Filament\Resources\WorkflowDelegations\WorkflowDelegationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkflowDelegation extends EditRecord
{
    protected static string $resource = WorkflowDelegationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
