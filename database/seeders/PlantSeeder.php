<?php

namespace Database\Seeders;

use App\Models\Plant;
use Illuminate\Database\Seeder;

class PlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plants = [
            ['code' => 'PLT-SPJ', 'name' => 'Plant Sepanjang', 'address' => 'Jl. Taman Asri No. 1, Sepanjang, Sidoarjo'],
            ['code' => 'PLT-PDA', 'name' => 'Plant Pandaan', 'address' => 'Jl. Raya Surabaya-Malang Km 48, Pandaan'],
            ['code' => 'PLT-KRW', 'name' => 'Plant Karawang', 'address' => 'Kawasan Industri KIIC, Karawang'],
            ['code' => 'PLT-SBY', 'name' => 'Plant Surabaya', 'address' => 'Jl. Berbek Industri I No. 5, Surabaya'],
            ['code' => 'PLT-CBR', 'name' => 'Plant Cibitung', 'address' => 'Kawasan MM2100, Cibitung, Bekasi'],
        ];

        foreach ($plants as $p) {
            Plant::firstOrCreate(['code' => $p['code']], $p);
        }
    }
}
