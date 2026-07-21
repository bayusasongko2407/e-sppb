<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GoodsReleaseController;
use App\Http\Controllers\Api\V1\SppbController;
use App\Http\Controllers\Api\V1\WorkflowTaskController;
use App\Models\ApiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth Profile & Session Endpoints
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // SPPB Endpoints
    Route::prefix('sppb')->group(function () {
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

            // Status Logs
            Route::get('status-logs', [SppbController::class, 'statusLogs'])->middleware('permission:view_sppbstatuslog');

            // Goods Releases related to SPPB
            Route::post('goods-releases', [GoodsReleaseController::class, 'store'])->middleware('permission:create_goodsrelease');
        });
    });

    // Workflow Endpoints
    Route::prefix('workflow')->group(function () {
        Route::get('tasks', [WorkflowTaskController::class, 'index'])->middleware('permission:view_any_workflowstepapprover');

        Route::prefix('instances/{uuid}')->group(function () {
            Route::get('/', [WorkflowTaskController::class, 'showInstance'])->middleware('permission:view_workflowinstance');
        });

        Route::prefix('steps/{stepId}')->group(function () {
            Route::post('approve', [WorkflowTaskController::class, 'approve'])->middleware('permission:view_any_workflowstepapprover');
            Route::post('reject', [WorkflowTaskController::class, 'reject'])->middleware('permission:view_any_workflowstepapprover');
            Route::post('revision', [WorkflowTaskController::class, 'requestRevision'])->middleware('permission:view_any_workflowstepapprover');
        });

        Route::prefix('delegations')->group(function () {
            Route::get('/', [WorkflowTaskController::class, 'listDelegations'])->middleware('permission:view_any_workflowdelegation');
            Route::post('/', [WorkflowTaskController::class, 'createDelegation'])->middleware('permission:create_workflowdelegation');
            Route::put('{id}', [WorkflowTaskController::class, 'updateDelegation'])->middleware('permission:update_workflowdelegation');
            Route::delete('{id}', [WorkflowTaskController::class, 'cancelDelegation'])->middleware('permission:delete_workflowdelegation');
        });
    });

    // Goods Releases Endpoints
    Route::prefix('goods-releases')->group(function () {
        Route::get('/', [GoodsReleaseController::class, 'index'])->middleware('permission:view_any_goodsrelease');
        Route::get('{uuid}', [GoodsReleaseController::class, 'show'])->middleware('permission:view_goodsrelease');
    });

    // Document Endpoints
    Route::prefix('documents')->group(function () {
        // ... placeholders
    });

});

// Public endpoints
Route::prefix('v1/public')->group(function () {
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
