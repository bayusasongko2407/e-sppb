<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Plant;
use App\Models\Position;
use App\Models\User;
use App\Models\UserPosition;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan user lama kecuali super admin
        UserPosition::query()->delete();
        User::query()->update(['manager_id' => null]);
        User::query()->where('email', '!=', 'superadmin@esppb.local')->delete();

        $plants = Plant::all();
        $positions = Position::all()->pluck('id', 'code');

        foreach ($plants as $plant) {
            // Dapatkan departemen Engineering dan Gudang untuk plant ini
            $engDept = Department::where('plant_id', $plant->id)->where('code', 'ENG')->first();
            $logDept = Department::where('plant_id', $plant->id)->where('code', 'LOG')->first();

            if (! $engDept || ! $logDept) {
                continue;
            }

            // 1. Buat Manager
            $manager = User::create([
                'plant_id' => $plant->id,
                'department_id' => $engDept->id,
                'name' => 'Manager ENG '.$plant->code,
                'email' => 'manager.'.strtolower(str_replace(' ', '', $plant->code)).'@esppb.local',
                'nik' => 'MGR'.$plant->id.'00001',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]);
            $manager->assignRole(['Manager', 'manager']);
            UserPosition::create([
                'user_id' => $manager->id,
                'position_id' => $positions->get('MGR'),
                'is_primary' => true,
                'is_active' => true,
            ]);

            // 2. Buat Supervisor
            $supervisor = User::create([
                'plant_id' => $plant->id,
                'department_id' => $engDept->id,
                'manager_id' => $manager->id,
                'name' => 'Supervisor ENG '.$plant->code,
                'email' => 'supervisor.'.strtolower(str_replace(' ', '', $plant->code)).'@esppb.local',
                'nik' => 'SPV'.$plant->id.'00001',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]);
            $supervisor->assignRole(['Supervisor', 'approver']);
            UserPosition::create([
                'user_id' => $supervisor->id,
                'position_id' => $positions->get('SUPERVISOR') ?? $positions->get('SPV'),
                'is_primary' => true,
                'is_active' => true,
            ]);

            // 3. Buat Requester (Pemohon)
            $requester = User::create([
                'plant_id' => $plant->id,
                'department_id' => $engDept->id,
                'manager_id' => $manager->id,
                'name' => 'Staff ENG '.$plant->code,
                'email' => 'requester.'.strtolower(str_replace(' ', '', $plant->code)).'@esppb.local',
                'nik' => 'REQ'.$plant->id.'00001',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]);
            $requester->assignRole(['Pemohon', 'requester']);
            UserPosition::create([
                'user_id' => $requester->id,
                'position_id' => $positions->get('STAFF'),
                'is_primary' => true,
                'is_active' => true,
            ]);

            // 4. Buat BAT (Bagian Aset Tetap / BAT Verifier)
            $bat = User::create([
                'plant_id' => $plant->id,
                'department_id' => $engDept->id,
                'name' => 'BAT Verifier '.$plant->code,
                'email' => 'bat.'.strtolower(str_replace(' ', '', $plant->code)).'@esppb.local',
                'nik' => 'BAT'.$plant->id.'00001',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]);
            $bat->assignRole(['BAT Verifier', 'approver']);
            UserPosition::create([
                'user_id' => $bat->id,
                'position_id' => $positions->get('BAT'),
                'is_primary' => true,
                'is_active' => true,
            ]);

            // 5. Buat Gudang / Sekuriti Staff
            $gudang = User::create([
                'plant_id' => $plant->id,
                'department_id' => $logDept->id,
                'name' => 'Gudang '.$plant->code,
                'email' => 'gudang.'.strtolower(str_replace(' ', '', $plant->code)).'@esppb.local',
                'nik' => 'WH'.$plant->id.'00001',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]);
            $gudang->assignRole(['Sekuriti/Gudang', 'gudang']);
            UserPosition::create([
                'user_id' => $gudang->id,
                'position_id' => $positions->get('STAFF'),
                'is_primary' => true,
                'is_active' => true,
            ]);
        }
    }
}
