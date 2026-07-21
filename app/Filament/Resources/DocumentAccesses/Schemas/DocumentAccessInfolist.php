<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentAccesses\Schemas;

use App\Models\DocumentAccess;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentAccessInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penerima')
                    ->schema([
                        TextEntry::make('receiver_type')
                            ->label('Tipe Penerima')
                            ->state(fn ($record) => $record?->role_id ? 'Peran (Role)' : 'Pengguna (User)')
                            ->weight('bold'),
                        TextEntry::make('recipient_name')
                            ->label('Nama Penerima')
                            ->state(fn ($record) => $record?->role_id ? $record->role?->name : $record->user?->name)
                            ->weight('bold'),
                    ])
                    ->columns(2),

                Section::make('Daftar Hak Akses Dokumen')
                    ->schema([
                        RepeatableEntry::make('access_items')
                            ->label('')
                            ->state(function (DocumentAccess $record): array {
                                $query = DocumentAccess::query();
                                if ($record->role_id) {
                                    $query->where('role_id', $record->role_id);
                                } else {
                                    $query->where('user_id', $record->user_id);
                                }
                                $accesses = $query->with(['plant', 'department'])->get();

                                return $accesses->map(fn ($item) => [
                                    'plant_name' => $item->plant?->name ?? 'Semua Plant',
                                    'department_name' => $item->department?->name ?? 'Semua Departemen',
                                    'module_name' => $item->module === 'sppb' ? 'SPPB' : 'Pelepasan Barang',
                                    'can_view' => $item->can_view,
                                    'can_create' => $item->can_create,
                                    'can_edit' => $item->can_edit,
                                    'can_delete' => $item->can_delete,
                                ])->toArray();
                            })
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('plant_name')
                                            ->label('Plant')
                                            ->weight('bold'),
                                        TextEntry::make('department_name')
                                            ->label('Departemen')
                                            ->weight('bold'),
                                        TextEntry::make('module_name')
                                            ->label('Modul')
                                            ->weight('bold'),
                                    ]),
                                Grid::make(4)
                                    ->schema([
                                        IconEntry::make('can_view')
                                            ->label('Bisa Lihat')
                                            ->boolean(),
                                        IconEntry::make('can_create')
                                            ->label('Bisa Tambah')
                                            ->boolean(),
                                        IconEntry::make('can_edit')
                                            ->label('Bisa Ubah')
                                            ->boolean(),
                                        IconEntry::make('can_delete')
                                            ->label('Bisa Hapus')
                                            ->boolean(),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
