<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle API Login.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string', // can be email or NIK
            'password' => 'required|string',
        ]);

        try {
            // Attempt to login using AuthService
            $user = $this->authService->attemptLogin(
                $request->input('email'),
                $request->input('password')
            );

            // Clean up old tokens first to avoid accumulation
            $user->tokens()->delete();

            // Create access token (short expiration could be handled in Sanctum if configured)
            $accessToken = $user->createToken('access_token')->plainTextToken;

            // Create refresh token
            $refreshToken = $user->createToken('refresh_token')->plainTextToken;

            // Retrieve roles and permissions
            $permissions = $user->getAllPermissions()->pluck('name')->toArray();
            $roles = $user->getRoleNames()->toArray();

            // Retrieve primary active position
            $primaryUserPosition = $user->userPositions()
                ->where('is_active', true)
                ->where('is_primary', true)
                ->with('position')
                ->first();

            $position = $primaryUserPosition?->position ? [
                'id' => $primaryUserPosition->position->id,
                'code' => $primaryUserPosition->position->code,
                'name' => $primaryUserPosition->position->name,
            ] : null;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'data' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'user' => [
                        'id' => $user->id,
                        'nik' => $user->nik,
                        'name' => $user->name,
                        'email' => $user->email,
                        'plant_id' => $user->plant_id,
                        'department_id' => $user->department_id,
                        'roles' => $roles,
                        'permissions' => $permissions,
                        'position' => $position,
                        'plant' => $user->plant ? [
                            'id' => $user->plant->id,
                            'code' => $user->plant->code,
                            'name' => $user->plant->name,
                            'address' => $user->plant->address,
                        ] : null,
                        'department' => $user->department ? [
                            'id' => $user->department->id,
                            'code' => $user->department->code,
                            'name' => $user->department->name,
                        ] : null,
                    ],
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle API Refresh Token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $plainRefreshToken = $request->input('refresh_token');

        // Parse token and validate
        $tokenModel = PersonalAccessToken::findToken($plainRefreshToken);

        if (! $tokenModel || $tokenModel->name !== 'refresh_token') {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token tidak valid atau telah kedaluwarsa.',
            ], 401);
        }

        $user = $tokenModel->tokenable;

        if (! $user || ! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak aktif atau tidak ditemukan.',
            ], 401);
        }

        // Delete old access tokens
        $user->tokens()->where('name', 'access_token')->delete();

        // Create new access token
        $newAccessToken = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil diperbarui.',
            'data' => [
                'access_token' => $newAccessToken,
                'refresh_token' => $plainRefreshToken, // reuse same refresh token
            ],
        ]);
    }

    /**
     * Handle API Logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            // Delete all tokens for this user
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Get Authenticated User profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        // Retrieve primary active position
        $primaryUserPosition = $user->userPositions()
            ->where('is_active', true)
            ->where('is_primary', true)
            ->with('position')
            ->first();

        $position = $primaryUserPosition?->position ? [
            'id' => $primaryUserPosition->position->id,
            'code' => $primaryUserPosition->position->code,
            'name' => $primaryUserPosition->position->name,
        ] : null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'nik' => $user->nik,
                'name' => $user->name,
                'email' => $user->email,
                'plant_id' => $user->plant_id,
                'department_id' => $user->department_id,
                'roles' => $roles,
                'permissions' => $permissions,
                'position' => $position,
                'plant' => $user->plant ? [
                    'id' => $user->plant->id,
                    'code' => $user->plant->code,
                    'name' => $user->plant->name,
                    'address' => $user->plant->address,
                ] : null,
                'department' => $user->department ? [
                    'id' => $user->department->id,
                    'code' => $user->department->code,
                    'name' => $user->department->name,
                ] : null,
            ],
        ]);
    }

    /**
     * Handle API Session Login (Cookie / Web Session).
     */
    public function sessionLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        try {
            $user = $this->authService->attemptLogin(
                $request->input('email'),
                $request->input('password'),
                (bool) $request->input('remember', false)
            );

            Auth::guard('web')->login($user, (bool) $request->input('remember', false));

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $permissions = $user->getAllPermissions()->pluck('name')->toArray();
            $roles = $user->getRoleNames()->toArray();

            $primaryUserPosition = $user->userPositions()
                ->where('is_active', true)
                ->where('is_primary', true)
                ->with('position')
                ->first();

            $position = $primaryUserPosition?->position ? [
                'id' => $primaryUserPosition->position->id,
                'code' => $primaryUserPosition->position->code,
                'name' => $primaryUserPosition->position->name,
            ] : null;

            return response()->json([
                'success' => true,
                'message' => 'Session login berhasil.',
                'data' => [
                    'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                    'user' => [
                        'id' => $user->id,
                        'nik' => $user->nik,
                        'name' => $user->name,
                        'email' => $user->email,
                        'plant_id' => $user->plant_id,
                        'department_id' => $user->department_id,
                        'roles' => $roles,
                        'permissions' => $permissions,
                        'position' => $position,
                        'plant' => $user->plant ? [
                            'id' => $user->plant->id,
                            'code' => $user->plant->code,
                            'name' => $user->plant->name,
                            'address' => $user->plant->address,
                        ] : null,
                        'department' => $user->department ? [
                            'id' => $user->department->id,
                            'code' => $user->department->code,
                            'name' => $user->department->name,
                        ] : null,
                    ],
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle API Session Logout.
     */
    public function sessionLogout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Session logout berhasil.',
        ]);
    }

    /**
     * Get Authenticated Session User Profile.
     */
    public function sessionMe(Request $request): JsonResponse
    {
        $user = Auth::guard('web')->user() ?? $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        $primaryUserPosition = $user->userPositions()
            ->where('is_active', true)
            ->where('is_primary', true)
            ->with('position')
            ->first();

        $position = $primaryUserPosition?->position ? [
            'id' => $primaryUserPosition->position->id,
            'code' => $primaryUserPosition->position->code,
            'name' => $primaryUserPosition->position->name,
        ] : null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'nik' => $user->nik,
                'name' => $user->name,
                'email' => $user->email,
                'plant_id' => $user->plant_id,
                'department_id' => $user->department_id,
                'roles' => $roles,
                'permissions' => $permissions,
                'position' => $position,
                'plant' => $user->plant ? [
                    'id' => $user->plant->id,
                    'code' => $user->plant->code,
                    'name' => $user->plant->name,
                    'address' => $user->plant->address,
                ] : null,
                'department' => $user->department ? [
                    'id' => $user->department->id,
                    'code' => $user->department->code,
                    'name' => $user->department->name,
                ] : null,
            ],
        ]);
    }
}
