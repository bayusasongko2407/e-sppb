<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Plant;
use App\Models\Position;
use App\Models\User;
use App\Models\UserPosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plant = Plant::where('code', 'SJA-SPJ')->first() ?? Plant::first();
        if (! $plant) {
            return;
        }

        $engDept = Department::where('plant_id', $plant->id)->where('code', 'ENG')->first();
        if (! $engDept) {
            return;
        }

        $positions = Position::all()->pluck('id', 'code');

        // Atasan Direct: Muhammad Rifa'i
        $rifai = User::updateOrCreate(
            ['nik' => '00000653'],
            [
                'name' => "Muhammad Rifa'i",
                'email' => 'rifai@engiboard.web.id',
                'password' => Hash::make('@Rifai123'),
                'plant_id' => $plant->id,
                'department_id' => $engDept->id,
                'is_active' => true,
            ]
        );
        $rifai->syncRoles(['Manager']);
        if ($positions->has('MGR')) {
            UserPosition::updateOrCreate(
                ['user_id' => $rifai->id, 'is_primary' => true],
                ['position_id' => $positions->get('MGR'), 'is_active' => true]
            );
        }

        // 11 Real Users
        $users = [
            ['nik' => '00062472', 'name' => 'Bayu Sasongko', 'email' => 'bayusasongko@admin.com', 'pos_code' => 'BAT', 'role' => 'BAT Verifier', 'pass' => '123BAYUs'],
            ['nik' => '00033194', 'name' => 'M. Iman Dwi Surya Ananta', 'email' => 'iman@engiboard.web.id', 'pos_code' => 'ASST_MGR', 'role' => 'Manager', 'pass' => '@Iman123'],
            ['nik' => '00039992', 'name' => 'M. Gunawan Fridiyanto', 'email' => 'gunawan@engiboard.web.id', 'pos_code' => 'SUPERVISOR', 'role' => 'Pemohon', 'pass' => '@Gunawan123'],
            ['nik' => '00002948', 'name' => 'Nico Budiman', 'email' => 'nico@engiboard.web.id', 'pos_code' => 'ASST_MGR', 'role' => 'Manager', 'pass' => '@Nico123'],
            ['nik' => '00002244', 'name' => 'Laksana Adi Nugroho', 'email' => 'laksana@engiboard.web.id', 'pos_code' => 'SUPERVISOR', 'role' => 'Pemohon', 'pass' => '@Laksana123'],
            ['nik' => '00062473', 'name' => 'Angga Setiawan Putra', 'email' => 'angga@engiboard.web.id', 'pos_code' => 'SUPERVISOR', 'role' => 'Pemohon', 'pass' => '@Angga123'],
            ['nik' => '00064284', 'name' => 'Christopher Aditya Cahya Dewata', 'email' => 'christopher@engiboard.web.id', 'pos_code' => 'SUPERVISOR', 'role' => 'Pemohon', 'pass' => '@Christopher123'],
            ['nik' => '00016625', 'name' => 'Fahmi Maulana Iqbal', 'email' => 'fahmi@engiboard.web.id', 'pos_code' => 'SUPERVISOR', 'role' => 'Pemohon', 'pass' => '@Fahmi123'],
            ['nik' => '00004266', 'name' => 'Irfan Permana Putra', 'email' => 'irfan@engiboard.web.id', 'pos_code' => 'SUPERVISOR', 'role' => 'Pemohon', 'pass' => '@Irfan123'],
            ['nik' => '00051201', 'name' => 'Rokhman Hidayat', 'email' => 'rokhman@engiboard.web.id', 'pos_code' => 'SUPERVISOR', 'role' => 'Pemohon', 'pass' => '@Rokhman123'],
            ['nik' => '00002259', 'name' => 'Endang Sunarmiasih', 'email' => 'endang@engiboard.web.id', 'pos_code' => 'BAT', 'role' => 'BAT Verifier', 'pass' => '@Endang123'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['nik' => $u['nik']],
                [
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'password' => Hash::make($u['pass']),
                    'plant_id' => $plant->id,
                    'department_id' => $engDept->id,
                    'manager_id' => $rifai->id,
                    'is_active' => true,
                ]
            );

            $user->syncRoles([$u['role']]);

            $posId = $positions->get($u['pos_code']);
            if ($posId) {
                UserPosition::updateOrCreate(
                    ['user_id' => $user->id, 'is_primary' => true],
                    ['position_id' => $posId, 'is_active' => true]
                );
            }
        }
    }
}
