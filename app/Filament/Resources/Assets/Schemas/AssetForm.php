<?php

declare(strict_types=1);

namespace App\Filament\Resources\Assets\Schemas;

use App\Models\EnumControl;
use App\Models\Unit;
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
                TextInput::make('asset_name')
                    ->label('Nama Aset')
                    ->required(),
                TextInput::make('asset_location_data')
                    ->label('Data Lokasi Aset')
                    ->default(null),
                TextInput::make('barcode')
                    ->required(),
                Select::make('condition')
                    ->label('Kondisi')
                    ->options(fn () => EnumControl::where('table_name', 'assets')
                        ->where('column_name', 'condition')
                        ->where('is_active', true)
                        ->orderBy('sequence')
                        ->pluck('label', 'value'))
                    ->required()
                    ->default('GOOD'),
                Select::make('status')
                    ->label('Status')
                    ->options(fn () => EnumControl::where('table_name', 'assets')
                        ->where('column_name', 'status')
                        ->where('is_active', true)
                        ->orderBy('sequence')
                        ->pluck('label', 'value'))
                    ->required()
                    ->default('AVAILABLE'),
                Select::make('unit_id')
                    ->label('Satuan')
                    ->options(fn () => Unit::getGroupedOptions())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(2),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
