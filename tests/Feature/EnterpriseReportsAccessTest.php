<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Department;
use App\Models\DocumentAccess;
use App\Models\Plant;
use App\Models\User;
use App\Services\Reporting\ReportAccessService;
use App\Services\Reporting\ReportRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EnterpriseReportsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_reports_1_to_6_are_registered(): void
    {
        $registry = app(ReportRegistry::class);
        $options = $registry->getOptions();

        $this->assertArrayHasKey('sppb', $options);
        $this->assertArrayHasKey('goods_release_search', $options);
        $this->assertArrayHasKey('document_validation_log', $options);
        $this->assertArrayHasKey('sppb_item_fulfillment', $options);
        $this->assertArrayHasKey('item_receipt_discrepancy', $options);
        $this->assertArrayHasKey('asset_movement_history', $options);
    }

    public function test_document_access_scopes_user_report_query_to_engineering_department(): void
    {
        $plant = Plant::factory()->create(['code' => 'ENG-PLANT']);
        $engDept = Department::factory()->create(['plant_id' => $plant->id, 'name' => 'Engineering']);
        $hrDept = Department::factory()->create(['plant_id' => $plant->id, 'name' => 'HR']);

        $user = User::factory()->create([
            'plant_id' => $plant->id,
            'department_id' => $engDept->id,
        ]);

        DocumentAccess::create([
            'user_id' => $user->id,
            'plant_id' => $plant->id,
            'department_id' => $engDept->id,
            'module' => 'sppb',
            'can_view' => true,
        ]);

        $accessService = app(ReportAccessService::class);
        $scope = $accessService->getScopeForUser($user);

        $this->assertEquals([$plant->id], $scope->allowedPlants);
        $this->assertEquals([$engDept->id], $scope->allowedDepartments);
    }

    public function test_super_admin_gets_unrestricted_report_access(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($superAdminRole);

        $accessService = app(ReportAccessService::class);
        $scope = $accessService->getScopeForUser($admin);

        $this->assertEmpty($scope->allowedPlants);
        $this->assertEmpty($scope->allowedDepartments);
        $this->assertTrue($scope->canPreview);
        $this->assertTrue($scope->canExport);
    }
}
