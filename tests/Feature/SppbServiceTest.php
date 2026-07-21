<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\Sppb\CreateSppbData;
use App\DTOs\Sppb\SppbDetailData;
use App\Enums\SppbStatus;
use App\Exceptions\Workflow\SppbNotEditableException;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\Unit;
use App\Models\User;
use App\Services\SppbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppbServiceTest extends TestCase
{
    use RefreshDatabase;

    private SppbService $sppbService;

    private Plant $plant;

    private Department $department;

    private User $requester;

    private Location $originLocation;

    private Location $destinationLocation;

    private Item $item;

    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sppbService = app(SppbService::class);
        $this->plant = Plant::factory()->create();
        $this->department = Department::factory()->create();
        $this->requester = User::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
        ]);
        $this->originLocation = Location::factory()->create(['plant_id' => $this->plant->id]);
        $this->destinationLocation = Location::factory()->create(['plant_id' => $this->plant->id]);

        $this->unit = Unit::factory()->create();
        $this->item = Item::factory()->create(['unit_id' => $this->unit->id]);
    }

    public function test_can_create_draft(): void
    {
        $data = new CreateSppbData(
            plantId: $this->plant->id,
            departmentId: $this->department->id,
            requesterId: $this->requester->id,
            originLocationId: $this->originLocation->id,
            destinationLocationId: $this->destinationLocation->id,
            requestDate: '2026-07-14',
            purpose: 'Test Purpose',
            neededName: 'Needed Item',
            dateNeeded: '2026-07-15',
            isUrgent: true,
        );

        $sppb = $this->sppbService->createDraft($data);

        $this->assertNotNull($sppb->id);
        $this->assertEquals(SppbStatus::DRAFT->value, $sppb->status);
        $this->assertTrue($sppb->is_urgent);
    }

    public function test_cannot_create_draft_with_same_origin_and_destination(): void
    {
        $data = new CreateSppbData(
            plantId: $this->plant->id,
            departmentId: $this->department->id,
            requesterId: $this->requester->id,
            originLocationId: $this->originLocation->id,
            destinationLocationId: $this->originLocation->id, // Same
            requestDate: '2026-07-14',
            purpose: 'Test Purpose',
            neededName: 'Needed Item',
            dateNeeded: '2026-07-15',
            isUrgent: true,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->sppbService->createDraft($data);
    }

    public function test_can_add_detail_to_draft(): void
    {
        $data = new CreateSppbData(
            plantId: $this->plant->id,
            departmentId: $this->department->id,
            requesterId: $this->requester->id,
            originLocationId: $this->originLocation->id,
            destinationLocationId: $this->destinationLocation->id,
            requestDate: '2026-07-14',
            purpose: 'Test Purpose',
            neededName: 'Needed Item',
            dateNeeded: '2026-07-15',
            isUrgent: true,
        );
        $sppb = $this->sppbService->createDraft($data);

        $detailData = new SppbDetailData(
            barcodeConfirmed: false,
            itemId: $this->item->id,
            assetId: null,
            referenceCode: 'ITEM-123',
            itemAssetName: 'Test Item',
            unitId: $this->unit->id,
            quantity: 10,
            remarks: 'Ok',
        );

        $detail = $this->sppbService->addDetail($sppb->id, $detailData);

        $this->assertEquals(1, $detail->line_no);
        $this->assertEquals(10, $detail->quantity);
    }

    public function test_cannot_add_detail_if_not_editable(): void
    {
        $sppb = SppbHeader::factory()->create([
            'status' => SppbStatus::WAITING_APPROVAL->value,
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
        ]);

        $detailData = new SppbDetailData(
            barcodeConfirmed: false,
            itemId: $this->item->id,
            assetId: null,
            referenceCode: 'ITEM-123',
            itemAssetName: 'Test Item',
            unitId: $this->unit->id,
            quantity: 10,
            remarks: 'Ok',
        );

        $this->expectException(SppbNotEditableException::class);
        $this->sppbService->addDetail($sppb->id, $detailData);
    }

    public function test_cannot_queue_submit_without_details(): void
    {
        $data = new CreateSppbData(
            plantId: $this->plant->id,
            departmentId: $this->department->id,
            requesterId: $this->requester->id,
            originLocationId: $this->originLocation->id,
            destinationLocationId: $this->destinationLocation->id,
            requestDate: '2026-07-14',
            purpose: 'Test Purpose',
            neededName: 'Needed Item',
            dateNeeded: '2026-07-15',
            isUrgent: true,
        );
        $sppb = $this->sppbService->createDraft($data);

        $this->expectException(\InvalidArgumentException::class);
        $this->sppbService->queueSubmit($sppb->id, $this->requester->id);
    }

    public function test_cannot_add_detail_with_zero_or_negative_quantity(): void
    {
        $data = new CreateSppbData(
            plantId: $this->plant->id,
            departmentId: $this->department->id,
            requesterId: $this->requester->id,
            originLocationId: $this->originLocation->id,
            destinationLocationId: $this->destinationLocation->id,
            requestDate: '2026-07-14',
            purpose: 'Test Purpose',
            neededName: 'Needed Item',
            dateNeeded: '2026-07-15',
            isUrgent: true,
        );
        $sppb = $this->sppbService->createDraft($data);

        $detailData = new SppbDetailData(
            barcodeConfirmed: false,
            itemId: $this->item->id,
            assetId: null,
            referenceCode: 'ITEM-123',
            itemAssetName: 'Test Item',
            unitId: $this->unit->id,
            quantity: 0, // Invalid zero quantity
            remarks: 'Zero qty',
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->sppbService->addDetail($sppb->id, $detailData);
    }

    public function test_cannot_queue_submit_if_not_editable(): void
    {
        $sppb = SppbHeader::factory()->create([
            'status' => SppbStatus::APPROVED->value,
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
        ]);

        $this->expectException(SppbNotEditableException::class);
        $this->sppbService->queueSubmit($sppb->id, $this->requester->id);
    }
}
