<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowTemplates;

use App\Filament\Resources\WorkflowTemplates\Pages\CreateWorkflowTemplate;
use App\Filament\Resources\WorkflowTemplates\Pages\EditWorkflowTemplate;
use App\Filament\Resources\WorkflowTemplates\Pages\ListWorkflowTemplates;
use App\Filament\Resources\WorkflowTemplates\Pages\ViewWorkflowTemplate;
use App\Filament\Resources\WorkflowTemplates\Tables\WorkflowTemplatesTable;
use App\Models\WorkflowTemplate;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkflowTemplateResource extends Resource
{
    protected static ?string $model = WorkflowTemplate::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Sistem';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('code')
                    ->label('Kode')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('version')
                    ->label('Versi')
                    ->required()
                    ->numeric()
                    ->default(1),
                Select::make('plant_id')
                    ->label('Pabrik')
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
                    ->label('Deskripsi')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
                DateTimePicker::make('effective_from')
                    ->label('Berlaku Dari'),
                DateTimePicker::make('effective_until')
                    ->label('Berlaku Sampai'),
                Repeater::make('workflowSteps')
                    ->label('Langkah Workflow')
                    ->relationship('workflowSteps')
                    ->schema([
                        TextInput::make('sequence')
                            ->label('Urutan')
                            ->numeric()
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode')
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required(),
                        TextInput::make('approver_type')
                            ->label('Tipe Penyetuju')
                            ->required(),
                        TextInput::make('approval_mode')
                            ->label('Mode Persetujuan')
                            ->required(),
                        TextInput::make('minimum_approvals')
                            ->label('Minimum Persetujuan')
                            ->numeric()
                            ->required(),
                        TextInput::make('sla_hours')
                            ->label('SLA (Jam)')
                            ->numeric()
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('code'),
                TextEntry::make('name'),
                TextEntry::make('version')
                    ->numeric(),
                TextEntry::make('plant.name')
                    ->label('Plant')
                    ->placeholder('-'),
                TextEntry::make('department.name')
                    ->label('Department')
                    ->placeholder('-'),
                TextEntry::make('document_type'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('effective_from')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('effective_until')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return WorkflowTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkflowTemplates::route('/'),
            'create' => CreateWorkflowTemplate::route('/create'),
            'view' => ViewWorkflowTemplate::route('/{record}'),
            'edit' => EditWorkflowTemplate::route('/{record}/edit'),
        ];
    }
}
