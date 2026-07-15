<?php

declare(strict_types=1);

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('plant_id')
                    ->label('Plant')
                    ->relationship('plant', 'name')
                    ->required(),
                TextInput::make('code')
                    ->label('Kode')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
