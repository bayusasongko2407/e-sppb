<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\EnumControls\Pages\CreateEnumControl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EnumControlFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_create_enum_control_page_without_type_error(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        Livewire::test(CreateEnumControl::class)
            ->assertSuccessful();
    }
}
