<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            // Hitungan & Kemasan Utama
            ['code' => 'PCS', 'name' => 'Pcs (Pieces)', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'UNT', 'name' => 'Unit', 'category' => 'Hitungan', 'is_active' => true],
            ['code' => 'SET', 'name' => 'Set', 'category' => 'Hitungan', 'is_active' => true],
            ['code' => 'BOX', 'name' => 'Box / Dus', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'CTN', 'name' => 'Karton (Carton)', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'PACK', 'name' => 'Pack', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'ROLL', 'name' => 'Roll', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'SAK', 'name' => 'Sak / Saku', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'DRUM', 'name' => 'Drum', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'CAN', 'name' => 'Kaleng (Can)', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'PLT', 'name' => 'Pallet', 'category' => 'Kemasan', 'is_active' => true],

            // Berat
            ['code' => 'KG', 'name' => 'Kilogram', 'category' => 'Berat', 'is_active' => true],
            ['code' => 'GR', 'name' => 'Gram', 'category' => 'Berat', 'is_active' => true],
            ['code' => 'TON', 'name' => 'Ton', 'category' => 'Berat', 'is_active' => true],

            // Volume
            ['code' => 'L', 'name' => 'Liter', 'category' => 'Volume', 'is_active' => true],
            ['code' => 'ML', 'name' => 'Mililiter', 'category' => 'Volume', 'is_active' => true],
            ['code' => 'M3', 'name' => 'Meter Kubik (M³)', 'category' => 'Volume', 'is_active' => true],

            // Panjang & Luas
            ['code' => 'M', 'name' => 'Meter', 'category' => 'Panjang', 'is_active' => true],
            ['code' => 'CM', 'name' => 'Centimeter', 'category' => 'Panjang', 'is_active' => true],
            ['code' => 'MM', 'name' => 'Millimeter', 'category' => 'Panjang', 'is_active' => true],
            ['code' => 'M2', 'name' => 'Meter Persegi (M²)', 'category' => 'Luas', 'is_active' => true],

            // Bentuk Fisik / Lainnya
            ['code' => 'BTH', 'name' => 'Batang', 'category' => 'Lainnya', 'is_active' => true],
            ['code' => 'SHT', 'name' => 'Sheet / Lembar', 'category' => 'Lainnya', 'is_active' => true],
            ['code' => 'PASANG', 'name' => 'Pasang (Pair)', 'category' => 'Hitungan', 'is_active' => true],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['code' => $unit['code']], $unit);
        }
    }
}
