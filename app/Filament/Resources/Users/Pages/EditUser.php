<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->before(function (DeleteAction $action, User $record): void {
                    if ($record->hasDependentRecords()) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal Menghapus Pengguna')
                            ->body('Pengguna tidak dapat dihapus karena memiliki riwayat transaksi, alur persetujuan, atau hirarki bawahan yang terhubung.')
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
