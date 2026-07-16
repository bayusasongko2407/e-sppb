<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowDelegations;

use App\Filament\Resources\WorkflowDelegations\Pages\CreateWorkflowDelegation;
use App\Filament\Resources\WorkflowDelegations\Pages\EditWorkflowDelegation;
use App\Filament\Resources\WorkflowDelegations\Pages\ListWorkflowDelegations;
use App\Filament\Resources\WorkflowDelegations\Pages\ViewWorkflowDelegation;
use App\Filament\Resources\WorkflowDelegations\Tables\WorkflowDelegationsTable;
use App\Models\WorkflowDelegation;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WorkflowDelegationResource extends Resource
{
    protected static ?string $model = WorkflowDelegation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'Workflow';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('delegator_id')
                    ->label('Pemberi Delegasi')
                    ->relationship('delegator', 'name')
                    ->required(),
                Select::make('delegate_id')
                    ->label('Penerima Delegasi')
                    ->relationship('delegate', 'name')
                    ->required(),
                Select::make('plant_id')
                    ->label('Pabrik')
                    ->relationship('plant', 'name')
                    ->default(null),
                DateTimePicker::make('starts_at')
                    ->label('Mulai Pada')
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('Berakhir Pada')
                    ->required(),
                Textarea::make('reason')
                    ->label('Alasan')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('delegator.name')
                    ->label('Delegator'),
                TextEntry::make('delegate.name')
                    ->label('Delegate'),
                TextEntry::make('plant.name')
                    ->label('Plant')
                    ->placeholder('-'),
                TextEntry::make('starts_at')
                    ->dateTime(),
                TextEntry::make('ends_at')
                    ->dateTime(),
                TextEntry::make('reason')
                    ->columnSpanFull(),
                IconEntry::make('is_active')
                    ->boolean(),
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
        return WorkflowDelegationsTable::configure($table);
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
            'index' => ListWorkflowDelegations::route('/'),
            'create' => CreateWorkflowDelegation::route('/create'),
            'view' => ViewWorkflowDelegation::route('/{record}'),
            'edit' => EditWorkflowDelegation::route('/{record}/edit'),
        ];
    }
}
