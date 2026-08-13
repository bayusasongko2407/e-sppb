<?php

declare(strict_types=1);

namespace App\Filament\Resources\Items\Schemas;

use App\Models\EnumControl;
use App\Models\Unit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ItemForm
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
                Textarea::make('specification')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('unit_id')
                    ->label('Satuan')
                    ->options(fn () => Unit::getGroupedOptions())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn () => Unit::where('code', 'PCS')->value('id') ?? Unit::first()?->id),
                Select::make('item_category')
                    ->label('Kategori')
                    ->options(fn () => EnumControl::where('table_name', 'items')
                        ->where('column_name', 'item_category')
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
