<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message to a target phone number using Meta WhatsApp Business API or Custom Gateway.
     *
     * @param  array<string, mixed>|null  $overrides
     */
    public function sendMessage(string $targetPhone, string $message, ?array $overrides = null): bool
    {
        $isEnabled = isset($overrides['notify_wa_enabled'])
            ? (bool) $overrides['notify_wa_enabled']
            : (bool) AppSetting::get('notify_wa_enabled', false);

        if (! $isEnabled) {
            Log::info('WhatsApp notification skipped because master switch is disabled.');

            return false;
        }

        $formattedPhone = $this->formatPhoneNumber($targetPhone);
        if (empty($formattedPhone)) {
            Log::warning('WhatsApp notification skipped because target phone is invalid: '.$targetPhone);

            return false;
        }

        $provider = $overrides['wa_provider'] ?? (string) AppSetting::get('wa_provider', 'meta_cloud');

        if ($provider === 'meta_cloud') {
            return $this->sendMetaCloudMessage($formattedPhone, $message, $overrides);
        }

        return $this->sendCustomGatewayMessage($formattedPhone, $message, $overrides);
    }

    /**
     * Send message using Official Meta WhatsApp Business Cloud API.
     *
     * @param  array<string, mixed>|null  $overrides
     */
    protected function sendMetaCloudMessage(string $formattedPhone, string $message, ?array $overrides = null): bool
    {
        $phoneNumberId = $overrides['wa_phone_number_id'] ?? (string) AppSetting::get('wa_phone_number_id', '');
        $accessToken = $overrides['wa_access_token'] ?? (string) AppSetting::get('wa_access_token', '');
        $apiVersion = $overrides['wa_api_version'] ?? (string) AppSetting::get('wa_api_version', 'v20.0');

        if (empty($phoneNumberId) || empty($accessToken)) {
            Log::warning('WhatsApp Business Cloud API skipped: Phone Number ID or Access Token is missing.');

            return false;
        }

        $url = "https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages";

        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $formattedPhone,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message,
                ],
            ];

            $response = Http::withToken($accessToken)
                ->timeout(8)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("WhatsApp Business Cloud API message sent successfully to {$formattedPhone}");

                return true;
            }

            Log::warning("WhatsApp Business Cloud API returned error {$response->status()}: {$response->body()}");

            return false;
        } catch (\Throwable $e) {
            Log::error('Failed to send WhatsApp message via Meta Cloud API: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Send message using Custom Node.js / Express REST Gateway (wwebjs).
     *
     * @param  array<string, mixed>|null  $overrides
     */
    protected function sendCustomGatewayMessage(string $formattedPhone, string $message, ?array $overrides = null): bool
    {
        $serverUrl = $overrides['wa_server_url'] ?? (string) AppSetting::get('wa_server_url', 'http://127.0.0.1:3000/send-message');
        $apiSecret = $overrides['wa_api_secret'] ?? (string) AppSetting::get('wa_api_secret', '');

        if (empty($serverUrl)) {
            Log::warning('WhatsApp notification failed: Custom Gateway Server URL is empty.');

            return false;
        }

        $endpoint = $this->resolveSendEndpoint((string) $serverUrl);

        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            if (! empty($apiSecret)) {
                $headers['X-API-Key'] = $apiSecret;
            }

            $payload = [
                'number' => $formattedPhone,
                'chatId' => $formattedPhone.'@c.us',
                'text' => $message,
            ];

            $response = Http::withHeaders($headers)
                ->timeout(5)
                ->post($endpoint, $payload);

            if ($response->successful() && ($response->json('success') ?? true)) {
                Log::info("WhatsApp message sent successfully via Custom Gateway to {$formattedPhone}");

                return true;
            }

            Log::warning("WhatsApp Custom Gateway returned error {$response->status()}: {$response->body()}");

            return false;
        } catch (\Throwable $e) {
            Log::error('Failed to send WhatsApp message via Custom Gateway: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get live connection status of WhatsApp Business API / Custom Gateway.
     *
     * @param  array<string, mixed>|null  $overrides
     * @return array{provider: string, connected: bool, status_label: string, qr_code: ?string, message: string}
     */
    public function getStatus(?array $overrides = null): array
    {
        $provider = $overrides['wa_provider'] ?? (string) AppSetting::get('wa_provider', 'meta_cloud');

        if ($provider === 'meta_cloud') {
            return $this->getMetaCloudStatus($overrides);
        }

        return $this->getCustomGatewayStatus($overrides);
    }

    /**
     * Get status for Meta WhatsApp Business Cloud API.
     *
     * @param  array<string, mixed>|null  $overrides
     * @return array{provider: string, connected: bool, status_label: string, qr_code: ?string, message: string}
     */
    protected function getMetaCloudStatus(?array $overrides = null): array
    {
        $phoneNumberId = $overrides['wa_phone_number_id'] ?? (string) AppSetting::get('wa_phone_number_id', '');
        $accessToken = $overrides['wa_access_token'] ?? (string) AppSetting::get('wa_access_token', '');
        $apiVersion = $overrides['wa_api_version'] ?? (string) AppSetting::get('wa_api_version', 'v20.0');

        if (empty($phoneNumberId) || empty($accessToken)) {
            return [
                'provider' => 'meta_cloud',
                'connected' => false,
                'status_label' => 'DISCONNECTED',
                'qr_code' => null,
                'message' => 'Phone Number ID atau Permanent Access Token Meta belum dikonfigurasi.',
            ];
        }

        try {
            $url = "https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}?fields=id,display_phone_number,verified_name,quality_rating";
            $response = Http::withToken($accessToken)
                ->timeout(5)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $displayName = $data['verified_name'] ?? $data['display_phone_number'] ?? 'WhatsApp Business Account';
                $displayPhone = $data['display_phone_number'] ?? '';
                $quality = $data['quality_rating'] ?? 'UNKNOWN';

                return [
                    'provider' => 'meta_cloud',
                    'connected' => true,
                    'status_label' => 'CONNECTED',
                    'qr_code' => null,
                    'message' => "Terhubung ke Meta WhatsApp Business API ({$displayName} - {$displayPhone}) | Quality Rating: {$quality}",
                ];
            }

            $errMsg = $response->json('error.message') ?? 'Access Token tidak valid atau Phone Number ID salah.';

            return [
                'provider' => 'meta_cloud',
                'connected' => false,
                'status_label' => 'DISCONNECTED',
                'qr_code' => null,
                'message' => 'Meta API Response Error: '.$errMsg,
            ];
        } catch (\Throwable $e) {
            return [
                'provider' => 'meta_cloud',
                'connected' => false,
                'status_label' => 'DISCONNECTED',
                'qr_code' => null,
                'message' => 'Gagal terhubung ke Meta Graph API: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get status for Custom Node.js / wwebjs Gateway.
     *
     * @param  array<string, mixed>|null  $overrides
     * @return array{provider: string, connected: bool, status_label: string, qr_code: ?string, message: string}
     */
    protected function getCustomGatewayStatus(?array $overrides = null): array
    {
        $serverUrl = $overrides['wa_server_url'] ?? (string) AppSetting::get('wa_server_url', 'http://127.0.0.1:3000/send-message');
        $apiSecret = $overrides['wa_api_secret'] ?? (string) AppSetting::get('wa_api_secret', '');

        if (empty($serverUrl)) {
            return [
                'provider' => 'wwebjs',
                'connected' => false,
                'status_label' => 'DISCONNECTED',
                'qr_code' => null,
                'message' => 'URL Server WhatsApp Gateway belum dikonfigurasi.',
            ];
        }

        $baseUrl = $this->resolveBaseUrl((string) $serverUrl);

        try {
            $headers = [
                'Accept' => 'application/json',
            ];
            if (! empty($apiSecret)) {
                $headers['X-API-Key'] = $apiSecret;
            }

            $response = Http::withHeaders($headers)
                ->timeout(3)
                ->get($baseUrl.'/status');

            if ($response->successful()) {
                $data = $response->json();
                $isConnected = (bool) ($data['connected'] ?? false);
                $statusStr = (string) ($data['status'] ?? ($isConnected ? 'READY' : 'DISCONNECTED'));
                $qrCode = $data['qr_code'] ?? $data['qrCode'] ?? null;
                $message = (string) ($data['message'] ?? ($isConnected ? 'Terhubung dengan WhatsApp Gateway.' : 'Perangkat belum terhubung.'));

                return [
                    'provider' => 'wwebjs',
                    'connected' => $isConnected,
                    'status_label' => $isConnected ? 'CONNECTED' : ($statusStr === 'QR_RECEIVED' ? 'PAIRING_REQUIRED' : 'DISCONNECTED'),
                    'qr_code' => $qrCode,
                    'message' => $message,
                ];
            }
        } catch (\Throwable $e) {
            // Gateway endpoint check failed
        }

        return [
            'provider' => 'wwebjs',
            'connected' => false,
            'status_label' => 'DISCONNECTED',
            'qr_code' => null,
            'message' => 'Gagal terhubung ke WhatsApp Gateway (Server offline atau URL tidak valid).',
        ];
    }

    /**
     * Send test notification to target phone.
     *
     * @param  array<string, mixed>|null  $overrides
     */
    public function sendTestMessage(string $targetPhone, ?array $overrides = null): bool
    {
        $provider = $overrides['wa_provider'] ?? (string) AppSetting::get('wa_provider', 'meta_cloud');
        $engineLabel = $provider === 'meta_cloud' ? 'Meta WhatsApp Business API' : 'Custom REST Gateway (wwebjs)';

        $message = "🔔 *[E-SPPB Enterprise]*\n\nIni adalah pesan uji coba integrasi *{$engineLabel}*.\nKoneksi berhasil dan notifikasi WhatsApp berfungsi dengan baik!";

        return $this->sendMessage($targetPhone, $message, $overrides);
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

    /**
     * Resolve base URL from server configuration URL.
     */
    protected function resolveBaseUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (isset($parsed['scheme'], $parsed['host'])) {
            return $parsed['scheme'].'://'.$parsed['host'].(isset($parsed['port']) ? ':'.$parsed['port'] : '');
        }

        return 'http://127.0.0.1:3000';
    }

    /**
     * Resolve full send-message endpoint.
     */
    protected function resolveSendEndpoint(string $url): string
    {
        if (str_contains($url, '/send-message')) {
            return $url;
        }

        return rtrim($this->resolveBaseUrl($url), '/').'/send-message';
    }
}
