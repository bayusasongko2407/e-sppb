# Prompt Antigravity: Remediasi Audit E-SPPB Enterprise

Gunakan prompt ini pada Antigravity untuk memperbaiki proyek `/www/wwwroot/e-sppb-enterprise` berdasarkan audit tanggal 14 Juli 2026.

---

## Peran dan Tujuan

Anda adalah Antigravity 2.0 yang bertanggung jawab melakukan remediasi teknis E-SPPB Enterprise secara aman, bertahap, teruji, dan patuh terhadap Master Blueprint beku.

Jangan hanya memberi saran. Periksa repository, implementasikan perubahan yang diizinkan pada fase aktif, jalankan quality gate, dokumentasikan hasil, lalu berhenti pada batas fase.

Target akhir adalah mengubah status proyek dari **NO-GO** menjadi **GO** untuk staging dengan menutup temuan pada:

`docs/AUDIT_CODEBASE_2026-07-14.md`

Audit adalah backlog remediasi, tetapi bukan izin untuk mengubah schema atau aturan bisnis beku tanpa prosedur yang diwajibkan.

## Direktori Kerja

Kerjakan hanya repository:

`/www/wwwroot/e-sppb-enterprise`

Jangan mengerjakan repository lain yang memiliki nama serupa.

## Konteks Wajib

Sebelum mengubah file, baca seluruh dokumen berikut:

1. `Agents.md`
2. `.codex/00_INDEX.md`
3. `memory/continuation-log.md`
4. `.agents/skills/antigravity-e-sppb/SKILL.md`
5. `.agents/skills/antigravity-e-sppb/references/frozen-rules.md`
6. `technical-work/e-sppb-enterprise/antigravity-blueprint.md`
7. `technical-work/e-sppb-enterprise/draft.yaml`
8. `technical-work/e-sppb-enterprise/build-report.md`
9. `docs/AUDIT_CODEBASE_2026-07-14.md`
10. ADR terkait schema, legacy, permission, dan keputusan arsitektur.

Baca dokumen `/docs` dan `.codex` tambahan hanya sesuai kebutuhan temuan yang sedang dikerjakan.

## Aturan Beku

1. Plant adalah tingkat organisasi tertinggi.
2. Dilarang membuat `Company`, `companies`, `company_id`, `CompanyResource`, atau scope Company.
3. Gunakan Laravel 12, PHP 8.3, MariaDB 10.11+, dan Filament v5.6.8.
4. Verifikasi API Filament pada source package `vendor/filament` sebelum mengubah kode Filament.
5. Gunakan `Filament\Schemas\Schema`; jangan memakai pola Filament v3/v4.
6. Seluruh teks pengguna wajib berbahasa Indonesia.
7. Gunakan Spatie Laravel Permission dan jangan menambahkan `role_id` pada users.
8. Logika bisnis harus berada pada Application/Domain Service, bukan Resource, Page, Controller, Blade, JavaScript, atau Model Observer.
9. Mutasi SPPB, workflow, approval, revisi, pembatalan, dan pelepasan barang wajib memakai transaksi, locking, idempotensi, serta event setelah commit.
10. Jangan membaca, menampilkan, menyalin, atau menulis nilai rahasia ke file, output, log, test, commit, atau dokumentasi.
11. Jangan menjalankan `blueprint:erase`.
12. Jangan menghapus atau me-reset perubahan pengguna yang sudah ada.
13. Jangan memakai `git reset --hard`, `git checkout --`, atau command destruktif lain.
14. Kerjakan hanya satu fase remediasi pada satu sesi.

## Perlindungan Worktree

Anggap worktree sedang kotor dan berisi banyak file baru/perubahan pengguna.

Sebelum implementasi:

1. Jalankan `git status --short`.
2. Catat file yang sudah berubah sebelum sesi dimulai.
3. Jangan mengembalikan perubahan yang tidak dibuat pada sesi ini.
4. Review diff per file sebelum menyatakan selesai.

## Urutan Fase Remediasi

Jalankan fase secara berurutan. Jangan menggabungkan fase tanpa persetujuan eksplisit pengguna.

### R0: Keamanan dan Blocker Quality Gate

Fase aktif untuk eksekusi pertama adalah **R0**.

Target R0:

1. Tangani K-01 tanpa menampilkan nilai rahasia.
   - Hapus kredensial dari `memory/continuation-log.md`.
   - Ganti dengan pernyataan bahwa rahasia hanya boleh berada di `.env`.
   - Laporkan bahwa rotasi kredensial merupakan tindakan eksternal wajib oleh pemilik sistem.
   - Jangan mencoba login database menggunakan kredensial yang ditemukan di dokumentasi.
