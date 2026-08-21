<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Attachment;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiCoverageAndDocsTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $user;

    private Plant $plant;

    private Department $department;

    private Location $originLocation;

    private Location $destLocation;

    private Unit $unit;

    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions
        $permissions = [
            'view_any_sppbheader',
            'view_sppbheader',
            'create_sppbheader',
            'update_sppbheader',
            'delete_sppbheader',
            'view_sppbdetail',
            'create_sppbdetail',
            'update_sppbdetail',
            'delete_sppbdetail',
            'view_attachment',
            'create_attachment',
            'delete_attachment',
            'view_sppbstatuslog',
            'view_any_goodsrelease',
            'create_goodsrelease',
            'view_any_workflowdelegation',
            'view_workflowdelegation',
            'create_workflowdelegation',
            'update_workflowdelegation',
            'delete_workflowdelegation',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $this->plant = Plant::factory()->create(['is_active' => true]);
        $this->department = Department::factory()->create(['plant_id' => $this->plant->id, 'is_active' => true]);
        $this->originLocation = Location::factory()->create(['plant_id' => $this->plant->id, 'is_active' => true]);
        $this->destLocation = Location::factory()->create(['plant_id' => $this->plant->id, 'is_active' => true]);
        $this->unit = Unit::factory()->create(['name' => 'Pcs']);
        $this->item = Item::factory()->create(['unit_id' => $this->unit->id, 'is_active' => true]);

        $this->superAdmin = User::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->user = User::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
        ]);
        $this->user->givePermissionTo($permissions);
    }

    #[Test]
    public function it_serves_api_docs_and_markdown_spec_cleanly(): void
    {
        // Unified /docs portal
        $docsResponse = $this->get('/docs');
        $docsResponse->assertOk();
        $docsResponse->assertSee('Panduan Pengguna');
        $docsResponse->assertSee('API Reference');
        $docsResponse->assertSee('Panduan Mobile');
        $docsResponse->assertSee('AI Studio Prompt');
        $docsResponse->assertSee('elements-api');

        // /docs/api-reference redirects to unified docs portal with ?tab=api
        $redirectResponse = $this->get('/docs/api-reference');
        $redirectResponse->assertRedirect('/docs?tab=api');
        $redirectResponse->assertStatus(301);

        // Raw Markdown prompt specification
        $mdResponse = $this->get('/docs/api.md');
        $mdResponse->assertOk();
        $mdResponse->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $mdResponse->assertSee('Context API E-SPPB Enterprise');
        $mdResponse->assertSee('export interface SppbHeader');
        $mdResponse->assertSee('export interface GoodsRelease');
        $mdResponse->assertSee('export interface WorkflowDelegation');
        $mdResponse->assertSee('export interface BrandingSettings');

        // Scramble OpenAPI schema JSON used by Stoplight Elements
        $jsonResponse = $this->get('/docs/api.json');
        $jsonResponse->assertOk();
        $jsonResponse->assertJsonPath('openapi', fn ($val) => str_starts_with((string) $val, '3.'));
    }

    #[Test]
    public function it_handles_sppb_attachment_lifecycle_via_api(): void
    {
        Storage::fake('private');

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $this->plant->id,
            'department_id' => $this->department->id,
            'requester_id' => $this->user->id,
            'status' => 'DRAFT',
        ]);

        $file = UploadedFile::fake()->create('dokumen-pendukung.pdf', 500, 'application/pdf');

        // Upload attachment
        $uploadResponse = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/sppb/{$sppb->uuid}/attachments", [
                'file' => $file,
            ]);

        $uploadResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.original_name', 'dokumen-pendukung.pdf');

        $attUuid = $uploadResponse->json('data.uuid');

        $this->assertDatabaseHas('attachments', [
            'uuid' => $attUuid,
            'sppb_header_id' => $sppb->id,
            'original_name' => 'dokumen-pendukung.pdf',
        ]);

        // List attachments
        $listResponse = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/sppb/{$sppb->uuid}/attachments");

        $listResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        // Delete attachment
        $deleteResponse = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/sppb/{$sppb->uuid}/attachments/{$attUuid}");

        $deleteResponse->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('attachments', [
            'uuid' => $attUuid,
        ]);
    }

    #[Test]
    public function it_handles_workflow_delegations_via_api(): void
    {
        $delegate = User::factory()->create(['plant_id' => $this->plant->id]);

        // Create delegation
        $createResponse = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workflow/delegations', [
                'delegate_id' => $delegate->id,
                'plant_id' => $this->plant->id,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addDays(7)->toDateString(),
                'reason' => 'Tugas luar kota dinas lapangan',
                'is_active' => true,
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.reason', 'Tugas luar kota dinas lapangan')
            ->assertJsonPath('data.delegator_id', $this->user->id);

        $delegationId = $createResponse->json('data.id');

        // List delegations
        $listResponse = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/workflow/delegations');

        $listResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        // Update delegation
        $updateResponse = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/workflow/delegations/{$delegationId}", [
                'reason' => 'Perpanjangan tugas luar kota',
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.reason', 'Perpanjangan tugas luar kota');

        // Cancel delegation
        $cancelResponse = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workflow/delegations/{$delegationId}");

        $cancelResponse->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('workflow_delegations', [
            'id' => $delegationId,
            'is_active' => false,
        ]);
    }

    #[Test]
    public function it_serves_system_health_and_dashboard_metrics(): void
    {
        $healthResponse = $this->getJson('/api/v1/health');
        $healthResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('system_status.database', 'OK')
            ->assertJsonPath('system_status.qr_decoder', 'OPERATIONAL');

        $metricsResponse = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/dashboard/metrics');

        $metricsResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'total_sppb',
                    'pending_approvals',
                    'ready_for_release',
                    'completed_today',
                ],
            ]);

        $sandboxResponse = $this->getJson('/api/v1/public/sandbox-info');
        $sandboxResponse->assertOk()
            ->assertJsonPath('success', true);
    }
}
