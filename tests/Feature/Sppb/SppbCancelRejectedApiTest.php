<?php

declare(strict_types=1);

namespace Tests\Feature\Sppb;

use App\Enums\SppbStatus;
use App\Models\Department;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SppbCancelRejectedApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requester_can_cancel_rejected_sppb_via_api(): void
    {
        $plant = Plant::factory()->create();
        $dept = Department::factory()->create(['plant_id' => $plant->id]);
        $requester = User::factory()->create(['plant_id' => $plant->id, 'department_id' => $dept->id]);
        Permission::firstOrCreate(['name' => 'update_sppbheader', 'guard_name' => 'web']);
        $requester->givePermissionTo('update_sppbheader');

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'requester_id' => $requester->id,
            'status' => SppbStatus::REJECTED->value,
        ]);

        Sanctum::actingAs($requester);

        $response = $this->postJson('/api/v1/sppb/'.$sppb->uuid.'/cancel', [
            'reason' => 'Permohonan dibatalkan karena tidak jadi digunakan.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Permohonan SPPB berhasil dibatalkan.');

        $this->assertDatabaseHas('sppb_headers', [
            'id' => $sppb->id,
            'status' => SppbStatus::CANCELLED->value,
            'cancelled_reason' => 'Permohonan dibatalkan karena tidak jadi digunakan.',
        ]);

        $this->assertDatabaseHas('sppb_status_logs', [
            'sppb_header_id' => $sppb->id,
            'actor_id' => $requester->id,
            'action' => 'SPPB_CANCELLED',
            'from_status' => SppbStatus::REJECTED->value,
            'to_status' => SppbStatus::CANCELLED->value,
            'remarks' => 'Permohonan dibatalkan karena tidak jadi digunakan.',
        ]);
    }
}
