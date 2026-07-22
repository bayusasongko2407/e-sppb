<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckMaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'requester', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@esppb.local',
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->regularUser = User::factory()->create([
            'is_active' => true,
        ]);
        $this->regularUser->assignRole('requester');
    }

    public function test_when_maintenance_is_disabled_regular_user_gets_forbidden_not_maintenance(): void
    {
        AppSetting::set('op_maintenance_mode', false, 'general', 'boolean');

        $this->actingAs($this->regularUser);

        $response = $this->get('/');
        // When maintenance is disabled, they get 403 because they are not allowed to access the admin dashboard,
        // but they do NOT get 503 Service Unavailable!
        $response->assertStatus(403);
    }

    public function test_when_maintenance_is_enabled_guest_and_regular_user_are_blocked(): void
    {
        AppSetting::set('op_maintenance_mode', true, 'general', 'boolean');
        AppSetting::set('op_maintenance_message', 'Sistem sedang main', 'general', 'string');

        // 1. Guest blocked with 503
        $response = $this->get('/');
        $response->assertStatus(503);
        $response->assertSee('Sistem sedang main');

        // 2. Regular user blocked with 503
        $this->actingAs($this->regularUser);
        $response = $this->get('/');
        $response->assertStatus(503);
        $response->assertSee('Sistem sedang main');
    }

    public function test_when_maintenance_is_enabled_admins_are_exempted(): void
    {
        AppSetting::set('op_maintenance_mode', true, 'general', 'boolean');

        $this->actingAs($this->superAdmin);

        $response = $this->get('/');
        $this->assertNotEquals(503, $response->status());
    }

    public function test_when_maintenance_is_enabled_login_routes_are_accessible(): void
    {
        AppSetting::set('op_maintenance_mode', true, 'general', 'boolean');

        $response = $this->get('/login');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }
}
