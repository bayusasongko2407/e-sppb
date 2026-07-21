# Frozen Rules: Security & Authorization

## 1. ID Masking (URL Protection)
*   Semua model resource Filament wajib menggunakan trait `App\Traits\SecureRouteBinding` untuk mengenkripsi ID integer di URL.
*   Jika model menggunakan `HasUuids` (seperti `SppbHeader`), tambahkan resolusi tabrakan pada model:
    `SecureRouteBinding::resolveRouteBindingQuery insteadof HasUuids;`

## 2. Authorization & Policies
*   Setiap model resource dilindungi oleh Policy Laravel (`app/Policies/`).
*   Verifikasi permission dilakukan melalui Policy yang terintegrasi dengan Spatie Laravel Permission.
*   Dilarang keras meloloskan request input tanpa divalidasi menggunakan Form Request atau validation rules Filament.

## 3. Data Scoping
*   Semua query pencarian data wajib disaring berdasarkan lingkup Plant & Departemen pengguna yang sedang masuk (`auth()->user()`).
*   Super Admin dilewati dari pembatasan lingkup data (*bypass data scoping*).
