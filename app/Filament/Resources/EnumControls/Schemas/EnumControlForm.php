<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumControls\Schemas;

use App\Enums\DeliveryStatus;
use App\Enums\GoodsReleaseStatus;
use App\Enums\SppbStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as DbSchema;

class EnumControlForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('table_name')
                    ->label('Tabel Database')
                    ->options(function (): array {
                        $tables = DbSchema::getTableListing();
                        $excluded = [
                            'migrations',
                            'failed_jobs',
                            'personal_access_tokens',
                            'sessions',
                            'password_reset_tokens',
                            'cache',
                            'cache_locks',
                            'job_batches',
                            'jobs',
                        ];

                        $domainTables = array_filter($tables, fn ($table) => ! in_array($table, $excluded, true));

                        $options = [];
                        foreach ($domainTables as $table) {
                            $options[$table] = $table;
                        }
                        ksort($options);

                        return $options;
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('column_name', null)),

                Select::make('column_name')
                    ->label('Kolom Database')
                    ->options(function (Get $get): array {
                        $tableName = $get('table_name');
                        if (! $tableName || ! DbSchema::hasTable($tableName)) {
                            return [];
                        }

                        $columns = DbSchema::getColumnListing($tableName);
                        $options = [];
                        foreach ($columns as $column) {
                            $options[$column] = $column;
                        }
                        ksort($options);

                        return $options;
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),

                TextInput::make('value')
                    ->label('Nilai Internal (Value)')
                    ->placeholder('Misal: DRAFT, APPROVED, GOOD')
                    ->required()
                    ->datalist(function (Get $get): array {
                        $tableName = $get('table_name');
                        $columnName = $get('column_name');

                        if (! $tableName || ! $columnName) {
                            return [];
                        }

                        if ($tableName === 'sppb_headers' && $columnName === 'status') {
                            return array_map(fn (SppbStatus $s) => $s->value, SppbStatus::cases());
                        }

                        if ($tableName === 'goods_releases' && $columnName === 'status') {
                            return array_map(fn (GoodsReleaseStatus $s) => $s->value, GoodsReleaseStatus::cases());
                        }

                        if ($tableName === 'goods_releases' && $columnName === 'delivery_status') {
                            return array_map(fn (DeliveryStatus $s) => $s->value, DeliveryStatus::cases());
                        }

                        if (DbSchema::hasTable($tableName) && DbSchema::hasColumn($tableName, $columnName)) {
                            try {
                                return DB::table($tableName)
                                    ->whereNotNull($columnName)
                                    ->distinct()
                                    ->pluck($columnName)
                                    ->toArray();
                            } catch (\Throwable $e) {
                                return [];
                            }
                        }

                        return [];
                    }),

                TextInput::make('label')
                    ->label('Teks Tampilan (Label)')
                    ->placeholder('Misal: Disetujui, Baik, Suku Cadang')
                    ->required(),

                TextInput::make('sequence')
                    ->label('Urutan Tampilan')
                    ->required()
                    ->numeric()
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->required(),

                Textarea::make('description')
                    ->label('Deskripsi / Catatan')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
