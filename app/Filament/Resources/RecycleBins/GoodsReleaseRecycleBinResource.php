<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecycleBins;

use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use App\Filament\Resources\RecycleBins\Pages\ListGoodsReleaseRecycleBins;
use App\Filament\Resources\RecycleBins\Pages\ViewGoodsReleaseRecycleBin;
use App\Filament\Resources\RecycleBins\Tables\GoodsReleaseRecycleBinsTable;
use App\Models\GoodsRelease;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GoodsReleaseRecycleBinResource extends Resource
{
    protected static ?string $model = GoodsRelease::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Recycle Bin';

    protected static ?string $modelLabel = 'Surat Jalan Terhapus';

    protected static ?string $pluralModelLabel = 'Recycle Bin Surat Jalan';

    protected static ?string $navigationLabel = 'Surat Jalan Terhapus';

    protected static ?int $navigationSort = 2;

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
        return GoodsReleaseResource::form($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GoodsReleaseResource::infolist($schema);
    }

    public static function table(Table $table): Table
    {
        return GoodsReleaseRecycleBinsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGoodsReleaseRecycleBins::route('/'),
            'view' => ViewGoodsReleaseRecycleBin::route('/{record}'),
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
