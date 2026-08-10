<?php

declare(strict_types=1);

namespace App\Filament\Resources\Plants\Pages;

use App\Filament\Resources\Plants\PlantResource;
use App\Models\Plant;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPlant extends EditRecord
{
    protected static string $resource = PlantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->before(function (DeleteAction $action, Plant $record): void {
                    if ($record->hasDependentRecords()) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal Menghapus Plant')
                            ->body('Plant tidak dapat dihapus karena masih digunakan oleh data departemen, lokasi, pengguna, atau transaksi.')
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
