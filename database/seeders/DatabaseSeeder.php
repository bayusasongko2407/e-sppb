<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'admin',
            'requester',
            'bat_approver',
            'manager_approver',
            'warehouse',
            'auditor',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@esppb.local',
        ], [
            'name' => 'Super Admin',
            'nik' => 'SA0000000000000000000000000001',
            'password' => app()->environment('production')
                ? bcrypt(env('SUPERADMIN_PASSWORD', Str::random(32)))
                : bcrypt('password'),
            'is_active' => true,
        ]);

        if (! $superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }
    }
}
