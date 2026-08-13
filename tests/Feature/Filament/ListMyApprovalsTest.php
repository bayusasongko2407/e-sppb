<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\MyApprovals\Pages\ListMyApprovals;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListMyApprovalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_list_my_approvals_page(): void
    {
        Role::firstOrCreate(['name' => 'super_admin']);
        $approver = User::factory()->create();
        $approver->assignRole('super_admin');

        $this->actingAs($approver);

        Livewire::test(ListMyApprovals::class)
            ->assertSuccessful();
    }
}
