<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SppbStatus;
use App\Filament\Resources\DocumentAccesses\DocumentAccessResource;
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
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_document_access_checks(): void
    {
        $user = User::factory()->create();
        $plant = Plant::factory()->create();
        $department = Department::factory()->create();

        // Create Spatie permissions
        $p1 = Permission::firstOrCreate(['name' => 'view_sppbheader', 'guard_name' => 'web']);
        $p2 = Permission::firstOrCreate(['name' => 'create_sppbheader', 'guard_name' => 'web']);
        $user->givePermissionTo($p1, $p2);

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
        $p = Permission::firstOrCreate(['name' => 'view_sppbheader', 'guard_name' => 'web']);

        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo($p);

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
        $p = Permission::firstOrCreate(['name' => 'view_any_sppbheader', 'guard_name' => 'web']);
        $user->givePermissionTo($p);

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
        $p = Permission::firstOrCreate(['name' => 'view_goodsrelease', 'guard_name' => 'web']);

        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo($p);

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

    public function test_hybrid_and_wildcard_document_access(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'manager', 'guard_name' => 'web']);
        $user->assignRole($role);

        $pSppb = Permission::firstOrCreate(['name' => 'view_sppbheader', 'guard_name' => 'web']);
        $role->givePermissionTo($pSppb);

        $plant = Plant::factory()->create();
        $department = Department::factory()->create();

        // Test 1: Wildcard Plant & Dept (null) via Role
        DocumentAccess::create([
            'role_id' => $role->id,
            'plant_id' => null, // Semua plant
            'department_id' => null, // Semua departemen
            'module' => 'sppb',
            'can_view' => true,
        ]);

        $this->assertTrue($user->hasDocumentAccess('sppb', 'view', $plant->id, $department->id));
        $this->assertTrue($user->hasDocumentAccess('sppb', 'view', null, null));

        // Test 2: Specific Plant but Wildcard Dept via User
        $user2 = User::factory()->create();
        $pRelease = Permission::firstOrCreate(['name' => 'create_goodsrelease', 'guard_name' => 'web']);
        $user2->givePermissionTo($pRelease);

        $plant2 = Plant::factory()->create();
        DocumentAccess::create([
            'user_id' => $user2->id,
            'plant_id' => $plant2->id,
            'department_id' => null, // Semua departemen di plant2
            'module' => 'goods_release',
            'can_create' => true,
        ]);

        $this->assertTrue($user2->hasDocumentAccess('goods_release', 'create', $plant2->id, 999));
        $this->assertFalse($user2->hasDocumentAccess('goods_release', 'create', 999, null));
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
