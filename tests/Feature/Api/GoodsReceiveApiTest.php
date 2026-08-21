<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\GoodsRelease;
use App\Models\GoodsReleaseItem;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GoodsReceiveApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeRelease(string $status = 'RELEASED'): GoodsRelease
    {
        $sppb = SppbHeader::factory()->create(['status' => 'APPROVED']);
        $detail = SppbDetail::factory()->create(['sppb_header_id' => $sppb->id, 'quantity' => 10]);

        $release = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'status' => $status,
            'delivery_date' => now()->toDateString(),
        ]);

        GoodsReleaseItem::factory()->create([
            'goods_release_id' => $release->id,
            'sppb_detail_id' => $detail->id,
            'quantity_released' => 10,
            'quantity_received' => 0,
        ]);

        return $release;
    }

    #[Test]
    public function it_confirms_receipt_with_name_only(): void
    {
        $release = $this->makeRelease();

        $response = $this->postJson("/api/v1/goods-releases/{$release->uuid}/receive", [
            'recipient_name' => 'Budi Santoso',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('already_confirmed', false)
            ->assertJsonPath('data.recipient_name', 'Budi Santoso')
            ->assertJsonPath('data.has_signature', false)
            ->assertJsonPath('data.status', 'DELIVERED');

        $this->assertDatabaseHas('goods_releases', [
            'id' => $release->id,
            'status' => 'DELIVERED',
            'recipient_name' => 'Budi Santoso',
        ]);
    }

    #[Test]
    public function it_confirms_receipt_with_signature(): void
    {
        $release = $this->makeRelease();
        $fakeSignature = 'data:image/png;base64,'.base64_encode('fake-image-data');

        $response = $this->postJson("/api/v1/goods-releases/{$release->uuid}/receive", [
            'recipient_name' => 'Siti Rahayu',
            'recipient_signature' => $fakeSignature,
            'receiving_notes' => 'Diterima dalam kondisi baik',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.has_signature', true)
            ->assertJsonPath('data.receiving_notes', 'Diterima dalam kondisi baik');

        $this->assertDatabaseHas('goods_releases', [
            'id' => $release->id,
            'recipient_name' => 'Siti Rahayu',
            'receiving_notes' => 'Diterima dalam kondisi baik',
        ]);
    }

    #[Test]
    public function it_requires_recipient_name(): void
    {
        $release = $this->makeRelease();

        $response = $this->postJson("/api/v1/goods-releases/{$release->uuid}/receive", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['recipient_name']);
    }

    #[Test]
    public function it_is_idempotent_when_already_confirmed(): void
    {
        $release = $this->makeRelease('DELIVERED');
        $release->update([
            'received_at' => now(),
            'recipient_name' => 'Penerima Awal',
        ]);

        $response = $this->postJson("/api/v1/goods-releases/{$release->uuid}/receive", [
            'recipient_name' => 'Coba Ganti Nama',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('already_confirmed', true);

        // Nama tidak boleh berubah (idempotent)
        $this->assertDatabaseHas('goods_releases', [
            'id' => $release->id,
            'recipient_name' => 'Penerima Awal',
        ]);
    }

    #[Test]
    public function it_rejects_cancelled_goods_release(): void
    {
        $release = $this->makeRelease('CANCELLED');

        $response = $this->postJson("/api/v1/goods-releases/{$release->uuid}/receive", [
            'recipient_name' => 'Budi',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function it_returns_404_for_unknown_uuid(): void
    {
        $response = $this->postJson('/api/v1/goods-releases/tidak-ada-uuid/receive', [
            'recipient_name' => 'Budi',
        ]);

        $response->assertNotFound();
    }

    #[Test]
    public function it_can_be_called_by_authenticated_user(): void
    {
        $user = User::factory()->create();
        $release = $this->makeRelease();

        $token = $user->createToken('mobile-test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson("/api/v1/goods-releases/{$release->uuid}/receive", [
                'recipient_name' => 'Authenticated User',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.recipient_name', 'Authenticated User');
    }

    #[Test]
    public function it_creates_goods_release_with_partial_custom_items_via_api(): void
    {
        $user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'create_goodsrelease', 'guard_name' => 'web']);
        $user->givePermissionTo('create_goodsrelease');

        $sppb = SppbHeader::factory()->create(['status' => 'APPROVED', 'requester_id' => $user->id]);
        $detail1 = SppbDetail::factory()->create(['sppb_header_id' => $sppb->id, 'quantity' => 10]);
        $detail2 = SppbDetail::factory()->create(['sppb_header_id' => $sppb->id, 'quantity' => 20]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/sppb/{$sppb->uuid}/goods-releases", [
                'driver_name' => 'Pak Joko Driver',
                'vehicle_number' => 'L 9999 XX',
                'items' => [
                    [
                        'sppb_detail_id' => $detail1->id,
                        'quantity_released' => 4,
                        'condition_on_release' => 'Baik',
                    ],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.driver_name', 'Pak Joko Driver');

        $this->assertDatabaseHas('goods_releases', [
            'sppb_header_id' => $sppb->id,
            'driver_name' => 'Pak Joko Driver',
        ]);

        $this->assertDatabaseHas('goods_release_items', [
            'sppb_detail_id' => $detail1->id,
            'quantity_released' => 4,
        ]);
    }
}
