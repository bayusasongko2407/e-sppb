<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DocumentVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentVerificationController extends Controller
{
    public function __construct(
        private readonly DocumentVerificationService $service
    ) {}

    /**
     * Unified document & Goods Release (Surat Jalan) verification API handler.
     * Accepts encrypted QR payloads (Base64, JSON {iv, value, mac}), hash tokens, or document numbers.
     */
    public function verifyDocument(Request $request, ?string $hash = null): JsonResponse
    {
        $payload = $hash
            ?? $request->input('qr_data')
            ?? $request->input('encrypted_data')
            ?? $request->input('hash')
            ?? $request->input('token')
            ?? $request->all();

        $result = $this->service->verifyDocument(
            $payload,
            $request->ip(),
            $request->userAgent(),
            $request->user()
        );

        $statusCode = match ($result['status']) {
            'VALID' => 200,
            'NOT_FOUND' => 404,
            'CANCELLED', 'SUPERSEDED', 'REVOKED', 'EXPIRED' => 422,
            default => 400,
        };

        return response()->json($result, $statusCode);
    }

    /**
     * Verify a public document QR code page using its SHA256 verification token.
     */
    public function verifyPublicPage(Request $request, string $sha256Token)
    {
        $result = $this->service->verifyBySha256Token(
            $sha256Token,
            $request->ip(),
            $request->userAgent()
        );

        if ($request->wantsJson()) {
            $statusCode = match ($result['status']) {
                'VALID' => 200,
                'NOT_FOUND' => 404,
                default => 403, // SUPERSEDED, REVOKED, EXPIRED
            };

            return response()->json($result, $statusCode);
        }

        return view('document.verify', [
            'status' => $result['status'],
            'validation_id' => $result['validation_id'],
            'data' => $result['data'],
            'sha256_token' => $sha256Token,
        ]);
    }
}
