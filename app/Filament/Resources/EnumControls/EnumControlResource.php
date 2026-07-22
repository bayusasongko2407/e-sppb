<?php

namespace App\Filament\Resources\EnumControls;

use App\Filament\Resources\EnumControls\Pages\CreateEnumControl;
use App\Filament\Resources\EnumControls\Pages\EditEnumControl;
use App\Filament\Resources\EnumControls\Pages\ListEnumControls;
use App\Filament\Resources\EnumControls\Schemas\EnumControlForm;
use App\Filament\Resources\EnumControls\Tables\EnumControlsTable;
use App\Models\EnumControl;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EnumControlResource extends Resource
{
    protected static ?string $model = EnumControl::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Sistem & Konfigurasi';

    protected static ?string $modelLabel = 'Enum Kontrol';

    protected static ?string $pluralModelLabel = 'Enum Kontrol';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return EnumControlForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnumControlsTable::configure($table);
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
            'index' => ListEnumControls::route('/'),
            'create' => CreateEnumControl::route('/create'),
            'edit' => EditEnumControl::route('/{record}/edit'),
        ];
    }
}
