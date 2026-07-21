<?php

namespace App\Filament\Resources\DocumentAccesses;

use App\Filament\Resources\DocumentAccesses\Pages\CreateDocumentAccess;
use App\Filament\Resources\DocumentAccesses\Pages\EditDocumentAccess;
use App\Filament\Resources\DocumentAccesses\Pages\ListDocumentAccesses;
use App\Filament\Resources\DocumentAccesses\Pages\ViewDocumentAccess;
use App\Filament\Resources\DocumentAccesses\Schemas\DocumentAccessForm;
use App\Filament\Resources\DocumentAccesses\Schemas\DocumentAccessInfolist;
use App\Filament\Resources\DocumentAccesses\Tables\DocumentAccessesTable;
use App\Models\DocumentAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentAccessResource extends Resource
{
    protected static ?string $model = DocumentAccess::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Setup';

    protected static ?string $navigationLabel = 'Document Accesses';

    protected static ?string $modelLabel = 'Document Access';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return DocumentAccessForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentAccessInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentAccessesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'role', 'plant', 'department'])
            ->selectRaw('MIN(id) as id, user_id, role_id')
            ->groupBy('user_id', 'role_id');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentAccesses::route('/'),
            'create' => CreateDocumentAccess::route('/create'),
            'view' => ViewDocumentAccess::route('/{record}'),
            'edit' => EditDocumentAccess::route('/{record}/edit'),
        ];
    }
}
