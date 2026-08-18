<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Run artisan command to generate model permissions
        Artisan::call('auth:sync-permissions');

        // 2. Custom permissions
        Permission::firstOrCreate(['name' => 'verify_bat', 'guard_name' => 'web']);

        $allPermissions = Permission::all();

        // 3. Super Admin & Admin (Full Access)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPermissions);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($allPermissions);

        // 4. Pemohon
        $requesterPerms = [
            'view_any_sppbheader', 'view_sppbheader', 'create_sppbheader', 'update_sppbheader', 'delete_sppbheader',
            'view_any_sppbdetail', 'view_sppbdetail', 'create_sppbdetail', 'update_sppbdetail', 'delete_sppbdetail',
            'view_any_attachment', 'view_attachment', 'create_attachment', 'delete_attachment',
            'view_any_sppbstatuslog', 'view_sppbstatuslog',
            'view_any_item', 'view_item', 'view_any_unit', 'view_unit',
            'view_any_plant', 'view_plant', 'view_any_department', 'view_department', 'view_any_location', 'view_location',
        ];
        Role::firstOrCreate(['name' => 'Pemohon', 'guard_name' => 'web'])->syncPermissions($requesterPerms);

        // 5. Supervisor & Manager
        $approverPerms = array_merge($requesterPerms, [
            'view_any_workflowinstance', 'view_workflowinstance', 'update_workflowinstance',
            'view_any_workflowdelegation', 'view_workflowdelegation', 'create_workflowdelegation', 'update_workflowdelegation',
        ]);
        Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web'])->syncPermissions($approverPerms);
        Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web'])->syncPermissions($approverPerms);

        // 6. BAT Verifier
        $batPerms = array_merge($approverPerms, [
            'verify_bat', 'view_any_asset', 'view_asset', 'create_asset', 'update_asset',
        ]);
        Role::firstOrCreate(['name' => 'BAT Verifier', 'guard_name' => 'web'])->syncPermissions($batPerms);

        // 7. Sekuriti / Gudang
        $gudangPerms = [
            'view_any_sppbheader', 'view_sppbheader',
            'view_any_goodsrelease', 'view_goodsrelease', 'create_goodsrelease', 'update_goodsrelease',
            'view_any_goodsreleaseitem', 'view_goodsreleaseitem', 'create_goodsreleaseitem', 'update_goodsreleaseitem',
            'verify_bat',
            'view_any_item', 'view_item', 'view_any_unit', 'view_unit',
            'view_any_plant', 'view_plant', 'view_any_department', 'view_department', 'view_any_location', 'view_location',
        ];
        Role::firstOrCreate(['name' => 'Sekuriti/Gudang', 'guard_name' => 'web'])->syncPermissions($gudangPerms);

        // 8. Auditor (Full Read-Only / Audit Trail Access)
        $auditorPerms = $allPermissions->filter(function ($permission) {
            return str_starts_with($permission->name, 'view') || $permission->name === 'verify_bat';
        })->pluck('name')->toArray();

        Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => 'web'])->syncPermissions($auditorPerms);
    }
}