2. Perbaiki K-02.
   - Perbaiki seluruh factory yang gagal parse.
   - Gunakan model target FK yang benar.
   - Gunakan enum/status valid.
   - Hormati nullable field dan constraint bisnis.
   - Password factory harus di-hash.
   - Factory harus mendukung test workflow dan authorization.
3. Perbaiki K-06.
   - Hilangkan semua `dd()`/`dump()` dari jalur login.
   - Gunakan validation/form error Filament yang aman dan berbahasa Indonesia.
   - Jangan bocorkan exception internal.
4. Perbaiki T-11 dan T-12.
   - Redirect user inactive ke route login Filament yang benar.
   - Terapkan pemeriksaan `locked_until`.
   - Terapkan threshold failed login yang terkonfigurasi, waktu lock, reset aman, dan audit/log tanpa rahasia.
   - Tangani atomic update/race condition pada failed attempt.
5. Perbaiki T-09.
   - Hapus password default produksi dari seeder.
   - Seeder harus aman, idempoten, dan environment-aware.
   - Jangan menghasilkan atau mencetak password rahasia.
6. Perbaiki T-10.
   - Hapus atau koreksi relasi ke class `UploadedBy` dan `Correlation` yang tidak ada.
   - Gunakan explicit foreign key bila nama kolom tidak mengikuti konvensi Eloquent.
7. Tambahkan `declare(strict_types=1);` dan benahi format pada file yang disentuh R0.
8. Tambahkan test untuk factory, login email/NIK, invalid credential, inactive user, locked user, failed-attempt threshold, dan relasi yang diperbaiki.

Batas R0:

- Jangan mengubah schema database beku pada fase ini.
- Jangan memperbaiki kolom FK duplikat langsung di migration tanpa analisis dampak dan persetujuan.
- Untuk K-04, K-05, dan T-07, buat proposal ADR/catatan analisis schema sebagai blocker R1.
- Jangan mengimplementasikan queue workflow, policy lengkap, Goods Release, API, atau perubahan UI besar pada R0.

Kriteria selesai R0:

- Tidak ada nilai rahasia pada file repository yang diperiksa.
- Semua factory lolos PHP lint dan dapat di-autoload.
- Tidak ada `dd()`/`dump()` pada kode aktif.
- Test auth, factory, dan relasi R0 lulus.
- Pint file terkait lulus.
- PHPStan dapat melanjutkan melewati parse error factory.
- Proposal dampak schema K-04/K-05/T-07 tersedia tanpa mengubah schema.
- Continuation log diperbarui tanpa kredensial.

Setelah R0 selesai, berhenti dan minta persetujuan sebelum R1.

### R1: Integritas Schema dan Model

Jalankan hanya setelah R0 disetujui dan perubahan schema mendapat persetujuan/ADR.

Target:

1. K-04 kolom FK duplikat/salah.
2. K-05 foreign key constraint dan delete/update action.
3. T-07 check constraint detail SPPB.
4. Sinkronisasi `draft.yaml`, migration, model, factory, seeder, dan generated baseline.
5. Validasi MariaDB 10.11 `migrate:fresh --seed` dan rollback.

Alur wajib:

`analisis dampak -> ADR/persetujuan -> draft.yaml -> validasi YAML -> Blueprint build/review -> koreksi manual -> quality gate`

### R2: Workflow Queue, Locking, dan Idempotensi

Target:

1. K-03 workflow asynchronous.
2. T-03 delegated approval.
3. T-04 revalidation worker.
4. T-05 optimistic locking.
5. T-06 idempotensi request/command.
6. S-10 template kosong.
7. S-11 current sequence.
8. Event after commit, audit, notification, retry, backoff, timeout, dan failure handling.

Tambahkan test end-to-end, retry, duplicate command, stale command, concurrency, delegation, reject, revision, cancel, dan failure recovery.

### R3: Authorization dan Plant Isolation

Target:

1. K-07 Plant scoping.
2. T-01 seluruh policy.
3. T-02 My Approvals.
4. Permission canonical dan role minimum.
5. Test seluruh role, ownership, assignment, delegation, dan manipulasi lintas Plant.

Jangan gunakan middleware kosong sebagai satu-satunya pengamanan. Terapkan defense in depth pada query, policy, service, resolver, form option, route binding, export, dan API.

### R4: Service Integration, Goods Release, dan Attachment

Target:

1. K-08 CRUD Filament yang melewati service.
2. T-08 Goods Release service.
3. S-09 attachment security.
4. Running number concurrency.
5. Quantity guard, duplicate release prevention, delivery status, private storage, checksum, malware scan, dan download authorization.

Filament dan API wajib memanggil use case/service yang sama.

### R5: API, UI, dan Operasional

Target:

