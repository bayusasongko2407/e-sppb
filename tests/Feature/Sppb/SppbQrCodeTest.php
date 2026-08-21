<?php

declare(strict_types=1);

namespace Tests\Feature\Sppb;

use App\Models\Department;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use App\Services\DocumentVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SppbQrCodeTest extends TestCase
{
    use RefreshDatabase;

    private Plant $plant;

    private Department $department;

    private User $user;

    private SppbHeader $sppb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plant = Plant::factory()->create();
        $this->department = Department::factory()->create(['plant_id' => $this->plant->id]);
        $this->user = User::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
        ]);

        Permission::firstOrCreate(['name' => 'view_sppbheader', 'guard_name' => 'web']);
        $this->user->givePermissionTo('view_sppbheader');

        $this->sppb = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'document_number' => 'SPPB/TEST/2026/08/0001',
            'status' => 'APPROVED',
        ]);
    }

    public function test_user_with_permission_can_generate_sppb_qr_code_json(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/sppb/'.$this->sppb->uuid.'/qr-code');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'sppb_id',
                'sppb_uuid',
                'document_number',
                'status',
                'verification_type',
                'qr_payload',
                'verification_url',
                'api_verification_url',
                'public_verification_url',
                'qr_image_base64',
                'generated_at',
            ],
            'timestamp',
            'request_id',
        ]);

        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.sppb_id', $this->sppb->id);
        $response->assertJsonPath('data.sppb_uuid', $this->sppb->uuid);
        $response->assertJsonPath('data.document_number', 'SPPB/TEST/2026/08/0001');
        $response->assertJsonPath('data.verification_type', 'LARAVEL_CRYPT_AES256');

        // Test that the encrypted payload can be decoded and verified
        $qrPayload = $response->json('data.qr_payload');
        $this->assertNotEmpty($qrPayload);

        $verificationService = app(DocumentVerificationService::class);
        $verifyResult = $verificationService->verifyDocument($qrPayload);

        $this->assertEquals('VALID', $verifyResult['status']);
        $this->assertNotNull($verifyResult['data']);
        $this->assertEquals('SPPB/TEST/2026/08/0001', $verifyResult['data']['document_number']);

        // Test that visiting the verification URL in a web browser renders the HTML verification certificate
        $webVerifyUrl = $response->json('data.verification_url');
        $webResponse = $this->get($webVerifyUrl);
        $webResponse->assertStatus(200);
        $webResponse->assertViewIs('document.verify');
        $webResponse->assertSee('SPPB/TEST/2026/08/0001');
        $webResponse->assertSee('DOKUMEN ASLI');
    }

    public function test_user_can_request_qr_code_as_svg_image(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->get('/api/v1/sppb/'.$this->sppb->uuid.'/qr-code?format=svg');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_user_without_permission_cannot_generate_qr_code(): void
    {
        $unauthorizedUser = User::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
        ]);

        Sanctum::actingAs($unauthorizedUser);

        $response = $this->getJson('/api/v1/sppb/'.$this->sppb->uuid.'/qr-code');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/sppb/'.$this->sppb->uuid.'/qr-code');

        $response->assertStatus(401);
    }

    public function test_qr_code_generation_returns_404_for_invalid_uuid(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/sppb/non-existent-uuid-123/qr-code');

        $response->assertStatus(404);
    }
}
