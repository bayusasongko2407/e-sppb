<?php

declare(strict_types=1);

namespace App\Filament\Resources\GoodsReleases;

use App\Filament\Resources\GoodsReleases\Pages\CreateGoodsRelease;
use App\Filament\Resources\GoodsReleases\Pages\EditGoodsRelease;
use App\Filament\Resources\GoodsReleases\Pages\ListGoodsReleases;
use App\Models\GoodsRelease;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GoodsReleaseResource extends Resource
{
    protected static ?string $model = GoodsRelease::class;

    protected static ?string $slug = 'goods-releases';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Logistik & Gudang';

    protected static ?string $navigationLabel = 'Surat Jalan';

    protected static ?string $modelLabel = 'Surat Jalan';

    protected static ?string $pluralModelLabel = 'Surat Jalan';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Surat Jalan')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('sppb_header_id')
                            ->label('No. SPPB')
                            ->relationship('sppbHeader', 'document_number', fn ($query) => $query->where('status', 'APPROVED'))
                            ->required()
                            ->searchable(),
                        TextInput::make('release_number')
                            ->label('Nomor Surat Jalan')
                            ->default('SJ-'.date('Ymd').'-'.rand(100, 999))
                            ->required(),
                    ]),
                ]),

            Section::make('Informasi Kurir / Ekspedisi')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('driver_name')
                            ->label('Nama Pengemudi'),
                        TextInput::make('vehicle_number')
                            ->label('Nomor Kendaraan'),
                        TextInput::make('expedition_name')
                            ->label('Nama Ekspedisi'),
                        DatePicker::make('delivery_date')
                            ->label('Tanggal Pengiriman'),
                    ]),
                ]),

            Section::make('Daftar Barang Keluar')
                ->schema([
                    Repeater::make('goodsReleaseItems')
                        ->relationship('goodsReleaseItems')
                        ->label('Sisa Barang')
                        ->schema([
                            Select::make('sppb_detail_id')
                                ->label('Item SPPB')
                                ->relationship('sppbDetail', 'id')
                                ->required(),
                            TextInput::make('quantity_released')
                                ->label('Jumlah Keluar')
                                ->numeric()
                                ->required(),
                            TextInput::make('condition_on_release')
                                ->label('Kondisi Saat Keluar'),
                            Textarea::make('notes')
                                ->label('Catatan'),
                        ])->columns(4),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('release_number')
                    ->label('No. Surat Jalan')
                    ->searchable(),
                TextColumn::make('sppbHeader.document_number')
                    ->label('No. SPPB')
                    ->searchable(),
                TextColumn::make('driver_name')
                    ->label('Pengemudi')
                    ->searchable(),
                TextColumn::make('delivery_date')
                    ->label('Tanggal Kirim')
                    ->date(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGoodsReleases::route('/'),
            'create' => CreateGoodsRelease::route('/create'),
            'edit' => EditGoodsRelease::route('/{record}/edit'),
        ];
    }
}
