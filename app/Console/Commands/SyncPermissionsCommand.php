<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;

class SyncPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:sync-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai sinkronisasi permissions...');

        $actions = ['view', 'view_any', 'create', 'update', 'delete', 'restore', 'force_delete'];
        $count = 0;

        // Custom models from vendor/other namespaces that need permissions
        $customModels = ['role', 'permission'];
        foreach ($customModels as $modelLower) {
            foreach ($actions as $action) {
                $permissionName = $action.'_'.$modelLower;
                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );

                if ($permission->wasRecentlyCreated) {
                    $this->line("Dibuat: {$permissionName}");
                    $count++;
                }
            }
        }

        $modelPath = app_path('Models');
        $files = File::files($modelPath);

        foreach ($files as $file) {
            $modelName = $file->getFilenameWithoutExtension();
            $modelLower = strtolower($modelName);

            foreach ($actions as $action) {
                $permissionName = $action.'_'.$modelLower;
                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );

                if ($permission->wasRecentlyCreated) {
                    $this->line("Dibuat: {$permissionName}");
                    $count++;
                }
            }
        }

        // Juga pastikan role punya permission
        $this->info("Sinkronisasi selesai. {$count} permissions baru ditambahkan.");
    }
}
