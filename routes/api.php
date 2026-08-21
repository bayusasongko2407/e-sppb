<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrandingSettingController;
use App\Http\Controllers\Api\V1\DashboardMetricsController;
use App\Http\Controllers\Api\V1\GoodsReleaseController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SppbController;
use App\Http\Controllers\Api\V1\SystemHealthController;
use App\Http\Controllers\Api\V1\WorkflowTaskController;
use App\Http\Controllers\DocumentVerificationController;
use App\Http\Middleware\CorsMiddleware;
use App\Models\ApiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Direct Top-Level Contract Aliases (/api/login, /api/me, /api/health, /api/branding)
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::get('health', [SystemHealthController::class, 'index']);
Route::get('branding', [BrandingSettingController::class, 'show']);

// Public API Auth routes (Token & Session)
Route::prefix('v1/auth')->group(function () {
    Route::get('login', fn () => response()->json([
        'success' => false,
        'message' => 'Method GET tidak didukung untuk login. Silakan gunakan method POST dengan payload email/nik dan password.',
    ], 405));
    Route::post('login', [AuthController::class, 'login']);

    Route::get('refresh', fn () => response()->json([
        'success' => false,
        'message' => 'Method GET tidak didukung untuk refresh token. Silakan gunakan method POST dengan payload refresh_token.',
    ], 405));
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::get('logout', fn () => response()->json([
        'success' => false,
        'message' => 'Method GET tidak didukung untuk logout. Silakan gunakan method POST dengan Bearer token.',
    ], 405));

    // Session-based Auth routes (Cookie/Web Session)
    Route::prefix('session')->middleware('web')->group(function () {
        Route::get('login', fn () => response()->json([
            'success' => false,
            'message' => 'Method GET tidak didukung untuk session login. Silakan gunakan method POST dengan payload email/nik dan password.',
        ], 405));
        Route::post('login', [AuthController::class, 'sessionLogin']);

        Route::get('logout', fn () => response()->json([
            'success' => false,
            'message' => 'Method GET tidak didukung untuk session logout. Silakan gunakan method POST.',
        ], 405));
        Route::post('logout', [AuthController::class, 'sessionLogout']);

        Route::get('me', [AuthController::class, 'sessionMe']);
    });
});

// Public Branding Endpoint
Route::get('v1/branding', [BrandingSettingController::class, 'show']);

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth Profile & Session Endpoints
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Dashboard & Metrics Endpoints
    Route::get('dashboard/metrics', [DashboardMetricsController::class, 'metrics']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // SPPB Endpoints (Supports both /sppb and /sppb-headers)
    $registerSppbRoutes = function () {
        Route::get('stats', [SppbController::class, 'stats'])->middleware('permission:view_any_sppbheader');
        Route::get('master/plants', [SppbController::class, 'plants']);
        Route::get('master/departments', [SppbController::class, 'departments']);
        Route::get('master/locations', [SppbController::class, 'locations']);
        Route::get('master/items', [SppbController::class, 'items']);
        Route::get('/', [SppbController::class, 'index'])->middleware('permission:view_any_sppbheader');
        Route::post('/', [SppbController::class, 'store'])->middleware('permission:create_sppbheader');

        Route::prefix('{uuid}')->group(function () {
            Route::get('/', [SppbController::class, 'show'])->middleware('permission:view_sppbheader');
            Route::put('/', [SppbController::class, 'update'])->middleware('permission:update_sppbheader');
            Route::delete('/', [SppbController::class, 'destroy'])->middleware('permission:delete_sppbheader');

            // Details
            Route::get('details', [SppbController::class, 'listDetails'])->middleware('permission:view_sppbdetail');
            Route::post('details', [SppbController::class, 'addDetail'])->middleware('permission:create_sppbdetail');
            Route::put('details/{detailId}', [SppbController::class, 'updateDetail'])->middleware('permission:update_sppbdetail');
            Route::delete('details/{detailId}', [SppbController::class, 'removeDetail'])->middleware('permission:delete_sppbdetail');

            // Attachments
            Route::get('attachments', [SppbController::class, 'listAttachments'])->middleware('permission:view_attachment');
            Route::post('attachments', [SppbController::class, 'uploadAttachment'])->middleware('permission:create_attachment');
            Route::delete('attachments/{attachmentUuid}', [SppbController::class, 'deleteAttachment'])->middleware('permission:delete_attachment');

            // Actions
            Route::post('submit', [SppbController::class, 'submit'])->middleware('permission:update_sppbheader');
            Route::post('resubmit', [SppbController::class, 'resubmit'])->middleware('permission:update_sppbheader');
            Route::post('cancel', [SppbController::class, 'cancel'])->middleware('permission:update_sppbheader');
            Route::post('approve', [SppbController::class, 'approve'])->middleware('permission:update_sppbheader');
            Route::post('reject', [SppbController::class, 'reject'])->middleware('permission:update_sppbheader');

            // Status Logs
            Route::get('status-logs', [SppbController::class, 'statusLogs'])->middleware('permission:view_sppbstatuslog');

            // Goods Releases & Releasable Items related to SPPB
            Route::get('releasable-items', [SppbController::class, 'releasableItems'])->middleware('permission:view_sppbheader');
            Route::post('goods-releases', [GoodsReleaseController::class, 'store'])->middleware('permission:create_goodsrelease');

            // QR Code Generator
            Route::get('qr-code', [SppbController::class, 'qrCode'])->middleware('permission:view_sppbheader');
        });
    };

    Route::prefix('sppb')->group($registerSppbRoutes);
    Route::prefix('sppb-headers')->group($registerSppbRoutes);

    // Workflow Endpoints
    Route::prefix('workflow')->group(function () {
        Route::get('tasks', [WorkflowTaskController::class, 'index']);

        Route::prefix('instances/{uuid}')->group(function () {
            Route::get('/', [WorkflowTaskController::class, 'showInstance']);
        });

        Route::prefix('steps/{stepId}')->group(function () {
            Route::post('approve', [WorkflowTaskController::class, 'approve']);
            Route::post('reject', [WorkflowTaskController::class, 'reject']);
            Route::post('revision', [WorkflowTaskController::class, 'requestRevision']);
        });

        Route::prefix('delegations')->group(function () {
            Route::get('/', [WorkflowTaskController::class, 'listDelegations']);
            Route::post('/', [WorkflowTaskController::class, 'createDelegation']);
            Route::put('{id}', [WorkflowTaskController::class, 'updateDelegation']);
            Route::delete('{id}', [WorkflowTaskController::class, 'cancelDelegation']);
        });
    });

    // Goods Releases Endpoints
    // Goods Releases Endpoints
    Route::prefix('goods-releases')->group(function () {
        Route::get('/', [GoodsReleaseController::class, 'index'])->middleware('permission:view_any_goodsrelease');
        Route::post('/', [GoodsReleaseController::class, 'storeCompatibility'])->middleware('permission:create_goodsrelease');
        Route::get('{uuid}', [GoodsReleaseController::class, 'show']);
        Route::post('{uuid}/receive', [GoodsReleaseController::class, 'receive']);
        Route::post('{uuid}/confirm-receipt', [GoodsReleaseController::class, 'receive']);
        Route::patch('{uuid}/status', [GoodsReleaseController::class, 'receive']);
    });

    // Branding & Logo Settings (Admin)
    Route::prefix('settings/branding')->group(function () {
        Route::get('/', [BrandingSettingController::class, 'show']);
        Route::post('/', [BrandingSettingController::class, 'update']);
        Route::delete('logos/{type}', [BrandingSettingController::class, 'deleteLogo']);
    });
});

