<?php

namespace App\Filament\Pages;

use App\Models\DocumentValidation;
use App\Services\DocumentVerificationService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SystemHealthView extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    protected string $view = 'filament.pages.system-health-view';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cpu-chip';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'Pengaturan';
    }

    public static function getNavigationLabel(): string
    {
        return 'Diagnostik & Kesehatan Sistem';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Monitoring Kesehatan Sistem & QR Code';
    }

    public string $apiUrl = 'https://e-sppb.engiboard.web.id/api/v1';

    public ?float $apiLatencyMs = null;

    public string $dbStatus = 'Belum Diuji';

    public string $qrDecoderStatus = 'Belum Diuji';

    public string $qrTestInput = '';

    public ?array $qrTestResult = null;

    public string $qrEncryptInput = '';

    public ?string $qrEncryptResult = null;

    public array $healthData = [];

    public function mount(): void
    {
        $this->runDiagnostics();
    }

    public function runDiagnostics(): void
    {
        $startTime = microtime(true);

        // 1. Database Check
        try {
            DB::connection()->getPdo();
            $this->dbStatus = 'NORMAL (Terhubung)';
        } catch (\Throwable $e) {
            $this->dbStatus = 'ERROR: '.$e->getMessage();
        }

        // 2. QR Decoder Test
        $service = app(DocumentVerificationService::class);
        $testString = 'SJ-TEST-DIAGNOSTIC-001';
        $encrypted = Crypt::encryptString($testString);
        $decoded = $service->decryptQrPayload($encrypted);

        if (($decoded['decrypted'] ?? '') === $testString) {
            $this->qrDecoderStatus = 'OPERATIONAL (Aktif & Valid)';
        } else {
            $this->qrDecoderStatus = 'GAGAL (Terjadi Kerusakan Decrypt)';
        }

        // Latency
        $this->apiLatencyMs = round((microtime(true) - $startTime) * 1000, 2);

        $this->healthData = [
            'base_url' => $this->apiUrl,
            'environment' => config('app.env'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'db_status' => $this->dbStatus,
            'qr_decoder_status' => $this->qrDecoderStatus,
            'latency_ms' => $this->apiLatencyMs,
            'timestamp' => now()->translatedFormat('d F Y H:i:s T'),
        ];
    }

    public function testDecodeQr(): void
    {
        if (empty(trim($this->qrTestInput))) {
            Notification::make()->title('Input QR kosong')->warning()->send();

            return;
        }

        $service = app(DocumentVerificationService::class);
        $startTime = microtime(true);

        $result = $service->decryptQrPayload($this->qrTestInput);
        $docResult = $service->verifyDocument($this->qrTestInput);

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        $this->qrTestResult = [
            'raw_input' => $this->qrTestInput,
            'decoded_method' => $result['decode_method'] ?? 'UNKNOWN',
            'is_encrypted' => $result['is_encrypted'] ?? false,
            'decrypted_target' => $result['decrypted'] ?? '—',
            'verification_status' => $docResult['status'] ?? 'UNKNOWN',
            'verification_data' => $docResult['data'] ?? null,
            'execution_time_ms' => $executionTime,
        ];

        Notification::make()->title('Pengujian QR Berhasil')->success()->send();
    }

    public function testEncryptString(): void
    {
        if (empty(trim($this->qrEncryptInput))) {
            Notification::make()->title('Input teks kosong')->warning()->send();

            return;
        }

        $this->qrEncryptResult = Crypt::encryptString(trim($this->qrEncryptInput));
        Notification::make()->title('String Berhasil Di-encrypt')->success()->send();
    }

    public function getRecentValidationsProperty()
    {
        return DocumentValidation::with(['documentGeneration', 'documentPage', 'actor'])
            ->latest('verified_at')
            ->limit(15)
            ->get();
    }
}
