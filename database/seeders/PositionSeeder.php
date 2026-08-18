<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'code' => 'STAFF',
                'name' => 'Staff',
                'description' => 'Staff operasional departemen.',
                'is_active' => true,
            ],
            [
                'code' => 'SUPERVISOR',
                'name' => 'Supervisor',
                'description' => 'Supervisor operasional departemen.',
                'is_active' => true,
            ],
            [
                'code' => 'BAT',
                'name' => 'BAT (Bagian Aset Tetap)',
                'description' => 'Tim Verifikasi & Pengelolaan Bagian Aset Tetap.',
                'is_active' => true,
            ],
            [
                'code' => 'ASST_MGR',
                'name' => 'Asisten Manager',
                'description' => 'Asisten Manager departemen.',
                'is_active' => true,
            ],
            [
                'code' => 'MGR',
                'name' => 'Manager',
                'description' => 'Manager departemen.',
                'is_active' => true,
            ],
            [
                'code' => 'GUDANG',
                'name' => 'Gudang',
                'description' => 'Petugas Pengelola Gudang & Pelepasan Barang.',
                'is_active' => true,
            ],
            [
                'code' => 'AUDITOR',
                'name' => 'Auditor',
                'description' => 'Tim Audit & Pemeriksaan Internal.',
                'is_active' => true,
            ],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(['code' => $position['code']], $position);
        }
    }
}
