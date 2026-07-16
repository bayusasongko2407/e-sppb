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
                Section::make('Informasi Pengguna')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Nama Pengguna')
                            ->weight('bold'),
                        TextEntry::make('user.nik')
                            ->label('NIK'),
                    ])
                    ->columns(2),

                Section::make('Rincian Hak Akses Dokumen (Dikelompokkan per Lokasi Kerja)')
                    ->schema([
                        RepeatableEntry::make('groupedAccesses')
                            ->label('')
                            ->state(function (DocumentAccess $record): array {
                                $accesses = DocumentAccess::where('user_id', $record->user_id)
                                    ->with(['plant', 'department'])
                                    ->get();

                                $groups = $accesses->groupBy(fn ($item) => $item->plant_id.'-'.$item->department_id);

                                $result = [];
                                foreach ($groups as $items) {
                                    $first = $items->first();

                                    $modules = [];
                                    foreach ($items as $item) {
                                        $modules[] = [
                                            'name' => $item->module === 'sppb' ? 'SPPB' : 'Pelepasan Barang',
                                            'can_view' => $item->can_view,
                                            'can_create' => $item->can_create,
                                            'can_edit' => $item->can_edit,
                                            'can_delete' => $item->can_delete,
                                        ];
                                    }

                                    $result[] = [
                                        'plant_name' => $first->plant?->name ?? 'N/A',
                                        'department_name' => $first->department?->name ?? 'N/A',
                                        'modules' => $modules,
                                    ];
                                }

                                return $result;
                            })
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('plant_name')
                                            ->label('Plant')
                                            ->weight('bold'),
                                        TextEntry::make('department_name')
                                            ->label('Departemen')
                                            ->weight('bold'),
                                    ]),
                                RepeatableEntry::make('modules')
                                    ->label('Hak Akses Modul')
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Nama Modul'),
                                        IconEntry::make('can_view')
                                            ->label('Lihat')
                                            ->boolean(),
                                        IconEntry::make('can_create')
                                            ->label('Tambah')
                                            ->boolean(),
                                        IconEntry::make('can_edit')
                                            ->label('Ubah')
                                            ->boolean(),
                                        IconEntry::make('can_delete')
                                            ->label('Hapus')
                                            ->boolean(),
                                    ])
                                    ->columns(5)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
