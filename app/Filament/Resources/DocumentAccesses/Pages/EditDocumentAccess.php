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
                    $query = DocumentAccess::query();
                    if ($record->role_id) {
                        $query->where('role_id', $record->role_id);
                    } else {
                        $query->where('user_id', $record->user_id);
                    }
                    $query->delete();
                    $action->success();
                    $action->redirect(DocumentAccessResource::getUrl('index'));
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $receiverType = isset($data['role_id']) && $data['role_id'] ? 'role' : 'user';
        $data['receiver_type'] = $receiverType;

        $query = DocumentAccess::query();
        if ($receiverType === 'role') {
            $query->where('role_id', $data['role_id']);
        } else {
            $query->where('user_id', $data['user_id']);
        }

        $accesses = $query->get();

        $data['access_items'] = $accesses->map(fn ($access) => [
            'plant_id' => $access->plant_id,
            'department_id' => $access->department_id,
            'module' => $access->module,
            'can_view' => $access->can_view,
            'can_create' => $access->can_create,
            'can_edit' => $access->can_edit,
            'can_delete' => $access->can_delete,
        ])->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $receiverType = $data['receiver_type'] ?? 'user';

        $originalUserId = $record->getOriginal('user_id');
        $originalRoleId = $record->getOriginal('role_id');

        // Delete original accesses
        $deleteQuery = DocumentAccess::query();
        if ($originalRoleId) {
            $deleteQuery->where('role_id', $originalRoleId);
        } else {
            $deleteQuery->where('user_id', $originalUserId);
        }
        $deleteQuery->delete();

        $newUserId = $receiverType === 'user' ? $data['user_id'] : null;
        $newRoleId = $receiverType === 'role' ? $data['role_id'] : null;

        // Delete potential duplicates for new recipient
        $newDeleteQuery = DocumentAccess::query();
        if ($newRoleId) {
            $newDeleteQuery->where('role_id', $newRoleId);
        } else {
            $newDeleteQuery->where('user_id', $newUserId);
        }
        $newDeleteQuery->delete();

        $accessItems = $data['access_items'] ?? [];

        $lastRecord = null;
        foreach ($accessItems as $item) {
            $lastRecord = DocumentAccess::create([
                'user_id' => $newUserId,
                'role_id' => $newRoleId,
                'plant_id' => $item['plant_id'] ?? null,
                'department_id' => $item['department_id'] ?? null,
                'module' => $item['module'],
                'can_view' => $item['can_view'] ?? false,
                'can_create' => $item['can_create'] ?? false,
                'can_edit' => $item['can_edit'] ?? false,
                'can_delete' => $item['can_delete'] ?? false,
            ]);
        }

        return $lastRecord ?? $record;
    }
}
