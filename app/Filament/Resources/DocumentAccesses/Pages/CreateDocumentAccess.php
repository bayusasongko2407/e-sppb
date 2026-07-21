<?php

namespace App\Filament\Resources\DocumentAccesses\Pages;

use App\Filament\Resources\DocumentAccesses\DocumentAccessResource;
use App\Models\DocumentAccess;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDocumentAccess extends CreateRecord
{
    protected static string $resource = DocumentAccessResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $receiverType = $data['receiver_type'] ?? 'user';
        $userId = $receiverType === 'user' ? $data['user_id'] : null;
        $roleId = $receiverType === 'role' ? $data['role_id'] : null;

        // Clean duplicates for this recipient first
        $query = DocumentAccess::query();
        if ($roleId) {
            $query->where('role_id', $roleId);
        } else {
            $query->where('user_id', $userId);
        }
        $query->delete();

        $accessItems = $data['access_items'] ?? [];

        $lastRecord = null;
        foreach ($accessItems as $item) {
            $lastRecord = DocumentAccess::create([
                'user_id' => $userId,
                'role_id' => $roleId,
                'plant_id' => $item['plant_id'] ?? null,
                'department_id' => $item['department_id'] ?? null,
                'module' => $item['module'],
                'can_view' => $item['can_view'] ?? false,
                'can_create' => $item['can_create'] ?? false,
                'can_edit' => $item['can_edit'] ?? false,
                'can_delete' => $item['can_delete'] ?? false,
            ]);
        }

        return $lastRecord ?? new DocumentAccess([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }
}
