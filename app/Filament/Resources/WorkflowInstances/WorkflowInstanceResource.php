<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowInstances;

use App\Filament\Resources\WorkflowInstances\Pages\ListWorkflowInstances;
use App\Filament\Resources\WorkflowInstances\Pages\ViewWorkflowInstance;
use App\Models\WorkflowInstance;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkflowInstanceResource extends Resource
{
    protected static ?string $model = WorkflowInstance::class;

    protected static ?string $slug = 'workflow-instances';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Workflow';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Lacak Dokumen';

    protected static ?string $modelLabel = 'Lacak Dokumen';

    protected static ?string $pluralModelLabel = 'Lacak Dokumen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Dokumen')
                ->schema([
                    Grid::make(2)->schema([
                        Placeholder::make('sppbHeader.document_number')
                            ->label('No. SPPB')
                            ->content(fn ($record) => $record?->sppbHeader?->document_number ?? '-'),
                        Placeholder::make('workflowTemplate.name')
                            ->label('Template Workflow')
                            ->content(fn ($record) => $record?->workflowTemplate?->name ?? '-'),
                        Placeholder::make('status')
                            ->label('Status Terkini')
                            ->content(fn ($record) => $record?->status ?? '-'),
                        Placeholder::make('started_at')
                            ->label('Waktu Mulai')
                            ->content(fn ($record) => $record?->started_at ?? '-'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sppbHeader.document_number')
                    ->label('No. Dokumen SPPB')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('workflowTemplate.name')
                    ->label('Workflow')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('current_sequence')
                    ->label('Sequence Saat Ini'),
                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->label('Selesai')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkflowInstances::route('/'),
            'view' => ViewWorkflowInstance::route('/{record}'),
        ];
    }
}
