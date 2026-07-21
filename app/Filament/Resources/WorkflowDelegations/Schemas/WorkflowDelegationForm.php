<?php

namespace App\Filament\Resources\WorkflowDelegations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorkflowDelegationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('delegator_id')
                    ->relationship('delegator', 'name')
                    ->required(),
                Select::make('delegate_id')
                    ->relationship('delegate', 'name')
                    ->different('delegator_id')
                    ->required(),
                Select::make('plant_id')
                    ->relationship('plant', 'name')
                    ->default(null),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->required(),
                Textarea::make('reason')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
