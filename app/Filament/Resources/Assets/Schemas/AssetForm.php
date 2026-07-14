<?php

declare(strict_types=1);

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('plant_id')
                    ->label('Plant')
                    ->relationship('plant', 'name')
                    ->default(null),
                Select::make('location_id')
                    ->relationship('location', 'name')
                    ->default(null),
                TextInput::make('asset_location_name')
                    ->default(null),
                Textarea::make('asset_location_address')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('barcode')
                    ->required(),
                TextInput::make('condition')
                    ->required()
                    ->default('GOOD'),
                TextInput::make('status')
                    ->required()
                    ->default('AVAILABLE'),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
