# Quality Gates Checklist

Sebelum Anda menyatakan suatu tugas selesai, Anda **wajib** mematuhi checklist validasi berikut dan melampirkan hasilnya dalam laporan akhir:

## 1. Coding & Style Standards
- [ ] File PHP baru menyertakan deklarasi `declare(strict_types=1);`.
- [ ] Format kode diselaraskan menggunakan Laravel Pint: `vendor/bin/pint --dirty --format agent`.
- [ ] Penamaan variabel, method, dan kelas menggunakan Bahasa Inggris deskriptif dan konsisten dengan codebase yang ada.

## 2. Static Analysis & Testing
- [ ] Static analysis lulus bersih tanpa error menggunakan PHPStan/Larastan: `vendor/bin/phpstan analyse`.
- [ ] Unit & Feature tests yang terkait lulus 100% tanpa ada kegagalan: `php artisan test --compact`.

## 3. Keamanan & Kebijakan Akses
- [ ] URL model menggunakan penyamaran ID terenkripsi (`SecureRouteBinding` aktif).
- [ ] Hak akses halaman dan aksi dilindungi oleh Policy Laravel.
- [ ] Data query tersaring berdasarkan Plant & Departemen pengguna (kecuali super_admin).

## 4. Performa
- [ ] Query database menggunakan eager loading (`with`) untuk memuat data relasi.
- [ ] Tidak ada kueri N+1 yang terdeteksi di logs atau pengujian.
