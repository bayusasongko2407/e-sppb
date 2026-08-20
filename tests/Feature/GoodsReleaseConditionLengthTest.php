<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GoodsRelease;
use App\Models\GoodsReleaseItem;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReleaseConditionLengthTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_long_condition_on_release_string_without_truncation(): void
    {
        $sppb = SppbHeader::factory()->create();
        $detail = SppbDetail::factory()->create(['sppb_header_id' => $sppb->id]);
        $release = GoodsRelease::factory()->create(['sppb_header_id' => $sppb->id]);

        $longConditionText = 'akan dikirim sebagian dari total order barang';

        $item = GoodsReleaseItem::create([
            'goods_release_id' => $release->id,
            'sppb_detail_id' => $detail->id,
            'quantity_requested' => 10,
            'quantity_released' => 5,
            'condition_on_release' => $longConditionText,
        ]);

        $this->assertDatabaseHas('goods_release_items', [
            'id' => $item->id,
            'condition_on_release' => $longConditionText,
        ]);
    }
}
