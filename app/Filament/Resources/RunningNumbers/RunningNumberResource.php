<?php

namespace App\Filament\Resources\RunningNumbers;

use App\Filament\Resources\RunningNumbers\Pages\CreateRunningNumber;
use App\Filament\Resources\RunningNumbers\Pages\EditRunningNumber;
use App\Filament\Resources\RunningNumbers\Pages\ListRunningNumbers;
use App\Filament\Resources\RunningNumbers\Schemas\RunningNumberForm;
use App\Filament\Resources\RunningNumbers\Tables\RunningNumbersTable;
use App\Models\RunningNumber;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RunningNumberResource extends Resource
{
    protected static ?string $model = RunningNumber::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';

    protected static \UnitEnum|string|null $navigationGroup = 'Sistem & Konfigurasi';

    protected static ?string $navigationLabel = 'Format Penomoran';

    protected static ?string $modelLabel = 'Format Penomoran';

    protected static ?string $pluralModelLabel = 'Format Penomoran';

    public static function form(Schema $schema): Schema
    {
        return RunningNumberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RunningNumbersTable::configure($table);
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
            'index' => ListRunningNumbers::route('/'),
            'create' => CreateRunningNumber::route('/create'),
            'edit' => EditRunningNumber::route('/{record}/edit'),
        ];
    }
}
