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
        // Bersihkan data posisi lama agar tersinkronisasi sempurna
        Position::query()->delete();

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
                'name' => 'BAT',
                'description' => 'Bagian Aset Tetap.',
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
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
