<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DocumentVerificationService;
use Illuminate\Http\Request;

class DocumentVerificationController extends Controller
{
    public function __construct(
        private readonly DocumentVerificationService $service
    ) {}

    /**
     * Verify a public document QR code page using its SHA256 verification token.
     *
     * The token is a 64-character SHA256 hex string embedded in the QR code,
     * derived deterministically from the generation UUID and page number.
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
