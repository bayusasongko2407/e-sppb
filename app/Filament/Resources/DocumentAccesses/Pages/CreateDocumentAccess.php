<?php

namespace App\Filament\Resources\DocumentAccesses\Pages;

use App\Filament\Resources\DocumentAccesses\DocumentAccessResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDocumentAccess extends CreateRecord
{
    protected static string $resource = DocumentAccessResource::class;

    protected function handleRecordCreation(array $data): Model
    {
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
                    $lastRecord = $this->getModel()::updateOrCreate(
                        [
                            'user_id' => $data['user_id'],
                            'plant_id' => $plantId,
                            'department_id' => $departmentId,
                            'module' => $module,
                        ],
                        [
                            'can_view' => $data['can_view'] ?? false,
                            'can_create' => $data['can_create'] ?? false,
                            'can_edit' => $data['can_edit'] ?? false,
                            'can_delete' => $data['can_delete'] ?? false,
                        ]
                    );
                }
            }
        }

        // Return the last created/updated record to satisfy the return type
        return $lastRecord ?? new ($this->getModel());
    }
}