1. REST API `/api/v1`.
2. API contract dan Flutter readiness.
3. Locale `id` dan timezone `Asia/Jakarta`.
4. Correlation logging context.
5. Trusted proxy/HTTPS environment configuration.
6. Hapus Resource test internal dari production.
7. Penerjemahan status UI.
8. Frontend build reproducible.
9. README, deployment, queue, scheduler, health, monitoring, dan backup evidence.

### R6: Testing dan Release Gate

Target:

1. Menutup seluruh test gap pada audit.
2. PHPUnit coverage sesuai testing strategy.
3. Pint, PHPStan maksimum, migration, rollback, dependency audit, frontend build, N+1/query count, API contract, Filament UI, security, concurrency, dan idempotensi.
4. Menetapkan status GO/NO-GO berdasarkan bukti.

## Analisis Dampak Wajib

Untuk setiap perubahan, nyatakan:

- Dampak database.
- Dampak API.
- Dampak Flutter.
- Dampak keamanan.
- Dampak workflow.
- Dampak audit/logging.
- Dampak queue/retry.
- Dampak UI Filament.
- Dampak testing/deployment.

Jika tidak terdampak, tulis `Tidak ada` beserta alasan singkat.

## Standar Implementasi

1. Gunakan strict types, type hint, dan return type lengkap.
2. Gunakan exception spesifik dan pesan pengguna Bahasa Indonesia.
3. Gunakan transaksi dan locking sesuai risiko.
4. Gunakan DTO/Form Request pada input boundary.
5. Gunakan Policy untuk authorization.
6. Gunakan event/Job sesuai blueprint.
7. Hindari N+1 dengan eager loading terukur.
8. Jangan memakai `json_encode()` bila model mempunyai cast array yang tepat.
9. Jangan menampilkan raw status database; map enum ke label Bahasa Indonesia.
10. Jangan membuat bypass security sementara.
11. Jangan membuat test kosong hanya untuk menaikkan coverage.

## Quality Gate

Jalankan pemeriksaan relevan pada akhir setiap fase:

```bash
composer validate --strict
find app database bootstrap config routes tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l
vendor/bin/pint --test
vendor/bin/phpstan analyse app database routes --no-progress --debug --memory-limit=1G
php artisan test --colors=never
php artisan route:list --except-vendor
git diff --check
```

Tambahkan sesuai fase:

```bash
php artisan migrate:fresh --seed --force
php artisan migrate:rollback --force
npm run build
```

Untuk perubahan schema, migration wajib diuji pada MariaDB 10.11. SQLite hanya pemeriksaan tambahan.

Untuk workflow, uji queue backend yang sesuai staging/production, bukan hanya koneksi `sync`.

Pencarian frozen rule:

```bash
rg -n "Company|company_id|CompanyResource" app database technical-work/e-sppb-enterprise/draft.yaml
rg -n "Filament\\\\Forms\\\\Form|form\(Form \\$form\)" app
rg -n "dd\(|dump\(" app database routes tests
```

Jangan menyatakan fase selesai bila quality gate relevan gagal. Laporkan kegagalan, akar masalah, dan risiko tersisa.

## Dokumentasi

Pada akhir fase:

1. Perbarui `memory/continuation-log.md` tanpa rahasia.
2. Tandai ID temuan audit yang selesai dan masih terbuka.
3. Catat command quality gate beserta hasilnya.
4. Tambahkan/update ADR bila ada keputusan arsitektur atau schema.
5. Jangan mengubah laporan audit untuk menyembunyikan temuan; tambahkan status remediasi yang dapat ditelusuri.

## Format Laporan Akhir Antigravity

1. **Status fase:** selesai, belum selesai, atau blocked.
2. **Temuan yang ditutup:** ID audit dan bukti file/baris.
3. **Perubahan:** daftar file dan alasan.
4. **Dampak:** database, API, Flutter, security, workflow, audit, queue, UI, testing.
5. **Quality gate:** command, pass/fail, dan ringkasan output penting.
6. **Risiko tersisa:** temuan audit yang belum ditutup.
7. **Batas fase:** konfirmasi fase berikutnya belum dikerjakan.
8. **Persetujuan:** minta persetujuan eksplisit untuk fase berikutnya.

## Instruksi Eksekusi Sekarang

Mulai dengan **R0: Keamanan dan Blocker Quality Gate** saja.

Jangan mulai R1 atau fase setelahnya pada sesi yang sama. Jika perubahan schema diperlukan, dokumentasikan dampak dan siapkan proposal ADR, tetapi jangan menerapkannya sebelum persetujuan eksplisit.

Utamakan bukti yang dapat direproduksi. Jangan menyatakan selesai hanya karena aplikasi dapat boot atau dua test contoh lulus.
