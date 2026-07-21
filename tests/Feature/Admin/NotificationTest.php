<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_notification_can_be_sent_to_user(): void
    {
        $user = User::factory()->create();

        Notification::make()
            ->title('Pengajuan SPPB Baru')
            ->body('SPPB #ENG-SPPB/2026/07/0001 membutuhkan persetujuan Anda.')
            ->icon('heroicon-o-document-text')
            ->success()
            ->sendToDatabase($user, isEventDispatched: true);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        $notification = $user->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertEquals('Pengajuan SPPB Baru', $notification->data['title']);
        $this->assertEquals('SPPB #ENG-SPPB/2026/07/0001 membutuhkan persetujuan Anda.', $notification->data['body']);
    }
}
