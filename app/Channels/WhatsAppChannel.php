<?php

declare(strict_types=1);

namespace App\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function __construct(
        protected WhatsAppService $whatsAppService,
    ) {}

    /**
     * Send the given notification via WhatsApp.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        $targetPhone = method_exists($notifiable, 'routeNotificationForWhatsApp')
            ? $notifiable->routeNotificationForWhatsApp()
            : ($notifiable->phone ?? $notifiable->phone_number ?? null);

        if (! $targetPhone) {
            return;
        }

        $message = null;
        if (method_exists($notification, 'toWhatsApp')) {
            $message = $notification->toWhatsApp($notifiable);
        } elseif (method_exists($notification, 'toMail')) {
            $mail = $notification->toMail($notifiable);
            $lines = [];
            if (! empty($mail->subject)) {
                $lines[] = '*'.$mail->subject.'*';
            }
            if (! empty($mail->introLines)) {
                $lines = array_merge($lines, $mail->introLines);
            }
            $message = implode("\n", $lines);
        }

        if (empty($message)) {
            return;
        }

        $this->whatsAppService->sendMessage((string) $targetPhone, (string) $message);
    }
}
