<?php

declare(strict_types=1);

namespace App\Filament\Resources\SppbHeaders\Pages;

use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use App\Models\SppbHeader;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListSppbHeaders extends ListRecords
{
    protected static string $resource = SppbHeaderResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        if (! auth()->user()?->hasRole('super_admin')) {
            return [];
        }

        $trashedCount = SppbHeader::onlyTrashed()->count();

        return [
            'active' => Tab::make('Dokumen Aktif')
                ->icon('heroicon-o-document-text')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),

            'trashed' => Tab::make('Recycle Bin')
                ->icon('heroicon-o-trash')
                ->badge($trashedCount > 0 ? (string) $trashedCount : null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class])->onlyTrashed()),
        ];
    }
}
