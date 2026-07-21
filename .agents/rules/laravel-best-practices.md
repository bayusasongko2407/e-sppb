# Frozen Rules: Laravel 12 & PHP 8.3 Best Practices

## 1. PHP 8.3 Standard
*   **Constructor Property Promotion:** Gunakan property promotion untuk inject dependency:
    ```php
    public function __construct(
        protected SppbService $sppbService,
    ) {}
    ```
*   **Type Hinting:** Semua parameter method dan return type wajib dideklarasikan secara eksplisit:
    ```php
    public function submitSppb(int $sppbId): bool
    ```
*   **Strict Types:** Selalu deklarasikan `declare(strict_types=1);` di baris pertama file PHP baru.

## 2. Laravel 12 Architecture
*   **Service Layer:** Simpan logika bisnis di kelas Service di folder `app/Services/`. Jangan menaruh query DB atau logika bisnis di Controller atau Filament Page.
*   **Implicit Binding Safety:** Semua model menggunakan trait `SecureRouteBinding` untuk menyamarkan primary key ID di URL.
*   **Database Migrations:** Gunakan class anonymous untuk file migrasi baru. Jangan ubah skema yang sudah dideploy tanpa migrasi tambahan.
*   **Eager Loading:** Selalu gunakan `with()` saat query database yang memuat relasi untuk menghindari N+1 query.
