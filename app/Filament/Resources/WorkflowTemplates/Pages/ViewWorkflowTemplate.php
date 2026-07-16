<?php

namespace App\Filament\Resources\WorkflowTemplates\Pages;

use App\Filament\Resources\WorkflowTemplates\WorkflowTemplateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkflowTemplate extends ViewRecord
{
    protected static string $resource = WorkflowTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
