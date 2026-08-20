<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecycleBins;

use App\Filament\Resources\RecycleBins\Pages\ListRecycleBins;
use App\Filament\Resources\RecycleBins\Pages\ViewRecycleBin;
use App\Filament\Resources\RecycleBins\Tables\RecycleBinsTable;
use App\Filament\Resources\SppbHeaders\Schemas\SppbHeaderForm;
use App\Filament\Resources\SppbHeaders\Schemas\SppbHeaderInfolist;
use App\Models\SppbHeader;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecycleBinResource extends Resource
{
    protected static ?string $model = SppbHeader::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Recycle Bin';

    protected static ?string $modelLabel = 'Dokumen SPPB Terhapus';

    protected static ?string $pluralModelLabel = 'Recycle Bin SPPB';

    protected static ?string $navigationLabel = 'SPPB Terhapus';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return SppbHeaderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SppbHeaderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecycleBinsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecycleBins::route('/'),
            'view' => ViewRecycleBin::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->onlyTrashed();
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->onlyTrashed();
    }
}
