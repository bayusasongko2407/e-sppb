<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BrandingSettingApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->regularUser = User::factory()->create();
    }

    #[Test]
    public function it_fetches_public_branding_settings(): void
    {
        AppSetting::set('app_custom_name', 'E-SPPB Enterprise Pro', 'visual', 'string');
        AppSetting::set('company_name', 'PT Santos Jaya Abadi', 'general', 'string');
        AppSetting::set('app_primary_color', '#0ea5e9', 'visual', 'string');
        AppSetting::set('logo_height', 40, 'visual', 'integer');

        $response = $this->getJson('/api/v1/branding');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.app_custom_name', 'E-SPPB Enterprise Pro')
            ->assertJsonPath('data.company_name', 'PT Santos Jaya Abadi')
            ->assertJsonPath('data.app_primary_color', '#0ea5e9')
            ->assertJsonPath('data.logo_height', 40)
            ->assertJsonStructure([
                'data' => [
                    'app_custom_name',
                    'company_name',
                    'app_primary_color',
                    'logos' => [
                        'light',
                        'dark',
                        'favicon',
                        'login',
                        'pdf',
                    ],
                    'logo_height',
                    'logo_login_height',
                    'logo_pdf_position',
                    'logo_pdf_height',
                    'logo_pdf_show_address',
                ],
            ]);

        // Also verify aliases
        $this->getJson('/api/branding')->assertOk();
        $this->getJson('/api/v1/public/branding')->assertOk();
    }

    #[Test]
    public function it_denies_unauthorized_users_from_updating_branding(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->postJson('/api/v1/settings/branding', [
                'app_custom_name' => 'Hacked Name',
            ]);

        $response->assertForbidden()
            ->assertJsonPath('success', false);
    }

    #[Test]
    public function it_allows_admin_to_update_text_and_color_branding_settings(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/settings/branding', [
                'app_custom_name' => 'E-SPPB Enterprise Suite',
                'company_name' => 'PT Santos Jaya Abadi Tbk',
                'app_primary_color' => '#10B981',
                'logo_height' => 48,
                'logo_login_height' => 75,
                'logo_pdf_position' => 'center',
                'logo_pdf_height' => 50,
                'logo_pdf_show_address' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.app_custom_name', 'E-SPPB Enterprise Suite')
            ->assertJsonPath('data.company_name', 'PT Santos Jaya Abadi Tbk')
            ->assertJsonPath('data.app_primary_color', '#10B981')
            ->assertJsonPath('data.logo_height', 48)
            ->assertJsonPath('data.logo_login_height', 75)
            ->assertJsonPath('data.logo_pdf_position', 'center')
            ->assertJsonPath('data.logo_pdf_height', 50)
            ->assertJsonPath('data.logo_pdf_show_address', false);

        $this->assertEquals('E-SPPB Enterprise Suite', AppSetting::get('app_custom_name'));
        $this->assertEquals('#10B981', AppSetting::get('app_primary_color'));
    }

    #[Test]
    public function it_allows_admin_to_upload_logos_and_favicon(): void
    {
        Storage::fake('public');

        $lightLogo = UploadedFile::fake()->image('custom-logo-light.png', 200, 50);
        $darkLogo = UploadedFile::fake()->image('custom-logo-dark.png', 200, 50);
        $favicon = UploadedFile::fake()->create('custom-favicon.ico', 32, 'image/x-icon');
        $loginLogo = UploadedFile::fake()->image('custom-logo-login.png', 300, 100);
        $pdfLogo = UploadedFile::fake()->image('custom-logo-pdf.png', 400, 120);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/settings/branding', [
                'logo_light' => $lightLogo,
                'logo_dark' => $darkLogo,
                'logo_favicon' => $favicon,
                'logo_login' => $loginLogo,
                'logo_pdf' => $pdfLogo,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $lightPath = AppSetting::get('logo_light');
        $faviconPath = AppSetting::get('logo_favicon');
        $darkPath = AppSetting::get('logo_dark');

        $this->assertNotNull($lightPath);
        $this->assertNotNull($faviconPath);
        $this->assertNotNull($darkPath);

        Storage::disk('public')->assertExists($lightPath);
        Storage::disk('public')->assertExists($faviconPath);
        Storage::disk('public')->assertExists($darkPath);
    }

    #[Test]
    public function it_allows_admin_to_delete_specific_logo_or_favicon(): void
    {
        Storage::fake('public');

        $favicon = UploadedFile::fake()->create('favicon.ico', 32, 'image/x-icon');
        $storedPath = $favicon->store('logos', 'public');
        AppSetting::set('logo_favicon', $storedPath, 'visual', 'string');

        Storage::disk('public')->assertExists($storedPath);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson('/api/v1/settings/branding/logos/favicon');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logo favicon berhasil dihapus.')
            ->assertJsonPath('data.logos.favicon.path', null);

        $this->assertNull(AppSetting::get('logo_favicon'));
        Storage::disk('public')->assertMissing($storedPath);
    }

    #[Test]
    public function it_validates_logo_type_on_delete(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson('/api/v1/settings/branding/logos/invalid_type');

        $response->assertUnprocessable()
            ->assertJsonPath('success', false);
    }
}
