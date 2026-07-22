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

        // Parse base URL (e.g. http://127.0.0.1:2785)
        $parsed = parse_url($serverUrl);
        $baseUrl = isset($parsed['scheme'], $parsed['host'])
            ? $parsed['scheme'].'://'.$parsed['host'].(isset($parsed['port']) ? ':'.$parsed['port'] : '')
            : 'http://127.0.0.1:3000';

        // Resolve dynamic session ID UUID
        $sessionId = $this->resolveSessionId($baseUrl, $apiSecret, $senderNumber);
        if (empty($sessionId)) {
            Log::warning('WhatsApp notification failed: Gagal menemukan session ID aktif di OpenWA Gateway.');

            return false;
        }

        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            if (! empty($apiSecret)) {
                $headers['X-API-Key'] = $apiSecret;
            }

            $payload = [
                'chatId' => $formattedPhone.'@c.us',
                'text' => $message,
            ];

            $sendUrl = $baseUrl.'/api/sessions/'.$sessionId.'/messages/send-text';

            $response = Http::withHeaders($headers)
                ->timeout(5)
                ->post($sendUrl, $payload);

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
     * Resolve the active session UUID from OpenWA Gateway.
     */
    protected function resolveSessionId(string $baseUrl, string $apiSecret, ?string $senderNumber = null): ?string
    {
        try {
            $headers = [
                'Accept' => 'application/json',
            ];
            if (! empty($apiSecret)) {
                $headers['X-API-Key'] = $apiSecret;
            }

            $response = Http::withHeaders($headers)
                ->timeout(3)
                ->get($baseUrl.'/api/sessions');

            if ($response->successful()) {
                $sessions = $response->json();
                if (is_array($sessions) && count($sessions) > 0) {
                    // Try to match by phone or name if senderNumber is provided
                    if (! empty($senderNumber)) {
                        $cleanSender = preg_replace('/[^0-9]/', '', $senderNumber);
                        foreach ($sessions as $session) {
                            $sessionPhone = isset($session['phone']) ? preg_replace('/[^0-9]/', '', (string) $session['phone']) : '';
                            if (($sessionPhone && $sessionPhone === $cleanSender) || ($session['name'] ?? '') === $senderNumber) {
                                return $session['id'];
                            }
                        }
                    }

                    // Fallback to first session where status is ready
                    foreach ($sessions as $session) {
                        if (($session['status'] ?? '') === 'ready') {
                            return $session['id'];
                        }
                    }

                    // Dynamic default fallback
                    return $sessions[0]['id'];
                }
            }
        } catch (\Throwable $e) {
            Log::error('Gagal resolve session ID dari OpenWA: '.$e->getMessage());
        }

        return null;
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
        $senderNumber = (string) AppSetting::get('wa_sender_number', '');

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
            $headers = [
                'Accept' => 'application/json',
            ];
            if (! empty($apiSecret)) {
                $headers['X-API-Key'] = $apiSecret;
            }

            $response = Http::withHeaders($headers)
                ->timeout(3)
                ->get($baseUrl.'/api/sessions');

            if ($response->successful()) {
                $sessions = $response->json();
                if (is_array($sessions) && count($sessions) > 0) {
                    // Try to find the session corresponding to senderNumber
                    $activeSession = null;
                    if (! empty($senderNumber)) {
                        $cleanSender = preg_replace('/[^0-9]/', '', $senderNumber);
                        foreach ($sessions as $session) {
                            $sessionPhone = isset($session['phone']) ? preg_replace('/[^0-9]/', '', (string) $session['phone']) : '';
                            if (($sessionPhone && $sessionPhone === $cleanSender) || ($session['name'] ?? '') === $senderNumber) {
                                $activeSession = $session;
                                break;
                            }
                        }
                    }

                    // Fallback to first session
                    if (! $activeSession) {
                        $activeSession = $sessions[0];
                    }

                    $status = $activeSession['status'] ?? 'unknown';
                    $isConnected = ($status === 'ready');
                    $sessionId = $activeSession['id'];

                    $qrCode = null;
                    if ($status === 'qr_ready') {
                        // Fetch QR Code
                        $qrResponse = Http::withHeaders($headers)
                            ->timeout(3)
                            ->get($baseUrl.'/api/sessions/'.$sessionId.'/qr');
                        if ($qrResponse->successful()) {
                            $qrData = $qrResponse->json();
                            $qrCode = $qrData['qrCode'] ?? null;
                        }
                    }

                    $statusMsg = match ($status) {
                        'ready' => 'Terhubung dengan OpenWA Gateway.',
                        'qr_ready' => 'Perangkat belum terhubung (Perlu Scan QR).',
                        'authenticating' => 'Sedang melakukan autentikasi...',
                        'initializing' => 'Sedang menginisialisasi sesi...',
                        'disconnected' => 'Sesi terputus.',
                        'failed' => 'Inisialisasi sesi gagal: '.($activeSession['lastError'] ?? 'Unknown error'),
                        default => 'Status sesi: '.ucfirst($status),
                    };

                    return [
                        'connected' => $isConnected,
                        'status_label' => $isConnected ? 'CONNECTED' : 'DISCONNECTED',
                        'qr_code' => $qrCode,
                        'message' => $statusMsg,
                    ];
                }

                return [
                    'connected' => false,
                    'status_label' => 'DISCONNECTED',
                    'qr_code' => null,
                    'message' => 'Tidak ada sesi terdaftar di OpenWA Gateway.',
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
