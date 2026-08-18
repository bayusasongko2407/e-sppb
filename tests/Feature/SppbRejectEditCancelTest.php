<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SppbStatus;
use App\Models\SppbHeader;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppbRejectEditCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejected_sppb_is_editable_and_can_be_cancelled_permanently(): void
    {
        $requester = User::factory()->create();

        $header = SppbHeader::factory()->create([
            'requester_id' => $requester->id,
            'status' => SppbStatus::REJECTED->value,
        ]);

        $statusEnum = SppbStatus::from($header->status);

        // 1. Verify REJECTED is editable and cancellable, but NOT terminal
        $this->assertTrue($statusEnum->isEditable());
        $this->assertTrue($statusEnum->isCancellable());
        $this->assertFalse($statusEnum->isTerminal());

        // 2. User cancels the REJECTED SPPB -> status becomes CANCELLED
        $service = app(WorkflowService::class);
        $service->cancelWorkflow(
            sppbHeaderId: $header->id,
            actorId: $requester->id,
            reason: 'User decided to close request permanently.'
        );

        $header->refresh();
        $this->assertEquals(SppbStatus::CANCELLED->value, $header->status);

        // 3. Verify CANCELLED is terminal (closed permanently)
        $cancelledEnum = SppbStatus::from($header->status);
        $this->assertTrue($cancelledEnum->isTerminal());
        $this->assertFalse($cancelledEnum->isEditable());
    }
}
