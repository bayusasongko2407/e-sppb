<?php

declare(strict_types=1);

namespace Tests\Feature\GoodsRelease;

use App\Models\GoodsRelease;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReleasePreviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_goods_release_preview_url_uses_encrypted_hash_and_rejects_plain_id(): void
    {
        $plant = Plant::factory()->create();
        $user = User::factory()->create(['plant_id' => $plant->id]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'requester_id' => $user->id,
            'status' => 'APPROVED',
        ]);

        $goodsRelease = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'created_by_id' => $user->id,
            'status' => 'RELEASED',
        ]);

        $this->actingAs($user);

        // Get route key (encrypted hash)
        $routeKey = $goodsRelease->getRouteKey();

        // 1. Verify encrypted route key is encrypted hash (not plain ID)
        $this->assertNotEquals((string) $goodsRelease->id, $routeKey);
        $this->assertGreaterThan(20, strlen($routeKey));

        // 2. Verify encrypted URL works
        $encryptedUrl = route('goods-releases.preview', ['record' => $goodsRelease]);
        $this->assertStringContainsString('/goods-releases/', $encryptedUrl);
        $this->assertStringEndsWith('/preview', $encryptedUrl);

        $response = $this->get($encryptedUrl);
        $response->assertStatus(200);

        // 3. Verify accessing plain numeric ID (e.g. /goods-releases/9/preview) fails with 404
        $plainIdUrl = '/goods-releases/'.$goodsRelease->id.'/preview';
        $plainResponse = $this->get($plainIdUrl);
        $plainResponse->assertStatus(404);
    }
}
