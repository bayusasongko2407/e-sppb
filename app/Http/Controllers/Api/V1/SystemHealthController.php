<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DocumentValidation;
use App\Services\DocumentVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SystemHealthController extends Controller
{
    /**
     * Diagnostic endpoint for system health, latency, and QR Code decoder operational status.
     */
    public function index(Request $request, DocumentVerificationService $verificationService): JsonResponse
    {
        $startTime = microtime(true);

        // Check DB connection
        $dbStatus = 'OK';
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbStatus = 'ERROR: '.$e->getMessage();
        }

        // Test QR decryption handler
        $testReleaseNumber = 'SJ-TEST-'.date('Ymd').'-0001';
        $encryptedPayload = Crypt::encryptString($testReleaseNumber);

        // Test decoding encrypted Base64 payload
        $decryptedResult = $verificationService->decryptQrPayload($encryptedPayload);
        $qrDecoderStatus = ($decryptedResult['decrypted'] === $testReleaseNumber) ? 'OPERATIONAL' : 'ERROR';

        // Latency calculation
        $latencyMs = round((microtime(true) - $startTime) * 1000, 2);

        $recentValidationsCount = DocumentValidation::where('created_at', '>=', now()->subHours(24))->count();

        return response()->json([
            'status' => 'ok',
            'success' => true,
            'service' => 'E-SPPB Backend Enterprise API',
            'version' => '1.0.1',
            'environment' => config('app.env'),
            'base_url' => 'https://e-sppb.engiboard.web.id/api/v1',
            'system_status' => [
                'database' => $dbStatus,
                'qr_decoder' => $qrDecoderStatus,
                'latency_ms' => $latencyMs,
            ],
            'qr_decoder_test' => [
                'sample_encrypted_payload' => substr($encryptedPayload, 0, 40).'...',
                'decrypted_test_result' => $decryptedResult['decrypted'],
                'test_passed' => ($decryptedResult['decrypted'] === $testReleaseNumber),
            ],
            'metrics' => [
                'recent_24h_validations' => $recentValidationsCount,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
