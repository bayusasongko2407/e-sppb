<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EnumControl;
use Illuminate\Database\Seeder;

class EnumControlSeeder extends Seeder
{
    public function run(): void
    {
        $enums = [
            // Asset conditions
            ['table_name' => 'assets', 'column_name' => 'condition', 'value' => 'GOOD', 'label' => 'Baik', 'sequence' => 10],
            ['table_name' => 'assets', 'column_name' => 'condition', 'value' => 'NEEDS_REPAIR', 'label' => 'Perlu Perbaikan', 'sequence' => 20],
            ['table_name' => 'assets', 'column_name' => 'condition', 'value' => 'BROKEN', 'label' => 'Rusak', 'sequence' => 30],
            ['table_name' => 'assets', 'column_name' => 'condition', 'value' => 'SCRAP', 'label' => 'Scrap / Afkir', 'sequence' => 40],

            // Asset status
            ['table_name' => 'assets', 'column_name' => 'status', 'value' => 'AVAILABLE', 'label' => 'Tersedia', 'sequence' => 10],
            ['table_name' => 'assets', 'column_name' => 'status', 'value' => 'IN_USE', 'label' => 'Digunakan', 'sequence' => 20],
            ['table_name' => 'assets', 'column_name' => 'status', 'value' => 'CLASS_A', 'label' => 'Kelas A', 'sequence' => 30],
            ['table_name' => 'assets', 'column_name' => 'status', 'value' => 'CLASS_B', 'label' => 'Kelas B', 'sequence' => 40],

            // Item category
            ['table_name' => 'items', 'column_name' => 'item_category', 'value' => 'CONSUMABLE', 'label' => 'Barang Habis Pakai (Consumable)', 'sequence' => 10],
            ['table_name' => 'items', 'column_name' => 'item_category', 'value' => 'SPARE_PART', 'label' => 'Suku Cadang', 'sequence' => 20],
            ['table_name' => 'items', 'column_name' => 'item_category', 'value' => 'MATERIAL', 'label' => 'Bahan Baku', 'sequence' => 30],
            ['table_name' => 'items', 'column_name' => 'item_category', 'value' => 'EQUIPMENT', 'label' => 'Peralatan', 'sequence' => 40],

            // Unit category
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'BERAT', 'label' => 'Berat', 'sequence' => 10],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'VOLUME', 'label' => 'Volume', 'sequence' => 20],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'PANJANG', 'label' => 'Panjang', 'sequence' => 30],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'LUAS', 'label' => 'Luas', 'sequence' => 40],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'HITUNGAN', 'label' => 'Hitungan / Qty', 'sequence' => 50],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'KEMASAN', 'label' => 'Kemasan', 'sequence' => 60],
            ['table_name' => 'units', 'column_name' => 'category', 'value' => 'LAINNYA', 'label' => 'Lainnya', 'sequence' => 70],
        ];

        foreach ($enums as $enum) {
            EnumControl::firstOrCreate([
                'table_name' => $enum['table_name'],
                'column_name' => $enum['column_name'],
                'value' => $enum['value'],
            ], [
                'label' => $enum['label'],
                'sequence' => $enum['sequence'],
                'is_active' => true,
            ]);
        }
    }
}
