<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandingSettingController extends Controller
{
    /**
     * Get application branding, logos, and favicon settings.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Pengaturan logo dan branding aplikasi berhasil diambil.',
            'data' => $this->getBrandingData(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Update application branding, logos, and favicon settings.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $this->canManageBranding($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengubah pengaturan logo dan branding.',
            ], 403);
        }

        $request->validate([
            'app_custom_name' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:200',
            'app_primary_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'logo_height' => 'nullable|integer|min:16|max:200',
            'logo_login_height' => 'nullable|integer|min:20|max:300',
            'logo_pdf_position' => 'nullable|string|in:left,center,right',
            'logo_pdf_height' => 'nullable|integer|min:10|max:150',
            'logo_pdf_show_address' => 'nullable|boolean',
            'logo_light' => 'nullable|file|mimes:png,jpg,jpeg,webp,svg|max:5120',
            'logo_dark' => 'nullable|file|mimes:png,jpg,jpeg,webp,svg|max:5120',
            'logo_favicon' => 'nullable|file|mimes:ico,png,svg|max:2048',
            'logo_login' => 'nullable|file|mimes:png,jpg,jpeg,webp,svg|max:5120',
            'logo_pdf' => 'nullable|file|mimes:png,jpg,jpeg,webp,svg|max:5120',
        ]);

        if ($request->has('app_custom_name')) {
            AppSetting::set('app_custom_name', $request->input('app_custom_name'), 'visual', 'string');
        }

        if ($request->has('company_name')) {
            AppSetting::set('company_name', $request->input('company_name'), 'general', 'string');
        }

        if ($request->has('app_primary_color')) {
            AppSetting::set('app_primary_color', $request->input('app_primary_color'), 'visual', 'string');
        }

        if ($request->has('logo_height')) {
            AppSetting::set('logo_height', (int) $request->input('logo_height'), 'visual', 'integer');
        }

        if ($request->has('logo_login_height')) {
            AppSetting::set('logo_login_height', (int) $request->input('logo_login_height'), 'visual', 'integer');
        }

        if ($request->has('logo_pdf_position')) {
            AppSetting::set('logo_pdf_position', $request->input('logo_pdf_position'), 'visual', 'string');
        }

        if ($request->has('logo_pdf_height')) {
            AppSetting::set('logo_pdf_height', (int) $request->input('logo_pdf_height'), 'visual', 'integer');
        }

        if ($request->has('logo_pdf_show_address')) {
            AppSetting::set('logo_pdf_show_address', $request->boolean('logo_pdf_show_address'), 'visual', 'boolean');
        }

        $logoKeys = [
            'logo_light',
            'logo_dark',
            'logo_favicon',
            'logo_login',
            'logo_pdf',
        ];

        foreach ($logoKeys as $key) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $oldPath = AppSetting::get($key);

                if ($oldPath && is_string($oldPath) && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }

                $path = $file->store('logos', 'public');
                AppSetting::set($key, $path, 'visual', 'string');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan logo dan branding aplikasi berhasil diperbarui.',
            'data' => $this->getBrandingData(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Delete a specific logo or favicon asset.
     */
    public function deleteLogo(Request $request, string $type): JsonResponse
    {
        $user = $request->user();
        if (! $this->canManageBranding($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menghapus logo aplikasi.',
            ], 403);
        }

        $allowedTypes = ['light', 'dark', 'favicon', 'login', 'pdf'];
        if (! in_array($type, $allowedTypes, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Tipe logo tidak valid. Pilihan yang tersedia: '.implode(', ', $allowedTypes).'.',
            ], 422);
        }

        $key = 'logo_'.$type;
        $oldPath = AppSetting::get($key);

        if ($oldPath && is_string($oldPath) && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        AppSetting::set($key, null, 'visual', 'string');

        return response()->json([
            'success' => true,
            'message' => "Logo {$type} berhasil dihapus.",
            'data' => $this->getBrandingData(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Compile branding settings and public asset URLs.
     *
     * @return array<string, mixed>
     */
    private function getBrandingData(): array
    {
        $logoLight = AppSetting::get('logo_light');
        $logoDark = AppSetting::get('logo_dark');
        $logoFavicon = AppSetting::get('logo_favicon');
        $logoLogin = AppSetting::get('logo_login');
        $logoPdf = AppSetting::get('logo_pdf');

        return [
            'app_custom_name' => AppSetting::get('app_custom_name', 'E-SPPB Enterprise'),
            'company_name' => AppSetting::get('company_name', 'PT SANTOS JAYA ABADI'),
            'app_primary_color' => AppSetting::get('app_primary_color', '#2563EB'),
            'logos' => [
                'light' => [
                    'path' => $logoLight,
                    'url' => $this->resolvePublicUrl($logoLight),
                ],
                'dark' => [
                    'path' => $logoDark,
                    'url' => $this->resolvePublicUrl($logoDark),
                ],
                'favicon' => [
                    'path' => $logoFavicon,
                    'url' => $this->resolvePublicUrl($logoFavicon),
                ],
                'login' => [
                    'path' => $logoLogin,
                    'url' => $this->resolvePublicUrl($logoLogin ?? $logoLight),
                ],
                'pdf' => [
                    'path' => $logoPdf,
                    'url' => $this->resolvePublicUrl($logoPdf ?? $logoLight),
                ],
            ],
            'logo_height' => (int) AppSetting::get('logo_height', 36),
            'logo_login_height' => (int) AppSetting::get('logo_login_height', 60),
            'logo_pdf_position' => AppSetting::get('logo_pdf_position', 'left'),
            'logo_pdf_height' => (int) AppSetting::get('logo_pdf_height', 40),
            'logo_pdf_show_address' => (bool) AppSetting::get('logo_pdf_show_address', true),
        ];
    }

    /**
     * Check if user has permission to manage branding.
     */
    private function canManageBranding(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        try {
            return $user->hasPermissionTo('update_appsetting') || $user->hasPermissionTo('manage_settings');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Resolve absolute public asset URL from disk path.
     */
    private function resolvePublicUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
