<?php

declare(strict_types=1);

namespace Tests\Feature\Document;

use App\Contracts\DocumentRendererInterface;
use App\DTOs\Document\DocumentGenerationData;
use App\Jobs\ProcessDocumentGenerationJob;
use App\Models\DocumentGeneration;
use App\Models\DocumentPage;
use App\Models\DocumentTemplate;
use App\Models\Plant;
use App\Models\User;
use App\Services\DocumentGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentGenerationServiceTest extends TestCase
{
    use RefreshDatabase;

    private DocumentGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DocumentGenerationService::class);
    }

    public function test_can_request_document_generation(): void
    {
        Queue::fake();

        $plant = Plant::factory()->create();
        $user = User::factory()->create();
        $template = DocumentTemplate::factory()->create([
            'plant_id' => $plant->id,
            'code' => 'SPPB-V1',
            'version' => 1,
            'document_type' => 'SPPB',
        ]);

        $dto = new DocumentGenerationData(
            documentType: 'SPPB',
            templateId: $template->id,
            plantId: $plant->id,
            generatedById: $user->id,
            renderPayload: ['foo' => 'bar'],
            sppbHeaderId: null,
            goodsReleaseId: null,
            supersedesId: null,
            isOfficial: true,
        );

        $generation = $this->service->requestGeneration($dto);

        $this->assertDatabaseHas('document_generations', [
            'id' => $generation->id,
            'status' => 'QUEUED',
            'document_type' => 'SPPB',
            'template_version' => 1,
            'is_official' => 1,
        ]);

        // Note: Actual dispatching is supposed to happen in the service or listener
        // We will dispatch it manually in the test to verify job logic
    }

    public function test_process_job_generates_pdf_and_pages(): void
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
            'render_payload' => json_encode(['test' => 123]),
        ]);

        $job = new ProcessDocumentGenerationJob($generation->id);
        $job->handle($this->service, app(DocumentRendererInterface::class));

        $generation->refresh();

        $this->assertEquals('READY', $generation->status);
        $this->assertNotNull($generation->checksum_sha256);
        $this->assertNotNull($generation->path);
        
        Storage::disk('private')->assertExists($generation->path);

        $this->assertDatabaseCount('document_pages', 1);
        $page = DocumentPage::first();
        $this->assertEquals($generation->id, $page->document_generation_id);
        $this->assertEquals(1, $page->page_number);
        $this->assertNotNull($page->verification_token_hash);
    }

    public function test_superseding_old_generation(): void
    {
        Storage::fake('private');
        $plant = Plant::factory()->create();
        $user = User::factory()->create();
        $template = DocumentTemplate::factory()->create(['plant_id' => $plant->id]);

        $oldGeneration = DocumentGeneration::factory()->create([
            'document_template_id' => $template->id,
            'plant_id' => $plant->id,
            'generated_by_id' => $user->id,
            'status' => 'READY',
            'generation_no' => 1,
        ]);

        $dto = new DocumentGenerationData(
            documentType: $oldGeneration->document_type,
            templateId: $template->id,
            plantId: $plant->id,
            generatedById: $user->id,
            renderPayload: ['foo' => 'bar2'],
            supersedesId: $oldGeneration->id,
        );

        $newGeneration = $this->service->requestGeneration($dto);

        $this->assertEquals(2, $newGeneration->generation_no);

        // Process new job
        $job = new ProcessDocumentGenerationJob($newGeneration->id);
        $job->handle($this->service, app(DocumentRendererInterface::class));

        $oldGeneration->refresh();
        $this->assertEquals('SUPERSEDED', $oldGeneration->status);
        $this->assertNotNull($oldGeneration->revoked_at);
        $this->assertEquals($user->id, $oldGeneration->revoked_by_id);
    }
}
