<?php

namespace App\Filament\Resources\DocumentAccesses\Schemas;

use App\Models\Department;
use App\Models\Plant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class DocumentAccessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('plant_id')
                    ->label('Plant')
                    ->options(Plant::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->required(),
                Select::make('department_id')
                    ->label('Department')
                    ->options(fn () => Department::with('plant')->get()->mapWithKeys(fn ($dept) => [$dept->id => '['.($dept->plant?->code ?? 'N/A').'] - '.$dept->name])->toArray())
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->required(),
                Select::make('module')
                    ->options([
                        'sppb' => 'SPPB',
                        'goods_release' => 'Pelepasan Barang',
                    ])
                    ->multiple()
                    ->required(),
                Grid::make(4)
                    ->schema([
                        Toggle::make('can_view')
                            ->required(),
                        Toggle::make('can_create')
                            ->required(),
                        Toggle::make('can_edit')
                            ->required(),
                        Toggle::make('can_delete')
                            ->required(),
                    ]),
            ]);
    }
}
