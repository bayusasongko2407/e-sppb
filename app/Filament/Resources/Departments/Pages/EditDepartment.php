<?php

declare(strict_types=1);

namespace App\Filament\Resources\Departments\Pages;

use App\Filament\Resources\Departments\DepartmentResource;
use App\Models\Department;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->before(function (DeleteAction $action, Department $record): void {
                    if ($record->hasDependentRecords()) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal Menghapus Departemen')
                            ->body('Departemen tidak dapat dihapus karena masih digunakan oleh pengguna, SPPB, workflow, atau data terkait lainnya.')
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
