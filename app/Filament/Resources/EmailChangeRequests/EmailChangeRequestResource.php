<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailChangeRequests;

use App\Filament\Resources\EmailChangeRequests\Pages\ListEmailChangeRequests;
use App\Filament\Resources\EmailChangeRequests\Pages\ViewEmailChangeRequest;
use App\Filament\Resources\EmailChangeRequests\Schemas\EmailChangeRequestForm;
use App\Filament\Resources\EmailChangeRequests\Schemas\EmailChangeRequestInfolist;
use App\Filament\Resources\EmailChangeRequests\Tables\EmailChangeRequestsTable;
use App\Models\EmailChangeRequest;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EmailChangeRequestResource extends Resource
{
    protected static ?string $model = EmailChangeRequest::class;

    protected static ?string $slug = 'email-change-requests';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static \UnitEnum|string|null $navigationGroup = 'Sistem & Konfigurasi';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Konfirmasi Email';

    protected static ?string $modelLabel = 'Konfirmasi Perubahan Email';

    protected static ?string $pluralModelLabel = 'Konfirmasi Perubahan Email';

    public static function form(Schema $schema): Schema
    {
        return EmailChangeRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmailChangeRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailChangeRequestsTable::configure($table);
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
            'index' => ListEmailChangeRequests::route('/'),
            'view' => ViewEmailChangeRequest::route('/{record}'),
        ];
    }
}
