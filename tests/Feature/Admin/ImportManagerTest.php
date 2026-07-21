<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Filament\Pages\ImportManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImportManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_manager_page_renders_successfully(): void
    {
        Role::create(['name' => 'super_admin']);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user);

        Livewire::test(ImportManager::class)
            ->assertStatus(200)
            ->assertFormFieldExists('import_type')
            ->assertFormFieldExists('file');
    }

    public function test_user_can_download_master_data_templates(): void
    {
        Role::create(['name' => 'super_admin']);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user);

        Livewire::test(ImportManager::class)
            ->call('downloadSelectedTemplate', 'assets')
            ->assertFileDownloaded('template-assets.xlsx');

        Livewire::test(ImportManager::class)
            ->call('downloadSelectedTemplate', 'plants')
            ->assertFileDownloaded('template-plants.xlsx');
    }
}