// Public endpoints
Route::prefix('v1/public')->group(function () {
    Route::get('branding', [BrandingSettingController::class, 'show']);
    Route::get('sandbox-info', function () {
        $setting = ApiSetting::first();

        return response()->json([
            'success' => true,
            'message' => 'Status konfigurasi API Sandbox.',
            'data' => [
                'environment' => $setting?->environment ?? 'sandbox',
                'is_sandbox' => (bool) ($setting?->is_sandbox ?? true),
                'mock_approval' => (bool) ($setting?->is_mock_approval_enabled ?? false),
                'api_rate_limit' => (int) ($setting?->api_rate_limit ?? 60),
                'api_documentation_url' => url('/docs/api'),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    });
});

// Public Document & Goods Release Verification & Receive Endpoints (QR Decoder & Receipt Confirmation)
Route::prefix('v1/verify')->middleware(['throttle:60,1', CorsMiddleware::class])->group(function () {
    Route::options('document/{hash?}', fn () => response('', 200));
    Route::post('document', [DocumentVerificationController::class, 'verifyDocument']);
    Route::get('document/{hash?}', [DocumentVerificationController::class, 'verifyDocument']);
});

Route::prefix('v1/goods-releases')->middleware(['throttle:60,1', CorsMiddleware::class])->group(function () {
    Route::options('{uuid}/receive', fn () => response('', 200));
    Route::options('{uuid}/confirm-receipt', fn () => response('', 200));
    Route::options('{uuid}/status', fn () => response('', 200));
    Route::post('{uuid}/receive', [GoodsReleaseController::class, 'receive']);
    Route::post('{uuid}/confirm-receipt', [GoodsReleaseController::class, 'receive']);
    Route::patch('{uuid}/status', [GoodsReleaseController::class, 'receive']);
});

Route::prefix('v1/public/sppb')->middleware(['throttle:60,1', CorsMiddleware::class])->group(function () {
    Route::options('verify/{code}', fn () => response('', 200));
    Route::get('verify/{code}', [DocumentVerificationController::class, 'verifyDocument']);
});

Route::prefix('v1/sppb')->middleware(['throttle:60,1', CorsMiddleware::class])->group(function () {
    Route::options('verify-barcode', fn () => response('', 200));
    Route::post('verify-barcode', [DocumentVerificationController::class, 'verifyBarcode']);
    Route::get('verify', [DocumentVerificationController::class, 'verifyDocument']);
});

Route::prefix('v1')->middleware(['throttle:60,1', CorsMiddleware::class])->group(function () {
    Route::options('verify-barcode', fn () => response('', 200));
    Route::post('verify-barcode', [DocumentVerificationController::class, 'verifyBarcode']);
});

// System Health & Real-time Diagnostic Endpoints
Route::prefix('v1/health')->middleware([CorsMiddleware::class])->group(function () {
    Route::options('/', fn () => response('', 200));
    Route::get('/', [SystemHealthController::class, 'index']);
});
