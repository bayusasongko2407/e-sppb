<?php

namespace App\Filament\Resources\WorkflowTemplates\Pages;

use App\Filament\Resources\WorkflowTemplates\WorkflowTemplateResource;
use App\Models\WorkflowTemplate;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditWorkflowTemplate extends EditRecord
{
    protected static string $resource = WorkflowTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->before(function (DeleteAction $action, WorkflowTemplate $record): void {
                    if ($record->hasDependentRecords()) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal Menghapus Template Workflow')
                            ->body('Template workflow tidak dapat dihapus karena masih digunakan oleh dokumen SPPB / alur persetujuan aktif.')
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
