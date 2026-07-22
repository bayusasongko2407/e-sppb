<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message to a target phone number using OpenWA Gateway.
     */
    public function sendMessage(string $targetPhone, string $message): bool
    {
        $isEnabled = AppSetting::get('notify_wa_enabled', false);
        if (! $isEnabled) {
            Log::info('WhatsApp notification skipped because master switch is disabled.');

            return false;
        }

        $formattedPhone = $this->formatPhoneNumber($targetPhone);
        if (empty($formattedPhone)) {
            Log::warning('WhatsApp notification skipped because target phone is invalid: '.$targetPhone);

            return false;
        }

        $serverUrl = (string) AppSetting::get('wa_server_url', 'http://127.0.0.1:3000/send-message');
        $apiSecret = (string) AppSetting::get('wa_api_secret', '');
        $senderNumber = (string) AppSetting::get('wa_sender_number', '');

        if (empty($serverUrl)) {
            Log::warning('WhatsApp notification failed: OpenWA Server URL is empty.');

            return false;
        }

        if (! str_contains($serverUrl, '/send-message')) {
            $serverUrl = rtrim($serverUrl, '/').'/send-message';
        }

        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            if (! empty($apiSecret)) {
                $headers['X-Api-Secret'] = $apiSecret;
                $headers['Authorization'] = 'Bearer '.$apiSecret;
            }

            $payload = [
                'phone' => $formattedPhone,
                'to' => $formattedPhone.'@c.us',
                'message' => $message,
                'sender' => $senderNumber,
            ];

            $response = Http::withHeaders($headers)
                ->timeout(5)
                ->post($serverUrl, $payload);

            if ($response->successful()) {
                Log::info("WhatsApp message sent successfully to {$formattedPhone}");

                return true;
            }

            Log::warning("WhatsApp Gateway returned error {$response->status()}: {$response->body()}");

            return false;
        } catch (\Throwable $e) {
            Log::error('Failed to send WhatsApp message via OpenWA Gateway: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get OpenWA Gateway live connection status and QR code if available.
     *
     * @return array{connected: bool, status_label: string, qr_code: ?string, message: string}
     */
    public function getStatus(): array
    {
        $serverUrl = (string) AppSetting::get('wa_server_url', 'http://127.0.0.1:3000/send-message');
        $apiSecret = (string) AppSetting::get('wa_api_secret', '');

        if (empty($serverUrl)) {
            return [
                'connected' => false,
                'status_label' => 'DISCONNECTED',
                'qr_code' => null,
                'message' => 'URL Server OpenWA belum dikonfigurasi.',
            ];
        }

        // Parse base URL (e.g., http://127.0.0.1:3000)
        $parsed = parse_url($serverUrl);
        $baseUrl = isset($parsed['scheme'], $parsed['host'])
            ? $parsed['scheme'].'://'.$parsed['host'].(isset($parsed['port']) ? ':'.$parsed['port'] : '')
            : 'http://127.0.0.1:3000';

        try {
            $headers = ['Accept' => 'application/json'];
            if (! empty($apiSecret)) {
                $headers['X-Api-Secret'] = $apiSecret;
                $headers['Authorization'] = 'Bearer '.$apiSecret;
            }

            $response = Http::withHeaders($headers)
                ->timeout(3)
                ->get($baseUrl.'/status');

            if ($response->successful()) {
                $data = $response->json();
                $isConnected = (bool) ($data['connected'] ?? $data['authenticated'] ?? true);
                $qrCode = $data['qr'] ?? $data['qr_code'] ?? null;

                return [
                    'connected' => $isConnected,
                    'status_label' => $isConnected ? 'CONNECTED' : 'DISCONNECTED',
                    'qr_code' => is_string($qrCode) ? $qrCode : null,
                    'message' => $isConnected ? 'Terhubung dengan OpenWA Gateway.' : 'Perangkat belum terhubung (Perlu Scan QR).',
                ];
            }
        } catch (\Throwable $e) {
            // Gateway endpoint check failed
        }

        return [
            'connected' => false,
            'status_label' => 'DISCONNECTED',
            'qr_code' => null,
            'message' => 'Gagal terhubung ke OpenWA Gateway (Server offline atau URL tidak valid).',
        ];
    }

    /**
     * Send test notification to target phone.
     */
    public function sendTestMessage(string $targetPhone): bool
    {
        $message = "🔔 [E-SPPB Enterprise]\n\nIni adalah pesan uji coba integrasi WhatsApp OpenWA Gateway.\nKoneksi berhasil dan notifikasi WhatsApp berfungsi dengan baik!";

        return $this->sendMessage($targetPhone, $message);
    }

    /**
     * Format Indonesian phone number into standard international format without '+' sign.
     * e.g., 08123456789 -> 628123456789
     */
    public function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if (empty($cleaned)) {
            return '';
        }

        if (str_starts_with($cleaned, '0')) {
            return '62'.substr($cleaned, 1);
        }

        if (str_starts_with($cleaned, '62')) {
            return $cleaned;
        }

        return $cleaned;
    }
}
