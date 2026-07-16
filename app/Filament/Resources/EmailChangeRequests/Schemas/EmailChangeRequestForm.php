<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailChangeRequests\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;

class EmailChangeRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('user.name')
                    ->label('Pengguna')
                    ->content(fn ($record) => $record?->user?->name),
                Placeholder::make('old_email')
                    ->label('Email Lama')
                    ->content(fn ($record) => $record?->old_email),
                Placeholder::make('new_email')
                    ->label('Email Baru')
                    ->content(fn ($record) => $record?->new_email),
                Placeholder::make('status')
                    ->label('Status')
                    ->content(fn ($record) => $record?->status),
                Placeholder::make('reason')
                    ->label('Alasan Penolakan')
                    ->content(fn ($record) => $record?->reason ?? '—')
                    ->visible(fn ($record) => $record?->status === 'REJECTED'),
            ]);
    }
}
