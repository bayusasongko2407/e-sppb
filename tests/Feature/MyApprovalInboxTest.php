<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SppbStatus;
use App\Filament\Resources\MyApprovals\MyApprovalResource;
use App\Filament\Resources\MyApprovals\Pages\ListMyApprovals;
use App\Models\Department;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MyApprovalInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejected_sppb_appears_in_requester_inbox(): void
    {
        Role::firstOrCreate(['name' => 'Pemohon', 'guard_name' => 'web']);

        $requester = User::factory()->create();
        $requester->assignRole('Pemohon');

        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);

        $rejectedSppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'requester_id' => $requester->id,
            'status' => SppbStatus::REJECTED->value,
        ]);

        $this->actingAs($requester);

        Livewire::test(ListMyApprovals::class)
            ->assertSeeText($rejectedSppb->number);

        $this->assertEquals('1', MyApprovalResource::getNavigationBadge());
    }

    public function test_navigation_badge_returns_null_when_inbox_is_empty(): void
    {
        Role::firstOrCreate(['name' => 'Pemohon', 'guard_name' => 'web']);

        $requester = User::factory()->create();
        $requester->assignRole('Pemohon');

        $this->actingAs($requester);

        $this->assertNull(MyApprovalResource::getNavigationBadge());
    }
}
