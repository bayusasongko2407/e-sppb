<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\GoodsRelease\CreateGoodsReleaseData;
use App\DTOs\GoodsRelease\GoodsReleaseItemData;
use App\Enums\SppbStatus;
use App\Exceptions\GoodsRelease\InvalidGoodsReleaseQuantityException;
use App\Exceptions\Workflow\InvalidSppbTransitionException;
use App\Models\Department;
use App\Models\Plant;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\User;
use App\Services\GoodsReleaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReleaseServiceTest extends TestCase
{
    use RefreshDatabase;

    private GoodsReleaseService $goodsReleaseService;

    private SppbHeader $sppb;

    private SppbDetail $detail1;

    private SppbDetail $detail2;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        // Bind service later, for now just use app()
        // $this->goodsReleaseService = app(GoodsReleaseService::class);

        $plant = Plant::factory()->create();
        $department = Department::factory()->create();
        $this->actor = User::factory()->create();

        $this->sppb = SppbHeader::factory()->create([
            'status' => SppbStatus::APPROVED->value,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
        ]);

        $this->detail1 = SppbDetail::factory()->create([
            'sppb_header_id' => $this->sppb->id,
            'quantity' => 10,
        ]);

        $this->detail2 = SppbDetail::factory()->create([
            'sppb_header_id' => $this->sppb->id,
            'quantity' => 5,
        ]);
    }

    public function test_cannot_create_goods_release_for_unapproved_sppb(): void
    {
        $this->sppb->status = SppbStatus::WAITING_APPROVAL->value;
        $this->sppb->save();

        $this->goodsReleaseService = app(GoodsReleaseService::class);
        $data = new CreateGoodsReleaseData(
            sppbHeaderId: $this->sppb->id,
            actorId: $this->actor->id,
            driverName: 'Budi',
            vehicleNumber: 'B 1234 CD',
            items: [
                new GoodsReleaseItemData(sppbDetailId: $this->detail1->id, quantityReleased: 5),
            ]
        );

        $this->expectException(InvalidSppbTransitionException::class);
        $this->goodsReleaseService->createGoodsRelease($data);
    }

    public function test_cannot_release_more_than_requested(): void
    {
        $this->goodsReleaseService = app(GoodsReleaseService::class);
        $data = new CreateGoodsReleaseData(
            sppbHeaderId: $this->sppb->id,
            actorId: $this->actor->id,
            driverName: 'Budi',
            vehicleNumber: 'B 1234 CD',
            items: [
                new GoodsReleaseItemData(sppbDetailId: $this->detail1->id, quantityReleased: 11), // 11 > 10
            ]
        );

        $this->expectException(InvalidGoodsReleaseQuantityException::class);
        $this->goodsReleaseService->createGoodsRelease($data);
    }

    public function test_can_create_partial_and_full_goods_release(): void
    {
        $this->goodsReleaseService = app(GoodsReleaseService::class);

        // 1. Partial Release
        $data1 = new CreateGoodsReleaseData(
            sppbHeaderId: $this->sppb->id,
            actorId: $this->actor->id,
            driverName: 'Budi',
            vehicleNumber: 'B 1234 CD',
            items: [
                new GoodsReleaseItemData(sppbDetailId: $this->detail1->id, quantityReleased: 5), // 5 out of 10
                new GoodsReleaseItemData(sppbDetailId: $this->detail2->id, quantityReleased: 5),  // 5 out of 5 (full)
            ]
        );

        $release1 = $this->goodsReleaseService->createGoodsRelease($data1);

        $this->assertEquals(1, $release1->release_sequence);
        $this->assertEquals('SJ-'.now()->format('Ymd').'-'.str_pad((string) $this->sppb->id, 4, '0', STR_PAD_LEFT).'-1', $release1->release_number);

        $this->sppb->refresh();
        $this->assertEquals(SppbStatus::RELEASE_IN_PROGRESS->value, $this->sppb->status);

        // 2. Final Release
        $data2 = new CreateGoodsReleaseData(
            sppbHeaderId: $this->sppb->id,
            actorId: $this->actor->id,
            driverName: 'Andi',
            vehicleNumber: 'A 4321 EF',
            items: [
                new GoodsReleaseItemData(sppbDetailId: $this->detail1->id, quantityReleased: 5), // remaining 5
                new GoodsReleaseItemData(sppbDetailId: $this->detail2->id, quantityReleased: 0),  // no more
            ]
        );

        $release2 = $this->goodsReleaseService->createGoodsRelease($data2);

        $this->assertEquals(2, $release2->release_sequence);

        $this->sppb->refresh();
        $this->assertEquals(SppbStatus::COMPLETED->value, $this->sppb->status);
        $this->assertNotNull($this->sppb->completed_at);
    }

    public function test_cannot_create_goods_release_with_empty_or_zero_quantity_items(): void
    {
        $this->goodsReleaseService = app(GoodsReleaseService::class);
        $data = new CreateGoodsReleaseData(
            sppbHeaderId: $this->sppb->id,
            actorId: $this->actor->id,
            driverName: 'Budi',
            vehicleNumber: 'B 1234 CD',
            items: [
                new GoodsReleaseItemData(sppbDetailId: $this->detail1->id, quantityReleased: 0),
            ]
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->goodsReleaseService->createGoodsRelease($data);
    }

    public function test_partial_release_omitting_unreleased_details_remains_in_progress(): void
    {
        $this->goodsReleaseService = app(GoodsReleaseService::class);

        // Only release detail1 (full 10), omitting detail2 (quantity 5) from items array
        $data = new CreateGoodsReleaseData(
            sppbHeaderId: $this->sppb->id,
            actorId: $this->actor->id,
            driverName: 'Budi',
            vehicleNumber: 'B 1234 CD',
            items: [
                new GoodsReleaseItemData(sppbDetailId: $this->detail1->id, quantityReleased: 10),
            ]
        );

        $this->goodsReleaseService->createGoodsRelease($data);

        $this->sppb->refresh();
        // Should be RELEASE_IN_PROGRESS because detail2 is not yet released
        $this->assertEquals(SppbStatus::RELEASE_IN_PROGRESS->value, $this->sppb->status);
    }

    public function test_can_receive_goods_release(): void
    {
        $this->goodsReleaseService = app(GoodsReleaseService::class);
        $data = new CreateGoodsReleaseData(
            sppbHeaderId: $this->sppb->id,
            actorId: $this->actor->id,
            driverName: 'Budi',
            vehicleNumber: 'B 1234 CD',
            items: [
                new GoodsReleaseItemData(sppbDetailId: $this->detail1->id, quantityReleased: 10),
                new GoodsReleaseItemData(sppbDetailId: $this->detail2->id, quantityReleased: 5),
            ]
        );

        $release = $this->goodsReleaseService->createGoodsRelease($data);

        $updatedRelease = $this->goodsReleaseService->receiveGoodsRelease($release, [
            'status' => 'DELIVERED',
            'notes' => 'Telah diterima dengan baik',
            'received_at' => now()->toIso8601String(),
        ], $this->actor->id);

        $this->assertEquals('DELIVERED', $updatedRelease->status);
        $this->assertEquals('Telah diterima dengan baik', $updatedRelease->notes);
        $this->assertNotNull($updatedRelease->received_at);

        $this->sppb->refresh();
        $this->assertEquals(SppbStatus::COMPLETED->value, $this->sppb->status);
    }
}
