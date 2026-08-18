<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Plant;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plants = Plant::all();

        foreach ($plants as $plant) {
            $cleanPlantCode = substr(str_replace([' ', '-'], '', $plant->code), 0, 10);
            $locations = [
                [
                    'code' => "LOC-{$cleanPlantCode}-ENG",
                    'name' => 'Workshop Engineering '.$plant->name,
                    'address' => 'Area workshop pemeliharaan mesin pada '.$plant->name,
                ],
                [
                    'code' => "LOC-{$cleanPlantCode}-GRAW",
                    'name' => 'Gudang Bahan Baku '.$plant->name,
                    'address' => 'Area penyimpanan raw material pada '.$plant->name,
                ],
                [
                    'code' => "LOC-{$cleanPlantCode}-GFIN",
                    'name' => 'Gudang Barang Jadi '.$plant->name,
                    'address' => 'Area penyimpanan produk jadi / finished goods pada '.$plant->name,
                ],
            ];

            foreach ($locations as $loc) {
                Location::firstOrCreate([
                    'plant_id' => $plant->id,
                    'code' => $loc['code'],
                ], [
                    'name' => $loc['name'],
                    'address' => $loc['address'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
