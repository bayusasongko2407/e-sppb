<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Filament\Pages\NotificationSettings;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\WhatsAppService;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create([
            'email' => 'admin@esppb.local',
            'phone' => '081234567890',
        ]);
        $this->superAdmin->assignRole($superAdminRole);

        $this->regularUser = User::factory()->create([
            'email' => 'user@esppb.local',
            'phone' => '089876543210',
        ]);
        $this->regularUser->assignRole($userRole);
    }

    public function test_super_admin_can_access_notification_settings_page(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(NotificationSettings::class)
            ->assertSuccessful()
            ->assertSee('Pengaturan Notifikasi Terpadu');
    }

    public function test_non_super_admin_cannot_access_notification_settings_page(): void
    {
        $this->actingAs($this->regularUser);

        $this->assertFalse(NotificationSettings::canAccess());
    }

    public function test_notification_settings_can_be_saved(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(NotificationSettings::class)
            ->fillForm([
                'notify_system_enabled' => true,
                'notify_system_retention_days' => 60,
                'notify_event_sppb_created' => true,
                'notify_event_approval_requested' => false,
                'notify_email_enabled' => true,
                'mail_driver' => 'smtp',
                'mail_host' => 'smtp.mailtrap.io',
                'mail_port' => 2525,
                'notify_wa_enabled' => true,
                'wa_server_url' => 'http://127.0.0.1:3000/send-message',
                'wa_sender_number' => '6281234567890',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(60, AppSetting::get('notify_system_retention_days'));
        $this->assertFalse(AppSetting::get('notify_event_approval_requested'));
        $this->assertTrue(AppSetting::get('notify_email_enabled'));
        $this->assertEquals(2525, AppSetting::get('mail_port'));
        $this->assertTrue(AppSetting::get('notify_wa_enabled'));
    }

    public function test_whatsapp_service_format_phone_number(): void
    {
        $service = new WhatsAppService;

        $this->assertEquals('628123456789', $service->formatPhoneNumber('08123456789'));
        $this->assertEquals('628123456789', $service->formatPhoneNumber('+628123456789'));
        $this->assertEquals('628123456789', $service->formatPhoneNumber('628123456789'));
        $this->assertEquals('628123456789', $service->formatPhoneNumber('0812-3456-789'));
    }

    public function test_prune_notifications_command_removes_old_notifications(): void
    {
        AppSetting::set('notify_system_retention_days', 30, 'notification', 'integer');

        // Create notification older than 30 days
        DB::table('notifications')->insert([
            'id' => (string) Uuid::uuid4(),
            'type' => 'Filament\Notifications\Notification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->regularUser->id,
            'data' => json_encode(['title' => 'Old Notification']),
            'created_at' => now()->subDays(40),
            'updated_at' => now()->subDays(40),
        ]);

        // Create recent notification
        DB::table('notifications')->insert([
            'id' => (string) Uuid::uuid4(),
            'type' => 'Filament\Notifications\Notification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->regularUser->id,
            'data' => json_encode(['title' => 'New Notification']),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $this->artisan('notifications:prune')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('notifications', [
            'data->title' => 'Old Notification',
        ]);

        $this->assertDatabaseHas('notifications', [
            'data->title' => 'New Notification',
        ]);
    }

    public function test_send_test_email(): void
    {
        Mail::shouldReceive('purge')->andReturnNull();
        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function ($body, $callback) {
                return str_contains($body, 'uji coba');
            });

        $this->actingAs($this->superAdmin);

        Livewire::test(NotificationSettings::class)
            ->fillForm([
                'mail_driver' => 'smtp',
                'mail_host' => '127.0.0.1',
                'mail_port' => 1025,
                'mail_from_address' => 'admin@esppb.local',
                'mail_from_name' => 'E-SPPB',
            ])
            ->set('test_email_recipient', 'test@example.com')
            ->call('sendTestEmail')
            ->assertHasNoFormErrors();
    }

    public function test_send_test_email_via_resend(): void
    {
        Mail::shouldReceive('purge')->andReturnNull();
        Mail::shouldReceive('raw')
            ->once()
            ->withArgs(function ($body, $callback) {
                return str_contains($body, 'uji coba');
            });

        $this->actingAs($this->superAdmin);

        Livewire::test(NotificationSettings::class)
            ->fillForm([
                'mail_driver' => 'resend',
                'resend_api_key' => 're_123456',
                'mail_from_address' => 'admin@esppb.local',
                'mail_from_name' => 'E-SPPB',
            ])
            ->set('test_email_recipient', 'test@example.com')
            ->call('sendTestEmail')
            ->assertHasNoFormErrors();
    }

    public function test_send_test_wa(): void
    {
        Http::fake([
            '*/api/sessions' => Http::response([
                [
                    'id' => 'sess-uuid-123',
                    'name' => 'sppb-bot',
                    'status' => 'ready',
                    'phone' => '6281234567890',
                ],
            ], 200),
            '*/api/sessions/*/messages/send-text' => Http::response(['messageId' => 'msg-123'], 201),
        ]);

        // Enable WA notifications
        AppSetting::set('notify_wa_enabled', true, 'notification', 'boolean');

        $this->actingAs($this->superAdmin);

        Livewire::test(NotificationSettings::class)
            ->set('test_wa_recipient', '081234567890')
            ->call('sendTestWa')
            ->assertHasNoFormErrors();
    }
}
