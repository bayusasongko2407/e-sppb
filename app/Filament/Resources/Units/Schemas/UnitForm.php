<?php

declare(strict_types=1);

namespace App\Filament\Resources\Units\Schemas;

use App\Models\EnumControl;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('code')
                    ->label('Kode')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                Select::make('category')
                    ->label('Kategori')
                    ->options(fn () => EnumControl::where('table_name', 'units')
                        ->where('column_name', 'category')
                        ->where('is_active', true)
                        ->orderBy('sequence')
                        ->pluck('label', 'value'))
                    ->searchable()
                    ->default(null),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
