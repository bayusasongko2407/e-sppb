<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_delete_department_with_associated_users(): void
    {
        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);
        $user = User::factory()->create(['department_id' => $department->id]);

        $this->assertTrue($department->hasDependentRecords());

        $this->expectException(\DomainException::class);
        $department->delete();
    }

    public function test_can_delete_department_without_dependent_records(): void
    {
        $plant = Plant::factory()->create();
        $department = Department::factory()->create(['plant_id' => $plant->id]);

        $this->assertFalse($department->hasDependentRecords());

        $department->delete();

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }
}
