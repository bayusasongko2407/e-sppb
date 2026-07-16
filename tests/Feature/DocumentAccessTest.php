<?php

namespace Tests\Feature;

use App\Enums\SppbStatus;
use App\Filament\Resources\DocumentAccesses\DocumentAccessResource;
use App\Filament\Resources\DocumentAccesses\Pages\CreateDocumentAccess;
use App\Filament\Resources\DocumentAccesses\Pages\EditDocumentAccess;
use App\Filament\Resources\GoodsReleases\GoodsReleaseResource;
use App\Filament\Resources\SppbHeaders\SppbHeaderResource;
use App\Models\Department;
use App\Models\DocumentAccess;
use App\Models\GoodsRelease;
use App\Models\Plant;
use App\Models\SppbHeader;
use App\Models\User;
use App\Policies\GoodsReleasePolicy;
use App\Policies\SppbHeaderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_document_access_checks(): void
    {
        $user = User::factory()->create();
        $plant = Plant::factory()->create();
        $department = Department::factory()->create();

        // Initially no access
        $this->assertFalse($user->hasDocumentAccess('sppb', 'view', $plant->id, $department->id));

        // Grant access
        DocumentAccess::create([
            'user_id' => $user->id,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'module' => 'sppb',
            'can_view' => true,
            'can_create' => true,
        ]);

        $user->load('documentAccesses');

        $this->assertTrue($user->hasDocumentAccess('sppb', 'view', $plant->id, $department->id));
        $this->assertTrue($user->hasDocumentAccess('sppb', 'create', $plant->id, $department->id));
        $this->assertFalse($user->hasDocumentAccess('sppb', 'edit', $plant->id, $department->id));
    }

    public function test_sppb_policy_utilizes_document_access(): void
    {
        // Create the permission to prevent Spatie throwing PermissionDoesNotExist
        Permission::create(['name' => 'view_sppbheader', 'guard_name' => 'web']);

        $user = User::factory()->create(['is_active' => true]);
        $plant = Plant::factory()->create();
        $department = Department::factory()->create();

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'status' => SppbStatus::DRAFT->value,
        ]);

        $policy = new SppbHeaderPolicy;

        // User doesn't have view permission normally
        $this->assertFalse($policy->view($user, $sppb));

        // Grant access
        DocumentAccess::create([
            'user_id' => $user->id,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'module' => 'sppb',
            'can_view' => true,
        ]);

        $user->load('documentAccesses');
        $this->assertTrue($policy->view($user, $sppb));
    }

    public function test_filament_resource_query_scoping(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $plant1 = Plant::factory()->create();
        $plant2 = Plant::factory()->create();
        $department = Department::factory()->create();

        $sppb1 = SppbHeader::factory()->create([
            'plant_id' => $plant1->id,
            'department_id' => $department->id,
        ]);

        $sppb2 = SppbHeader::factory()->create([
            'plant_id' => $plant2->id,
            'department_id' => $department->id,
        ]);

        // Grant access only to plant1
        DocumentAccess::create([
            'user_id' => $user->id,
            'plant_id' => $plant1->id,
            'department_id' => $department->id,
            'module' => 'sppb',
            'can_view' => true,
        ]);

        $this->actingAs($user);

        $query = SppbHeaderResource::getEloquentQuery();

        $this->assertTrue($query->where('id', $sppb1->id)->exists());

        // Re-evaluate query for sppb2
        $query2 = SppbHeaderResource::getEloquentQuery();
        $this->assertFalse($query2->where('id', $sppb2->id)->exists());
    }

    public function test_goods_release_policy_and_query_scoping(): void
    {
        // Spatie permission setup
        Permission::create(['name' => 'view_goodsrelease', 'guard_name' => 'web']);

        $user = User::factory()->create(['is_active' => true]);
        $plant1 = Plant::factory()->create();
        $plant2 = Plant::factory()->create();
        $department = Department::factory()->create();

        $sppb1 = SppbHeader::factory()->create([
            'plant_id' => $plant1->id,
            'department_id' => $department->id,
        ]);

        $sppb2 = SppbHeader::factory()->create([
            'plant_id' => $plant2->id,
            'department_id' => $department->id,
        ]);

        $release1 = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb1->id,
        ]);

        $release2 = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb2->id,
        ]);

        $policy = new GoodsReleasePolicy;

        // No access initially
        $this->assertFalse($policy->view($user, $release1));

        // Grant access
        DocumentAccess::create([
            'user_id' => $user->id,
            'plant_id' => $plant1->id,
            'department_id' => $department->id,
            'module' => 'goods_release',
            'can_view' => true,
        ]);

        $user->load('documentAccesses');
        $this->assertTrue($policy->view($user, $release1));
        $this->assertFalse($policy->view($user, $release2));

        // Scope test
        $this->actingAs($user);
        $query = GoodsReleaseResource::getEloquentQuery();

        $this->assertTrue($query->where('id', $release1->id)->exists());

        $query2 = GoodsReleaseResource::getEloquentQuery();
        $this->assertFalse($query2->where('id', $release2->id)->exists());
    }

    public function test_filament_document_access_multiple_combinations_creation_and_update(): void
    {
        $user = User::factory()->create();
        $plant1 = Plant::factory()->create();
        $plant2 = Plant::factory()->create();
        $dept1 = Department::factory()->create();
        $dept2 = Department::factory()->create();

        // 1. Test Creation of Multiple Combinations
        $createPage = new CreateDocumentAccess;
        $refMethod = new \ReflectionMethod($createPage, 'handleRecordCreation');
        $refMethod->setAccessible(true);
        $refMethod->invoke($createPage, [
            'user_id' => $user->id,
            'plant_id' => [$plant1->id, $plant2->id],
            'department_id' => [$dept1->id, $dept2->id],
            'module' => ['sppb', 'goods_release'],
            'can_view' => true,
            'can_create' => true,
            'can_edit' => false,
            'can_delete' => false,
        ]);

        // Assert all 2 * 2 * 2 = 8 combinations exist
        foreach ([$plant1->id, $plant2->id] as $plantId) {
            foreach ([$dept1->id, $dept2->id] as $deptId) {
                foreach (['sppb', 'goods_release'] as $module) {
                    $this->assertDatabaseHas('document_accesses', [
                        'user_id' => $user->id,
                        'plant_id' => $plantId,
                        'department_id' => $deptId,
                        'module' => $module,
                        'can_view' => true,
                    ]);
                }
            }
        }

        // 2. Test Edit/Update of Multiple Combinations
        $record = DocumentAccess::where('user_id', $user->id)
            ->where('plant_id', $plant1->id)
            ->where('department_id', $dept1->id)
            ->where('module', 'sppb')
            ->first();

        $editPage = new EditDocumentAccess;
        $editPage->record = $record;

        $refUpdateMethod = new \ReflectionMethod($editPage, 'handleRecordUpdate');
        $refUpdateMethod->setAccessible(true);
        // Change selection to only (plant1, dept1, goods_release)
        $refUpdateMethod->invoke($editPage, $record, [
            'user_id' => $user->id,
            'plant_id' => [$plant1->id],
            'department_id' => [$dept1->id],
            'module' => ['goods_release'],
            'can_view' => true,
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => false,
        ]);

        // (plant1, dept1, sppb) should be deleted because it is no longer in the list
        $this->assertDatabaseMissing('document_accesses', [
            'user_id' => $user->id,
            'plant_id' => $plant1->id,
            'department_id' => $dept1->id,
            'module' => 'sppb',
        ]);

        // (plant1, dept1, goods_release) should still exist and have can_edit updated to true
        $this->assertDatabaseHas('document_accesses', [
            'user_id' => $user->id,
            'plant_id' => $plant1->id,
            'department_id' => $dept1->id,
            'module' => 'goods_release',
            'can_edit' => true,
        ]);
    }

    public function test_document_access_query_groups_by_user_id_and_eager_loads_relations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $plant = Plant::factory()->create();
        $department = Department::factory()->create();

        // User 1 has 2 accesses
        DocumentAccess::create([
            'user_id' => $user1->id,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'module' => 'sppb',
        ]);
        DocumentAccess::create([
            'user_id' => $user1->id,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'module' => 'goods_release',
        ]);

        // User 2 has 1 access
        DocumentAccess::create([
            'user_id' => $user2->id,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'module' => 'sppb',
        ]);

        $query = DocumentAccessResource::getEloquentQuery();
        $results = $query->get();

        // Should return exactly 2 grouped records
        $this->assertCount(2, $results);

        $userIds = $results->pluck('user_id')->toArray();
        $this->assertContains($user1->id, $userIds);
        $this->assertContains($user2->id, $userIds);
    }

    public function test_document_access_uses_secure_route_binding(): void
    {
        $user = User::factory()->create();
        $plant = Plant::factory()->create();
        $department = Department::factory()->create();

        $access = DocumentAccess::create([
            'user_id' => $user->id,
            'plant_id' => $plant->id,
            'department_id' => $department->id,
            'module' => 'sppb',
        ]);

        $routeKey = $access->getRouteKey();

        // The route key should be encrypted and should not contain the original integer ID
        $this->assertNotEquals((string) $access->id, $routeKey);

        // Resolve the model using the route key
        $resolved = (new DocumentAccess)->resolveRouteBinding($routeKey);

        $this->assertNotNull($resolved);
        $this->assertEquals($access->id, $resolved->id);

        // Resolve with an invalid/tampered route key should return null
        $resolvedInvalid = (new DocumentAccess)->resolveRouteBinding('invalid-key');
        $this->assertNull($resolvedInvalid);
    }

    public function test_sppb_header_and_goods_release_uses_secure_route_binding(): void
    {
        $plant = Plant::factory()->create();
        $department = Department::factory()->create();

        $sppb = SppbHeader::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $department->id,
        ]);

        $release = GoodsRelease::factory()->create([
            'sppb_header_id' => $sppb->id,
        ]);

        // SppbHeader check
        $sppbRouteKey = $sppb->getRouteKey();
        $this->assertNotEquals((string) $sppb->id, $sppbRouteKey);
        $resolvedSppb = (new SppbHeader)->resolveRouteBinding($sppbRouteKey);
        $this->assertNotNull($resolvedSppb);
        $this->assertEquals($sppb->id, $resolvedSppb->id);

        // GoodsRelease check
        $releaseRouteKey = $release->getRouteKey();
        $this->assertNotEquals((string) $release->id, $releaseRouteKey);
        $resolvedRelease = (new GoodsRelease)->resolveRouteBinding($releaseRouteKey);
        $this->assertNotNull($resolvedRelease);
        $this->assertEquals($release->id, $resolvedRelease->id);
    }
}
