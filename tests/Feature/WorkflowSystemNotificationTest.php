<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowSystemNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_notification_is_saved_to_database_when_enabled(): void
    {
        AppSetting::set('notify_system_enabled', true, 'notification', 'boolean');
        AppSetting::set('notify_event_sppb_created', true, 'notification', 'boolean');

        $user = User::factory()->create();

        $service = app(WorkflowService::class);
        $service->sendNotification(
            user: $user,
            title: 'Test SPPB Created',
            body: 'Document SPPB-001 has been created.',
            url: '/admin/sppb-headers/1',
            eventType: 'sppb_created'
        );

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        $notification = $user->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('SPPB-001', json_encode($notification->data));
    }

    public function test_system_notification_is_skipped_when_master_switch_disabled(): void
    {
        AppSetting::set('notify_system_enabled', false, 'notification', 'boolean');
        AppSetting::set('notify_event_sppb_created', true, 'notification', 'boolean');

        $user = User::factory()->create();

        $service = app(WorkflowService::class);
        $service->sendNotification(
            user: $user,
            title: 'Test SPPB Created',
            body: 'Document SPPB-002 has been created.',
            url: '/admin/sppb-headers/2',
            eventType: 'sppb_created'
        );

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
        ]);
    }
}
