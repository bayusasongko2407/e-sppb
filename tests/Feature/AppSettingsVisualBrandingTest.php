<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\AppSettings;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppSettingsVisualBrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_save_visual_branding_settings_including_login_logo_height(): void
    {
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        $this->actingAs($admin);

        Livewire::test(AppSettings::class)
            ->fillForm([
                'app_custom_name' => 'E-SPPB Enterprise Test',
                'app_primary_color' => '#2563EB',
                'logo_height' => 45,
                'logo_login_height' => 80,
                'company_name' => 'PT Santos Jaya Abadi',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(80, AppSetting::get('logo_login_height'));
        $this->assertEquals(45, AppSetting::get('logo_height'));
        $this->assertEquals('E-SPPB Enterprise Test', AppSetting::get('app_custom_name'));
    }

    public function test_app_setting_get_handles_array_and_json_stored_logo_paths(): void
    {
        AppSetting::set('logo_login', ['logos/my-login-logo.png'], 'visual', 'string');

        $logoPath = AppSetting::get('logo_login');

        $this->assertEquals('logos/my-login-logo.png', $logoPath);
    }
}
