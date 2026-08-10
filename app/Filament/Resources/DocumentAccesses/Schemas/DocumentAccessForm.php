<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentAccesses\Schemas;

use App\Models\Department;
use App\Models\Plant;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentAccessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penerima')
                    ->description('Pilih tipe penerima akses dan tentukan Pengguna atau Peran.')
                    ->schema([
                        Radio::make('receiver_type')
                            ->label('Tipe Penerima')
                            ->options([
                                'user' => 'Pengguna (User)',
                                'role' => 'Peran (Role)',
                            ])
                            ->default('user')
                            ->live()
                            ->afterStateHydrated(fn ($state, $set, $record) => $set('receiver_type', $record?->role_id ? 'role' : 'user'))
                            ->columnSpanFull(),

                        Select::make('user_id')
                            ->label('Pengguna (User)')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('receiver_type') === 'user')
                            ->required(fn ($get) => $get('receiver_type') === 'user')
                            ->dehydrateStateUsing(fn ($state, $get) => $get('receiver_type') === 'user' ? $state : null)
                            ->columnSpanFull(),

                        Select::make('role_id')
                            ->label('Peran (Role Spatie)')
                            ->relationship('role', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('receiver_type') === 'role')
                            ->required(fn ($get) => $get('receiver_type') === 'role')
                            ->dehydrateStateUsing(fn ($state, $get) => $get('receiver_type') === 'role' ? $state : null)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Daftar Ruang Lingkup & Hak Aksi (Permissions)')
                    ->description('Tentukan kombinasi Plant, Departemen, Modul, beserta aksi yang diperbolehkan.')
                    ->schema([
                        Repeater::make('access_items')
                            ->label('Aturan Akses')
                            ->defaultItems(1)
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('plant_id')
                                            ->label('Plant')
                                            ->options(Plant::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Semua Plant')
                                            ->live(),

                                        Select::make('department_id')
                                            ->label('Departemen')
                                            ->options(function (callable $get): array {
                                                $plantId = $get('plant_id');
                                                $query = Department::with('plant');

                                                if (! empty($plantId)) {
                                                    $query->where('plant_id', $plantId);
                                                }

                                                return $query->get()->mapWithKeys(fn ($dept) => [
                                                    $dept->id => '['.($dept->plant?->code ?? 'N/A').'] - '.$dept->name,
                                                ])->toArray();
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Semua Departemen'),

                                        Select::make('module')
                                            ->label('Modul')
                                            ->options([
                                                'sppb' => 'SPPB',
                                                'goods_release' => 'Pelepasan Barang',
                                            ])
                                            ->required(),
                                    ]),

                                Grid::make(4)
                                    ->schema([
                                        Toggle::make('can_view')
                                            ->label('Bisa Lihat')
                                            ->default(true)
                                            ->required(),
                                        Toggle::make('can_create')
                                            ->label('Bisa Tambah')
                                            ->default(false)
                                            ->required(),
                                        Toggle::make('can_edit')
                                            ->label('Bisa Ubah')
                                            ->default(false)
                                            ->required(),
                                        Toggle::make('can_delete')
                                            ->label('Bisa Hapus')
                                            ->default(false)
                                            ->required(),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
