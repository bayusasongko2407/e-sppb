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
                    ->options(function (): array {
                        $dbOptions = EnumControl::where(function ($q) {
                            $q->where('table_name', 'units')->orWhere('table_name', 'like', '%.units');
                        })
                            ->where('column_name', 'category')
                            ->where('is_active', true)
                            ->orderBy('sequence')
                            ->pluck('label', 'value')
                            ->toArray();

                        if (! empty($dbOptions)) {
                            return $dbOptions;
                        }

                        return [
                            'BERAT' => 'Berat',
                            'VOLUME' => 'Volume',
                            'PANJANG' => 'Panjang',
                            'LUAS' => 'Luas',
                            'HITUNGAN' => 'Hitungan / Qty',
                            'KEMASAN' => 'Kemasan',
                            'LAINNYA' => 'Lainnya',
                        ];
                    })
                    ->searchable()
                    ->preload()
                    ->default(null),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }
}
