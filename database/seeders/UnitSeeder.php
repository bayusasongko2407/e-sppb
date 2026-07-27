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
            // Berat
            ['code' => 'KG', 'name' => 'Kilogram', 'category' => 'Berat', 'is_active' => true],
            ['code' => 'GR', 'name' => 'Gram', 'category' => 'Berat', 'is_active' => true],
            ['code' => 'TON', 'name' => 'Ton', 'category' => 'Berat', 'is_active' => true],

            // Volume
            ['code' => 'L', 'name' => 'Liter', 'category' => 'Volume', 'is_active' => true],
            ['code' => 'ML', 'name' => 'Mililiter', 'category' => 'Volume', 'is_active' => true],

            // Panjang
            ['code' => 'M', 'name' => 'Meter', 'category' => 'Panjang', 'is_active' => true],

            // Kemasan
            ['code' => 'PCS', 'name' => 'Pcs', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'BOX', 'name' => 'Box', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'PACK', 'name' => 'Pack', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'ROLL', 'name' => 'Roll', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'SAK', 'name' => 'Sak', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'DRUM', 'name' => 'Drum', 'category' => 'Kemasan', 'is_active' => true],
            ['code' => 'CAN', 'name' => 'Kaleng (Can)', 'category' => 'Kemasan', 'is_active' => true],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['code' => $unit['code']], $unit);
        }
    }
}
