<?php

namespace App\Filament\Resources\WorkflowTemplates\Pages;

use App\Filament\Resources\WorkflowTemplates\WorkflowTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkflowTemplate extends EditRecord
{
    protected static string $resource = WorkflowTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
