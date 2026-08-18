<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\ApproverStatus;
use App\Enums\SppbStatus;
use App\Models\Department;
use App\Models\GoodsRelease;
use App\Models\Item;
use App\Models\Location;
use App\Models\Plant;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkflowInstance;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowStepApprover;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ApiCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Plant $plant;

    protected Department $department;

    protected Location $location;

    protected Unit $unit;

    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plant = Plant::factory()->create(['name' => 'Pabrik Utama']);
        $this->department = Department::factory()->create(['plant_id' => $this->plant->id]);
        $this->location = Location::factory()->create(['plant_id' => $this->plant->id]);
        $this->unit = Unit::factory()->create(['name' => 'PCS', 'code' => 'PCS']);
        $this->item = Item::factory()->create(['unit_id' => $this->unit->id]);

        $this->user = User::factory()->create([
            'email' => 'testing_user@example.com',
            'nik' => '1234567890',
            'password' => Hash::make('password123'),
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);

        Permission::firstOrCreate(['name' => 'view_any_sppbheader']);
        Permission::firstOrCreate(['name' => 'view_sppbheader']);
        Permission::firstOrCreate(['name' => 'update_sppbheader']);
        Permission::firstOrCreate(['name' => 'create_goodsrelease']);
        Permission::firstOrCreate(['name' => 'view_any_goodsrelease']);

        $this->user->givePermissionTo([
            'view_any_sppbheader',
            'view_sppbheader',
            'update_sppbheader',
            'create_goodsrelease',
            'view_any_goodsrelease',
        ]);
    }

    public function test_login_accepts_username_and_returns_token_and_nip_aliases(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'username' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'data' => [
                'access_token',
                'token',
                'refresh_token',
                'user' => [
                    'id',
                    'nik',
                    'nip',
                    'email',
                ],
            ],
        ]);
        $this->assertEquals($response->json('data.access_token'), $response->json('data.token'));
        $this->assertEquals('1234567890', $response->json('data.user.nip'));
    }

    public function test_login_accepts_email_and_returns_aliases(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'testing_user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.user.email', 'testing_user@example.com');
        $this->assertEquals($response->json('data.access_token'), $response->json('data.token'));
    }

    public function test_auth_me_returns_nip_alias(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $response->assertJsonPath('data.nip', '1234567890');
        $response->assertJsonPath('data.nik', '1234567890');
    }

    public function test_sppb_list_supports_limit_alias_search_and_status_normalization(): void
    {
        Sanctum::actingAs($this->user);

        SppbHeader::factory()->count(5)->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'status' => SppbStatus::WAITING_APPROVAL->value,
            'purpose' => 'Pembelian sparepart rutin',
        ]);

        SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'status' => SppbStatus::RELEASE_IN_PROGRESS->value,
            'purpose' => 'Kebutuhan khusus pabrik',
        ]);

        // Test limit alias
        $responseLimit = $this->getJson('/api/v1/sppb?page=1&limit=2');
        $responseLimit->assertStatus(200);
        $responseLimit->assertJsonPath('meta.per_page', 2);

        // Test status normalization SUBMITTED -> WAITING_APPROVAL
        $responseStatus = $this->getJson('/api/v1/sppb?status=SUBMITTED');
        $responseStatus->assertStatus(200);
        $responseStatus->assertJsonPath('meta.total', 5);

        // Test search filter
        $responseSearch = $this->getJson('/api/v1/sppb?search=sparepart');
        $responseSearch->assertStatus(200);
        $responseSearch->assertJsonPath('meta.total', 5);
    }

    public function test_sppb_detail_returns_creator_and_items_aliases(): void
    {
        Sanctum::actingAs($this->user);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'origin_location_id' => $this->location->id,
            'destination_location_id' => $this->location->id,
        ]);

        SppbDetail::factory()->create([
            'sppb_header_id' => $sppb->id,
            'item_id' => $this->item->id,
            'unit_id' => $this->unit->id,
            'quantity' => 10,
        ]);

        $response = $this->getJson('/api/v1/sppb/'.$sppb->uuid);

        $response->assertStatus(200);
        $response->assertJsonPath('data.creator.id', $this->user->id);
        $response->assertJsonPath('data.requester.id', $this->user->id);
        $this->assertNotEmpty($response->json('data.items'));
        $this->assertNotEmpty($response->json('data.details'));
    }

    public function test_sppb_approve_compatibility_endpoint(): void
    {
        Sanctum::actingAs($this->user);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'origin_location_id' => $this->location->id,
            'destination_location_id' => $this->location->id,
            'status' => SppbStatus::WAITING_APPROVAL->value,
        ]);

        $workflowInstance = WorkflowInstance::factory()->create([
            'sppb_header_id' => $sppb->id,
            'status' => 'IN_PROGRESS',
            'current_sequence' => 1,
        ]);

        $step = WorkflowInstanceStep::create([
            'workflow_instance_id' => $workflowInstance->id,
            'sequence' => 1,
            'code' => 'STEP_APPROVAL_1',
            'name' => 'Persetujuan 1',
            'approver_type' => 'USER',
            'status' => 'PENDING',
        ]);

        WorkflowStepApprover::create([
            'workflow_instance_step_id' => $step->id,
            'approver_id' => $this->user->id,
            'status' => ApproverStatus::PENDING->value,
        ]);

        // Test POST /api/v1/sppb/{uuid}/approve with notes payload
        $responseApprove = $this->postJson('/api/v1/sppb/'.$sppb->uuid.'/approve', [
            'notes' => 'Disetujui via mobile compatibility route',
        ]);

        $responseApprove->assertStatus(200);
        $responseApprove->assertJsonPath('success', true);
    }

    public function test_sppb_reject_compatibility_endpoint(): void
    {
        Sanctum::actingAs($this->user);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'origin_location_id' => $this->location->id,
            'destination_location_id' => $this->location->id,
            'status' => SppbStatus::WAITING_APPROVAL->value,
        ]);

        $workflowInstance = WorkflowInstance::factory()->create([
            'sppb_header_id' => $sppb->id,
            'status' => 'IN_PROGRESS',
            'current_sequence' => 1,
        ]);

        $step = WorkflowInstanceStep::create([
            'workflow_instance_id' => $workflowInstance->id,
            'sequence' => 1,
            'code' => 'STEP_APPROVAL_1',
            'name' => 'Persetujuan 1',
            'approver_type' => 'USER',
            'status' => 'PENDING',
        ]);

        WorkflowStepApprover::create([
            'workflow_instance_step_id' => $step->id,
            'approver_id' => $this->user->id,
            'status' => ApproverStatus::PENDING->value,
        ]);

        // Test POST /api/v1/sppb/{uuid}/reject with reason payload
        $responseReject = $this->postJson('/api/v1/sppb/'.$sppb->uuid.'/reject', [
            'reason' => 'Ditolak karena perlu penyesuaian jumlah',
        ]);

        $responseReject->assertStatus(200);
        $responseReject->assertJsonPath('success', true);
    }

    public function test_goods_release_store_compatibility_endpoint(): void
    {
        Sanctum::actingAs($this->user);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'origin_location_id' => $this->location->id,
            'destination_location_id' => $this->location->id,
            'status' => SppbStatus::APPROVED->value,
        ]);

        SppbDetail::factory()->create([
            'sppb_header_id' => $sppb->id,
            'item_id' => $this->item->id,
            'unit_id' => $this->unit->id,
            'quantity' => 10,
        ]);

        $response = $this->postJson('/api/v1/goods-releases', [
            'sppb_header_id' => $sppb->id,
            'driver_name' => 'Sopir Budi',
            'vehicle_number' => 'B 1234 SJA',
            'recipient_name' => 'PT Harapan Bangsa',
            'notes' => 'Pengiriman Tahap 1',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.driver_name', 'Sopir Budi');
    }

    public function test_goods_release_epod_confirm_receipt_compatibility(): void
    {
        Sanctum::actingAs($this->user);

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'origin_location_id' => $this->location->id,
            'destination_location_id' => $this->location->id,
            'status' => SppbStatus::RELEASE_IN_PROGRESS->value,
        ]);

        $release = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'status' => 'IN_TRANSIT',
            'recipient_name' => 'Penerima Awal',
        ]);

        // EPOD confirm-receipt using legacy aliases (signature, notes)
        $response = $this->postJson('/api/v1/goods-releases/'.$release->uuid.'/confirm-receipt', [
            'recipient_name' => 'Nama Penerima Lapangan',
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'notes' => 'Barang diterima lengkap di lokasi',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.recipient_name', 'Nama Penerima Lapangan');
        $response->assertJsonPath('data.has_signature', true);
    }

    public function test_sppb_headers_route_alias(): void
    {
        Sanctum::actingAs($this->user);

        SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'status' => SppbStatus::WAITING_APPROVAL->value,
            'document_number' => 'SPPB/2026/08/999',
        ]);

        $response = $this->getJson('/api/v1/sppb-headers');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_dashboard_metrics_endpoint(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/dashboard/metrics');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'data' => [
                'total_sppb',
                'pending_approvals',
                'ready_for_release',
                'completed_today',
                'critical_alerts',
            ],
        ]);
    }

    public function test_notifications_endpoint(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    public function test_health_check_endpoint(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonPath('success', true);
    }

    public function test_verify_barcode_accepts_full_url_and_token_payloads(): void
    {
        $hash = '95886afc60e655ab0bf333da5072b6edcc04a746c2e3b5d98f5024f681656472';
        $sppb = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'status' => SppbStatus::APPROVED->value,
        ]);

        $goodsRelease = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
            'status' => 'RELEASED',
            'verification_hash' => $hash,
        ]);

        $fullUrl = 'https://e-sppb.engiboard.web.id/verify/document/'.$hash;

        // Test POST /api/v1/verify-barcode with full URL payload in 'barcode' key
        $responseUrl = $this->postJson('/api/v1/verify-barcode', [
            'barcode' => $fullUrl,
        ]);

        $responseUrl->assertStatus(200);
        $responseUrl->assertJsonPath('status', 'VALID');
        $responseUrl->assertJsonPath('success', true);

        // Test POST /api/v1/verify-barcode with token in 'code' key
        $responseCode = $this->postJson('/api/v1/verify-barcode', [
            'code' => $hash,
        ]);

        $responseCode->assertStatus(200);
        $responseCode->assertJsonPath('status', 'VALID');
        $responseCode->assertJsonPath('success', true);
    }
}
