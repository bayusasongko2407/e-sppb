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
                    ->label('Kode Template')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama Template Workflow')
                    ->required(),
                TextInput::make('version')
                    ->label('Versi Template')
                    ->required()
                    ->numeric()
                    ->default(1),
                Select::make('plant_id')
                    ->label('Pabrik / Plant')
                    ->relationship('plant', 'name')
                    ->default(null),
                Select::make('department_id')
                    ->label('Departemen')
                    ->relationship('department', 'name')
                    ->default(null),
                TextInput::make('document_type')
                    ->label('Jenis Dokumen')
                    ->required()
                    ->default('SPPB'),
                Textarea::make('description')
                    ->label('Deskripsi Alur Kerja')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
                DateTimePicker::make('effective_from')
                    ->label('Berlaku Mulai'),
                DateTimePicker::make('effective_until')
                    ->label('Berlaku Sampai'),
            ]);
    }
}
