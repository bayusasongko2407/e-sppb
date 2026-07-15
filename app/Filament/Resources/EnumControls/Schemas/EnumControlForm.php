<?php

namespace App\Filament\Resources\EnumControls\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EnumControlForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('table_name')
                    ->label('Tabel Database')
                    ->placeholder('Misal: assets')
                    ->required(),
                TextInput::make('column_name')
                    ->label('Kolom Database')
                    ->placeholder('Misal: condition')
                    ->required(),
                TextInput::make('value')
                    ->label('Nilai Internal')
                    ->placeholder('Misal: GOOD')
                    ->required(),
                TextInput::make('label')
                    ->label('Teks Tampilan')
                    ->placeholder('Misal: Baik')
                    ->required(),
                TextInput::make('sequence')
                    ->label('Urutan')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
