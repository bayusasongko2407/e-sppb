---
name: antigravity-e-sppb
description: Menjalankan analisis, desain, implementasi, review, dan validasi proyek E-SPPB Enterprise berdasarkan Antigravity 2.0 Master Blueprint yang dibekukan. Gunakan ketika mengerjakan fase E-SPPB, Laravel 12, Filament v5, workflow approval, SPPB, pelepasan barang, database Blueprint, role/permission, UI Bahasa Indonesia, atau ketika perlu mencegah penggunaan pola Filament v3/v4 dan scope Company.
---

# Antigravity E-SPPB

Kerjakan E-SPPB secara bertahap sesuai blueprint beku. Utamakan konsistensi bisnis, integritas transaksi, dan bukti verifikasi.

## Muat konteks wajib

1. Temukan root repositori E-SPPB. Lokasi standar adalah `/www/wwwroot/e-sppb-enterprise`.
2. Baca `Agents.md` seluruhnya.
3. Baca `.codex/00_INDEX.md` dan `memory/continuation-log.md`.
4. Baca `technical-work/e-sppb-enterprise/antigravity-blueprint.md` untuk fase yang dikerjakan.
5. Baca `technical-work/e-sppb-enterprise/draft.yaml` jika tugas menyentuh model, relasi, migration, constraint, index, atau generator.
6. Baca dokumen `.codex` dan `/docs` yang dirujuk oleh index hanya sesuai kebutuhan tugas.
7. Baca [aturan beku](references/frozen-rules.md) sebelum mengubah desain, database, workflow, otorisasi, atau Filament.

Jangan memulai implementasi sebelum konteks minimum tersebut selesai dibaca.

## Terapkan aturan beku

- Perlakukan Plant sebagai tingkat organisasi tertinggi.
- Jangan membuat atau memulihkan `Company`, `companies`, `company_id`, `CompanyResource`, scope company, atau multi-company.
- Gunakan Laravel 12, PHP 8.3, MariaDB 10.11+, dan Filament major v5.
- Gunakan Bahasa Indonesia untuk semua teks yang terlihat pengguna. Pertahankan identifier PHP dan database dalam bahasa Inggris.
- Gunakan Spatie Laravel Permission untuk role dan permission. Jangan menambahkan `role_id` pada users.
- Tempatkan aturan bisnis pada Domain/Application Service, bukan Controller, Filament, Livewire, Blade, JavaScript, atau Model Observer.
- Jalankan mutasi SPPB, workflow, approval, revisi, pembatalan, dan pelepasan dalam transaksi dengan locking, idempotensi, serta event `afterCommit`.
- Jangan membaca, menampilkan, menyalin, atau mengubah rahasia dalam `.env` kecuali pengguna secara eksplisit meminta tindakan yang aman dan diperlukan.
- Jangan mengubah aturan atau skema beku tanpa instruksi eksplisit, analisis dampak, dan ADR.

## Kunci Filament v5

Lakukan langkah berikut sebelum menulis kode Filament:

1. Periksa `composer.json` dan `composer.lock` untuk major dan versi Filament terpasang.
2. Periksa signature aktual di `vendor/filament` ketika ada keraguan API.
3. Gunakan dokumentasi resmi untuk major yang sama; jangan mengandalkan ingatan model.
4. Untuk baseline v5.6.8, gunakan `Filament\Schemas\Schema` dan signature Resource `form(Schema $schema): Schema`.
5. Tolak pola Filament v3/v4 seperti parameter `Filament\Forms\Form`, import lama, atau contoh API yang tidak tersedia pada vendor terpasang.
6. Jalankan pencarian pola lama dan test Filament sebelum menyatakan selesai.

Jika source package terpasang bertentangan dengan contoh eksternal, ikuti source package terpasang.

## Ikuti alur kerja

### Analisis atau review

1. Sajikan temuan berdasarkan tingkat risiko, mulai dari pelanggaran frozen rule.
2. Sertakan bukti file dan baris.
3. Bedakan masalah blueprint, implementasi, generator, dan operasi.
4. Jangan mengubah file jika pengguna hanya meminta analisis atau diagnosis.

### Implementasi

1. Pastikan fase aktif dari continuation log dan blueprint.
2. Nyatakan dampak database, API, Flutter, keamanan, workflow, audit, dan testing.
3. Buat checkpoint sebelum pekerjaan berisiko atau multi-file.
4. Implementasikan satu perubahan atomik melalui service/use case yang sama untuk Filament dan API.
5. Terapkan Policy, DTO/Form Request, transaksi, locking, idempotensi, dan audit sesuai risiko.
6. Gunakan event/listener atau queue hanya sesuai kontrak blueprint.
7. Jalankan quality gate fase dan perbarui continuation log.
8. Berhenti pada batas fase dan laporkan pekerjaan tersisa.

### Database dan Laravel Blueprint

1. Ubah desain hanya melalui `technical-work/e-sppb-enterprise/draft.yaml`.
2. Validasi YAML sebelum build.
3. Simpan baseline generator sebelum koreksi manual.
4. Review migration dan model untuk FK action, urutan migration, circular FK, decimal, enum cast, check constraint, index, locking, dan relasi alias.
5. Jangan memakai `blueprint:erase` tanpa pemeriksaan worktree dan persetujuan eksplisit.
6. Wajibkan build kedua idempoten, PHP lint lulus, serta `migrate:fresh --seed` dan rollback lulus sebelum memindahkan artefak generator.

### Manajemen role dan permission

1. Gunakan tabel, trait, dan registrar resmi Spatie Laravel Permission.
2. Gunakan `RoleManagementService` untuk mutasi role, sinkronisasi permission, assignment user, cache reset, dan audit.
3. Lindungi role sistem dan super admin terakhir.
4. Batasi UI melalui Policy dan permission `role.view`, `role.manage`, `permission.view`, serta `permission.manage`.
5. Jangan menyediakan pembuatan permission bebas melalui UI produksi; kelola permission kanonik melalui kode atau seeder idempoten.

## Validasi minimum

Sesuaikan pemeriksaan dengan perubahan, lalu minimal jalankan:

- `rg` untuk memastikan tidak ada `Company`, `company_id`, atau pola Filament lama pada area yang diubah.
- Parser YAML jika `draft.yaml` berubah.
- PHP lint, Pint, PHPStan/Larastan, dan test yang relevan.
- Test Policy dan role/permission bila otorisasi berubah.
- Test transaksi, konkurensi, retry, dan idempotensi untuk workflow atau pelepasan barang.
- Query-count/N+1 dan test UI untuk Resource atau dashboard Filament.
- `git diff --check` dan review diff tanpa mengganggu perubahan pengguna yang tidak terkait.

Jangan menyatakan selesai bila quality gate relevan gagal. Laporkan kegagalan dan risiko tersisa secara jelas dalam Bahasa Indonesia.
