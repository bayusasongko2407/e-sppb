<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Plant;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plants = Plant::all();

        $departments = [
            ['code' => 'ENG', 'name' => 'Engineering'],
            ['code' => 'LOG', 'name' => 'Gudang & Logistik'],
            ['code' => 'PROD', 'name' => 'Produksi'],
            ['code' => 'QA', 'name' => 'Quality Assurance'],
        ];

        foreach ($plants as $plant) {
            foreach ($departments as $dept) {
                Department::firstOrCreate([
                    'plant_id' => $plant->id,
                    'code' => $dept['code'],
                ], [
                    'name' => $dept['name'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
