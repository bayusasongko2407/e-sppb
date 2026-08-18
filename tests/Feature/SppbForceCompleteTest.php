<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SppbStatus;
use App\Models\SppbHeader;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppbForceCompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_force_complete_sppb_changes_status_to_completed(): void
    {
        $requester = User::factory()->create();

        $header = SppbHeader::factory()->create([
            'requester_id' => $requester->id,
            'status' => SppbStatus::RELEASE_IN_PROGRESS->value,
        ]);

        $service = app(WorkflowService::class);
        $service->forceCompleteSppb(
            sppbHeaderId: $header->id,
            actorId: $requester->id,
            reason: 'Remaining quantity not needed in the field.'
        );

        $header->refresh();

        $this->assertEquals(SppbStatus::COMPLETED->value, $header->status);
        $this->assertNotNull($header->completed_at);

        $this->assertDatabaseHas('sppb_status_logs', [
            'sppb_header_id' => $header->id,
            'to_status' => SppbStatus::COMPLETED->value,
            'action' => 'FORCE_COMPLETED',
        ]);
    }
}
