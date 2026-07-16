<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailChangeRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmailChangeRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('Pengguna'),
                TextEntry::make('old_email')
                    ->label('Email Lama'),
                TextEntry::make('new_email')
                    ->label('Email Baru'),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'APPROVED' => 'success',
                        'REJECTED' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('reason')
                    ->label('Alasan Penolakan')
                    ->placeholder('—')
                    ->visible(fn ($record) => $record?->status === 'REJECTED'),
            ]);
    }
}
