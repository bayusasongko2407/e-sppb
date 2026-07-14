<?php

namespace App\Filament\Resources\WorkflowTemplates\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorkflowTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(1),
                Select::make('plant_id')
                    ->relationship('plant', 'name')
                    ->default(null),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->default(null),
                TextInput::make('document_type')
                    ->required()
                    ->default('SPPB'),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('effective_from'),
                DateTimePicker::make('effective_until'),
            ]);
    }
}
