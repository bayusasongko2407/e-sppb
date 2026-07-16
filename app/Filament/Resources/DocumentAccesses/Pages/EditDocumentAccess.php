<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentAccesses\Pages;

use App\Filament\Resources\DocumentAccesses\DocumentAccessResource;
use App\Models\DocumentAccess;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDocumentAccess extends EditRecord
{
    protected static string $resource = DocumentAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function (DocumentAccess $record, DeleteAction $action) {
                    DocumentAccess::where('user_id', $record->user_id)->delete();
                    $action->success();
                    $action->redirect(DocumentAccessResource::getUrl('index'));
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $userId = $data['user_id'];

        $data['plant_id'] = DocumentAccess::where('user_id', $userId)->pluck('plant_id')->unique()->toArray();
        $data['department_id'] = DocumentAccess::where('user_id', $userId)->pluck('department_id')->unique()->toArray();
        $data['module'] = DocumentAccess::where('user_id', $userId)->pluck('module')->unique()->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $originalUserId = $record->getOriginal('user_id') ?? $record->user_id;
        $newUserId = $data['user_id'];

        // Delete all accesses for the original user
        DocumentAccess::where('user_id', $originalUserId)->delete();

        // Also delete for the new user if user_id was changed to avoid duplicates
        if ($originalUserId !== $newUserId) {
            DocumentAccess::where('user_id', $newUserId)->delete();
        }

        $plants = $data['plant_id'] ?? [];
        $departments = $data['department_id'] ?? [];
        $modules = $data['module'] ?? [];

        if (! is_array($plants)) {
            $plants = [$plants];
        }
        if (! is_array($departments)) {
            $departments = [$departments];
        }
        if (! is_array($modules)) {
            $modules = [$modules];
        }

        $lastRecord = null;
        foreach ($plants as $plantId) {
            foreach ($departments as $departmentId) {
                foreach ($modules as $module) {
                    $lastRecord = DocumentAccess::create([
                        'user_id' => $newUserId,
                        'plant_id' => $plantId,
                        'department_id' => $departmentId,
                        'module' => $module,
                        'can_view' => $data['can_view'] ?? false,
                        'can_create' => $data['can_create'] ?? false,
                        'can_edit' => $data['can_edit'] ?? false,
                        'can_delete' => $data['can_delete'] ?? false,
                    ]);
                }
            }
        }

        return $lastRecord ?? $record;
    }
}
