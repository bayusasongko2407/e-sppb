<?php

declare(strict_types=1);

namespace Tests\Feature\Document;

use App\Contracts\DocumentRendererInterface;
use App\Jobs\ProcessDocumentGenerationJob;
use App\Models\DocumentGeneration;
use App\Models\DocumentPage;
use App\Models\DocumentTemplate;
use App\Models\GoodsRelease;
use App\Models\Plant;
use App\Models\User;
use App\Services\DocumentGenerationService;
use App\Services\DocumentVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentVerificationTest extends TestCase
{
    use RefreshDatabase;

    private DocumentGenerationService $generationService;

    private DocumentVerificationService $verificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generationService = app(DocumentGenerationService::class);
        $this->verificationService = app(DocumentVerificationService::class);
    }

    // =========================================================================
    // Helper: create a generation, process it, return [generation, page]
    // =========================================================================

    private function createReadyGenerationWithPage(): array
    {
        Storage::fake('private');

        $plant = Plant::factory()->create();
        $user = User::factory()->create();
        $template = DocumentTemplate::factory()->create(['plant_id' => $plant->id]);

        $generation = DocumentGeneration::factory()->create([
            'document_template_id' => $template->id,
            'plant_id' => $plant->id,
            'generated_by_id' => $user->id,
            'status' => 'QUEUED',
            'render_payload' => [],
            'revoked_at' => null,
            'expires_at' => null,
        ]);

        $job = new ProcessDocumentGenerationJob($generation->id);
        $job->handle($this->generationService, app(DocumentRendererInterface::class));
        $generation->refresh();

        $page = DocumentPage::where('document_generation_id', $generation->id)->firstOrFail();

        return [$generation, $page];
    }

    // =========================================================================
    // SHA256 Token Derivation
    // =========================================================================

    public function test_derive_token_produces_sha256_hex_string(): void
    {
        $token = DocumentVerificationService::deriveToken('some-uuid-123', 1);

        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function test_derive_token_is_deterministic(): void
    {
        $uuid = 'test-generation-uuid-abc';
        $tokenA = DocumentVerificationService::deriveToken($uuid, 1);
        $tokenB = DocumentVerificationService::deriveToken($uuid, 1);

        $this->assertEquals($tokenA, $tokenB);
    }

    public function test_derive_token_differs_per_page(): void
    {
        $uuid = 'test-generation-uuid-abc';
        $tokenPage1 = DocumentVerificationService::deriveToken($uuid, 1);
        $tokenPage2 = DocumentVerificationService::deriveToken($uuid, 2);

        $this->assertNotEquals($tokenPage1, $tokenPage2);
    }

    public function test_derive_token_differs_per_generation(): void
    {
        $tokenA = DocumentVerificationService::deriveToken('uuid-aaa', 1);
        $tokenB = DocumentVerificationService::deriveToken('uuid-bbb', 1);

        $this->assertNotEquals($tokenA, $tokenB);
    }

    // =========================================================================
    // ProcessDocumentGenerationJob — SHA256 token creation
    // =========================================================================

    public function test_process_job_creates_document_page_with_sha256_token(): void
    {
        [$generation, $page] = $this->createReadyGenerationWithPage();

        $this->assertEquals('READY', $generation->status);
        $this->assertEquals(1, $page->page_number);

        // Verify the SHA256 token is deterministically derived from generation UUID + page
        $expectedToken = DocumentVerificationService::deriveToken($generation->uuid, 1);
        $this->assertEquals($expectedToken, $page->verification_token_hash);
        $this->assertEquals(64, strlen($page->verification_token_hash));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $page->verification_token_hash);
    }

    public function test_process_job_creates_storage_directory_if_missing(): void
    {
        [$generation] = $this->createReadyGenerationWithPage();

        // The file must exist on disk — directory was auto-created
        $this->assertEquals('READY', $generation->status);
        Storage::disk('private')->assertExists($generation->path);
    }

    // =========================================================================
    // DocumentVerificationService::verifyBySha256Token
    // =========================================================================

    public function test_verify_returns_valid_for_ready_document(): void
    {
        [$generation, $page] = $this->createReadyGenerationWithPage();

        $result = $this->verificationService->verifyBySha256Token($page->verification_token_hash);

        $this->assertEquals('VALID', $result['status']);
        $this->assertNotNull($result['validation_id']);
        $this->assertIsArray($result['data']);
        $this->assertEquals(1, $result['data']['page_number']);
    }

    public function test_verify_returns_not_found_for_invalid_token(): void
    {
        $fakeToken = str_repeat('a', 64);

        $result = $this->verificationService->verifyBySha256Token($fakeToken);

        $this->assertEquals('NOT_FOUND', $result['status']);
        $this->assertNull($result['data']);
        $this->assertNotNull($result['validation_id']);
    }

    public function test_verify_returns_superseded_for_superseded_document(): void
    {
        [$generation, $page] = $this->createReadyGenerationWithPage();

        // Mark the generation as SUPERSEDED
        $generation->update(['status' => 'SUPERSEDED']);

        $result = $this->verificationService->verifyBySha256Token($page->verification_token_hash);

        $this->assertEquals('SUPERSEDED', $result['status']);
    }

    public function test_verify_returns_revoked_for_revoked_document(): void
    {
        [$generation, $page] = $this->createReadyGenerationWithPage();

        // Revoke the document
        $generation->update(['status' => 'REVOKED', 'revoked_at' => now()]);

        $result = $this->verificationService->verifyBySha256Token($page->verification_token_hash);

        $this->assertEquals('REVOKED', $result['status']);
    }

    public function test_verify_logs_valid_validation_to_database(): void
    {
        [$generation, $page] = $this->createReadyGenerationWithPage();

        $this->verificationService->verifyBySha256Token(
            $page->verification_token_hash,
            '192.168.1.1',
            'Mozilla/5.0 TestAgent'
        );

        $this->assertDatabaseHas('document_validations', [
            'document_generation_id' => $generation->id,
            'document_page_id' => $page->id,
            'validation_result' => 'VALID',
            'verification_channel' => 'PUBLIC_QR',
        ]);
    }

    public function test_verify_logs_not_found_validation_to_database(): void
    {
        $fakeToken = str_repeat('f', 64);

        $this->verificationService->verifyBySha256Token($fakeToken);

        $this->assertDatabaseHas('document_validations', [
            'document_generation_id' => null,
            'document_page_id' => null,
            'validation_result' => 'NOT_FOUND',
        ]);
    }

    public function test_verify_returns_data_with_all_required_fields(): void
    {
        [$generation, $page] = $this->createReadyGenerationWithPage();

        $result = $this->verificationService->verifyBySha256Token($page->verification_token_hash);

        $this->assertEquals('VALID', $result['status']);
        $this->assertArrayHasKey('document_type', $result['data']);
        $this->assertArrayHasKey('document_number', $result['data']);
        $this->assertArrayHasKey('status_sppb', $result['data']);
        $this->assertArrayHasKey('plant_name', $result['data']);
        $this->assertArrayHasKey('page_number', $result['data']);
        $this->assertArrayHasKey('total_pages', $result['data']);
        $this->assertArrayHasKey('fingerprint', $result['data']);
    }

    public function test_verify_returns_not_found_when_document_generation_relation_missing(): void
    {
        [$generation, $page] = $this->createReadyGenerationWithPage();

        // Delete parent generation record directly in DB to simulate orphan page
        DocumentGeneration::where('id', $generation->id)->delete();

        $result = $this->verificationService->verifyBySha256Token($page->verification_token_hash);

        $this->assertEquals('NOT_FOUND', $result['status']);
        $this->assertNull($result['data']);
    }

    // =========================================================================
    // HTTP Route Tests
    // =========================================================================

    public function test_verify_route_returns_html_view_for_valid_token(): void
    {
        [$generation, $page] = $this->createReadyGenerationWithPage();

        $response = $this->get(route('document.verify', ['sha256Token' => $page->verification_token_hash]));

        $response->assertStatus(200);
        $response->assertViewIs('document.verify');
        $response->assertViewHas('status', 'VALID');
        $response->assertViewHas('sha256_token', $page->verification_token_hash);
    }

    public function test_verify_route_returns_not_found_view_for_invalid_token(): void
    {
        $fakeToken = str_repeat('b', 64);

        $response = $this->get(route('document.verify', ['sha256Token' => $fakeToken]));

        $response->assertStatus(200);
        $response->assertViewIs('document.verify');
        $response->assertViewHas('status', 'NOT_FOUND');
    }

    public function test_verify_route_returns_json_when_requested(): void
    {
        [$generation, $page] = $this->createReadyGenerationWithPage();

        $response = $this->getJson(route('document.verify', ['sha256Token' => $page->verification_token_hash]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'validation_id', 'data' => ['document_type', 'page_number', 'fingerprint']]);
        $response->assertJson(['status' => 'VALID']);
    }

    public function test_verify_route_returns_404_json_for_invalid_token(): void
    {
        $fakeToken = str_repeat('c', 64);

        $response = $this->getJson(route('document.verify', ['sha256Token' => $fakeToken]));

        $response->assertStatus(404);
        $response->assertJson(['status' => 'NOT_FOUND']);
    }

    public function test_verify_route_rejects_non_hex_characters_in_token(): void
    {
        // Route has constraint [a-f0-9]{64} — tokens with uppercase or non-hex chars should 404
        $response = $this->get('/verify/document/'.str_repeat('G', 64));

        $response->assertStatus(404);
    }

    public function test_verify_route_rejects_too_short_token(): void
    {
        // Token too short — not matching [a-f0-9]{64}
        $response = $this->get('/verify/document/'.str_repeat('a', 32));

        $response->assertStatus(404);
    }

    public function test_verify_route_is_rate_limited(): void
    {
        $fakeToken = str_repeat('d', 64);

        // Throttle is 60 per minute — send 61 requests and confirm the route is protected
        for ($i = 0; $i < 61; $i++) {
            $this->get(route('document.verify', ['sha256Token' => $fakeToken]));
        }

        $response = $this->get(route('document.verify', ['sha256Token' => $fakeToken]));

        // The throttle middleware is in place (429 on 62nd request)
        $response->assertStatus(429);
    }

    // =========================================================================
    // QR Code Decryptor & API v1 Verification Tests
    // =========================================================================

    public function test_decrypt_qr_payload_handles_laravel_crypt_string(): void
    {
        $releaseNumber = 'SJ-20260730-9999';
        $encrypted = Crypt::encryptString($releaseNumber);

        $result = $this->verificationService->decryptQrPayload($encrypted);

        $this->assertTrue($result['is_encrypted']);
        $this->assertEquals($releaseNumber, $result['decrypted']);
    }

    public function test_decrypt_qr_payload_handles_json_array_structure(): void
    {
        $releaseNumber = 'SJ-20260730-8888';
        $encrypted = Crypt::encryptString($releaseNumber);
        $jsonPayload = json_decode(base64_decode($encrypted), true);

        $result = $this->verificationService->decryptQrPayload($jsonPayload);

        $this->assertTrue($result['is_encrypted']);
        $this->assertEquals($releaseNumber, $result['decrypted']);
    }

    public function test_api_v1_verify_document_returns_goods_release_for_encrypted_qr(): void
    {
        $goodsRelease = GoodsRelease::factory()->create([
            'release_number' => 'SJ-20260730-0001',
            'delivery_date' => now()->addDays(2)->format('Y-m-d'),
            'status' => 'RELEASED',
        ]);

        $encryptedPayload = Crypt::encryptString($goodsRelease->release_number);

        $response = $this->postJson('/api/v1/verify/document', [
            'qr_data' => $encryptedPayload,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $response->assertJson([
            'status' => 'VALID',
            'data' => [
                'document_type' => 'SURAT_JALAN',
                'release_number' => 'SJ-20260730-0001',
                'status_display' => 'DALAM PENGIRIMAN',
                'decrypted_from_qr' => true,
            ],
        ]);
    }

    public function test_api_v1_health_returns_system_status_and_cors(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $response->assertJson([
            'success' => true,
            'base_url' => 'https://e-sppb.engiboard.web.id/api/v1',
            'system_status' => [
                'database' => 'OK',
                'qr_decoder' => 'OPERATIONAL',
            ],
        ]);
    }
}
