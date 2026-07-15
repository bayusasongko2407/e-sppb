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
    ) {
    }

    /**
     * Verify a public document QR code page.
     */
    public function verifyPublicPage(Request $request, string $verificationUuid, int $page): JsonResponse
    {
        $result = $this->service->verifyPublicPage(
            $verificationUuid,
            $page,
            $request->ip(),
            $request->userAgent()
        );

        $statusCode = match ($result['status']) {
            'VALID' => 200,
            'NOT_FOUND' => 404,
            default => 403, // SUPERSEDED, REVOKED, EXPIRED
        };

        return response()->json($result, $statusCode);
    }
}
