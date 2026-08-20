<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases\Pages;

use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use App\Models\GoodsRelease;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListGoodsReleases extends ListRecords
{
    protected static string $resource = GoodsReleaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_choice')
                ->label('Buat Surat Jalan')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading('Pilih Jenis Surat Jalan')
                ->modalDescription('Silakan pilih jenis Surat Jalan yang ingin Anda buat.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(view('filament.resources.goods-releases.create-choice-modal')),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Semua Surat Jalan')
                ->icon('heroicon-o-document-duplicate')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),

            'sppb_auto' => Tab::make('SPPB (Otomatis)')
                ->icon('heroicon-o-document-text')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')
                    ->where(fn ($q) => $q->where('is_manual', false)->orWhereNull('is_manual'))
                    ->where(fn ($q) => $q->whereNotNull('sppb_header_id')->orWhereHas('sppbHeaders'))),

            'sppb_manual' => Tab::make('SPPB (Manual SJ)')
                ->icon('heroicon-o-pencil-square')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')
                    ->where('is_manual', true)
                    ->where(fn ($q) => $q->whereNotNull('sppb_header_id')->orWhereHas('sppbHeaders'))),

            'manual_pure' => Tab::make('Manual (Non-SPPB)')
                ->icon('heroicon-o-clipboard-document-list')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')
                    ->where('is_manual', true)
                    ->whereNull('sppb_header_id')
                    ->whereDoesntHave('sppbHeaders')),
        ];

        if (auth()->user()?->hasRole('super_admin')) {
            $trashedCount = GoodsRelease::onlyTrashed()->count();
            $tabs['trashed'] = Tab::make('Recycle Bin')
                ->icon('heroicon-o-trash')
                ->badge($trashedCount > 0 ? (string) $trashedCount : null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class])->onlyTrashed());
        }

        return $tabs;
    }
}
