<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders;

use App\Filament\Resources\SppbHeaders\Pages\CreateSppbHeader;
use App\Filament\Resources\SppbHeaders\Pages\EditSppbHeader;
use App\Filament\Resources\SppbHeaders\Pages\ListSppbHeaders;
use App\Filament\Resources\SppbHeaders\Pages\ViewSppbHeader;
use App\Filament\Resources\SppbHeaders\Schemas\SppbHeaderForm;
use App\Filament\Resources\SppbHeaders\Schemas\SppbHeaderInfolist;
use App\Filament\Resources\SppbHeaders\Tables\SppbHeadersTable;
use App\Models\SppbHeader;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class SppbHeaderResource extends Resource
{
    protected static ?string $model = SppbHeader::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $modelLabel = 'Dokumen SPPB';

    protected static ?string $pluralModelLabel = 'Dokumen SPPB';

    protected static ?int $navigationSort = 1;

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
        return SppbHeadersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSppbHeaders::route('/'),
            'create' => CreateSppbHeader::route('/create'),
            'view' => ViewSppbHeader::route('/{record}'),
            'edit' => EditSppbHeader::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Restrict by DocumentAccess configuration, or if requester, or if they are the current approver
        return $query->where(function (Builder $q) use ($user) {
            $q->where('requester_id', $user->id)
                ->orWhere('current_approver_id', $user->id)
                ->orWhere(function (Builder $sub) use ($user) {
                    $sub->whereExists(function ($rawQuery) use ($user) {
                        $rawQuery->select(DB::raw(1))
                            ->from('document_accesses')
                            ->whereColumn('document_accesses.plant_id', 'sppb_headers.plant_id')
                            ->whereColumn('document_accesses.department_id', 'sppb_headers.department_id')
                            ->where('document_accesses.user_id', $user->id)
                            ->where('document_accesses.module', 'sppb')
                            ->where('document_accesses.can_view', true);
                    });
                });
        });
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
