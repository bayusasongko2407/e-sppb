<?php

declare(strict_types=1);

namespace Tests\Feature\Sppb;

use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SppbPreviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sppb_preview_url_uses_encrypted_hash_and_rejects_plain_id(): void
    {
        $plant = Plant::factory()->create();
        $user = User::factory()->create(['plant_id' => $plant->id]);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'requester_id' => $user->id,
            'status' => 'APPROVED',
        ]);

        $this->actingAs($user);

        // Get route key (encrypted hash)
        $routeKey = $sppb->getRouteKey();

        // 1. Verify encrypted route key is encrypted hash (not plain ID)
        $this->assertNotEquals((string) $sppb->id, $routeKey);
        $this->assertGreaterThan(20, strlen($routeKey));

        // 2. Verify encrypted URL works
        $encryptedUrl = route('sppb.preview', ['record' => $sppb]);
        $this->assertStringContainsString('/sppb/', $encryptedUrl);
        $this->assertStringEndsWith('/preview', $encryptedUrl);

        $response = $this->get($encryptedUrl);
        $response->assertStatus(200);

        // 3. Verify accessing plain numeric ID (e.g. /sppb/9/preview) fails with 404
        $plainIdUrl = '/sppb/'.$sppb->id.'/preview';
        $plainResponse = $this->get($plainIdUrl);
        $plainResponse->assertStatus(404);
    }
}
