<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncPoliciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:sync-policies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing hybrid authorization policies for all Models automatically';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Memulai generasi hybrid policies...');

        $modelPath = app_path('Models');
        if (! File::isDirectory($modelPath)) {
            $this->error('Direktori Models tidak ditemukan.');

            return;
        }

        $files = File::files($modelPath);
        $count = 0;

        foreach ($files as $file) {
            $modelName = $file->getFilenameWithoutExtension();
            $policyPath = app_path("Policies/{$modelName}Policy.php");

            // Skip if policy already exists to preserve custom logic
            if (File::exists($policyPath)) {
                continue;
            }

            // Determine module name (snake_case)
            $moduleName = Str::snake($modelName);

            // Generate template
            $template = $this->getPolicyTemplate($modelName, $moduleName);

            // Ensure directory exists
            File::ensureDirectoryExists(app_path('Policies'));

            File::put($policyPath, $template);
            $this->line("Dibuat Policy: {$modelName}Policy");
            $count++;
        }

        $this->info("Generasi policies selesai. {$count} policies baru dibuat.");
    }

    /**
     * Get stub template for the policy.
     */
    protected function getPolicyTemplate(string $modelName, string $moduleName): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\\{$modelName};
use Illuminate\Support\Facades\Schema;

class {$modelName}Policy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User \$user): bool
    {
        if (\$user->hasRole('super_admin')) {
            return true;
        }

        try {
            return \$user->hasPermissionTo('view_any_' . strtolower('{$modelName}'));
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist \$e) {
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User \$user, {$modelName} \$model): bool
    {
        if (\$user->hasRole('super_admin')) {
            return true;
        }

        \$plantId = Schema::hasColumn(\$model->getTable(), 'plant_id') ? \$model->plant_id : null;
        \$departmentId = Schema::hasColumn(\$model->getTable(), 'department_id') ? \$model->department_id : null;

        return \$user->hasDocumentAccess('{$moduleName}', 'view', \$plantId, \$departmentId);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User \$user): bool
    {
        if (\$user->hasRole('super_admin')) {
            return true;
        }

        try {
            if (! \$user->hasPermissionTo('create_' . strtolower('{$modelName}'))) {
                return false;
            }
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist \$e) {
            return false;
        }

        return \$user->documentAccesses()
            ->where('module', '{$moduleName}')
            ->where('can_create', true)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User \$user, {$modelName} \$model): bool
    {
        if (\$user->hasRole('super_admin')) {
            return true;
        }

        \$plantId = Schema::hasColumn(\$model->getTable(), 'plant_id') ? \$model->plant_id : null;
        \$departmentId = Schema::hasColumn(\$model->getTable(), 'department_id') ? \$model->department_id : null;

        return \$user->hasDocumentAccess('{$moduleName}', 'edit', \$plantId, \$departmentId);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User \$user, {$modelName} \$model): bool
    {
        if (\$user->hasRole('super_admin')) {
            return true;
        }

        \$plantId = Schema::hasColumn(\$model->getTable(), 'plant_id') ? \$model->plant_id : null;
        \$departmentId = Schema::hasColumn(\$model->getTable(), 'department_id') ? \$model->department_id : null;

        return \$user->hasDocumentAccess('{$moduleName}', 'delete', \$plantId, \$departmentId);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User \$user, {$modelName} \$model): bool
    {
        return \$user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User \$user, {$modelName} \$model): bool
    {
        return \$user->hasRole('super_admin');
    }
}
PHP;
    }
}
