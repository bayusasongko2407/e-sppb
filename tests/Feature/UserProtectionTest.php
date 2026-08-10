<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Plant;
use App\Models\Position;
use App\Models\SppbHeader;
use App\Models\User;
use App\Models\UserPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_user_with_user_positions_when_no_transaction_records_exist(): void
    {
        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);
        $position = Position::factory()->create();
        $user = User::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
        ]);

        $userPosition = UserPosition::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->assertFalse($user->hasDependentRecords());

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('user_positions', ['id' => $userPosition->id]);
    }

    public function test_cannot_delete_user_with_associated_sppb_requests(): void
    {
        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);
        $user = User::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
        ]);

        SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'requester_id' => $user->id,
        ]);

        $this->assertTrue($user->hasDependentRecords());

        $this->expectException(\DomainException::class);
        $user->delete();
    }
}
