# Audit Codebase E-SPPB Enterprise

Tanggal: 14 Juli 2026  
Lokasi: `/www/wwwroot/e-sppb-enterprise`  
Basis: kondisi filesystem/worktree saat audit, termasuk file yang belum dilacak Git.

## Ringkasan Eksekutif

Status rekomendasi: **NO-GO untuk staging/production**.

Fondasi Laravel, model, migration, service, dan Resource Filament sudah tersedia, tetapi alur utama belum tersambung end-to-end. Risiko tertinggi berada pada kebocoran kredensial, migration hasil generator yang salah, workflow queue yang tidak pernah diproses, plant isolation yang kosong, authorization yang belum berfungsi, transaksi Filament yang melewati service, dan tidak adanya test domain.

Temuan utama:

1. Kredensial database tersimpan dalam dokumentasi repository.
2. Empat belas factory mengandung parse error.
3. Tidak ada Job/Event/Listener untuk memproses workflow command.
4. Migration memiliki kolom FK duplikat/salah dan tidak menegakkan foreign key.
5. Login gagal menjalankan `dd()` dan membocorkan detail exception.
6. `ScopePlantMiddleware` belum melakukan apa pun.
7. Seluruh policy masih berupa stub `return false`.
8. Filament melakukan CRUD transaksi langsung, melewati Service Layer.
9. Test suite hanya berisi dua test contoh dengan dua assertion.
10. REST API `/api/v1` belum tersedia.

## Ruang Lingkup

Inventaris saat audit:

- Total file termasuk `.git`, `vendor`, dan aset publish: **25.929**.
- File selain `.git`, `vendor`, dan `node_modules`: **9.408**.
- File PHP aktif pada `app`, `database`, `bootstrap`, `config`, `routes`, dan `tests`: **259**.
- File PHP aktif tanpa `declare(strict_types=1)`: **143**.
- Factory dengan parse error: **14**.
- `return false` dalam policy: **84 lokasi**.

Dependency pihak ketiga dan aset minified tidak direview baris per baris sebagai kode aplikasi. Versi package, artefak, konfigurasi, dan kemampuan build tetap diperiksa. Folder `technical-work/e-sppb-enterprise/generated-preview` diperlakukan sebagai artefak generator, bukan kode runtime.

## Temuan Kritis

### K-01 [SELESAI] Kredensial database berada di repository

**Bukti:** `memory/continuation-log.md:38-40` memuat kredensial MySQL dalam teks biasa.

**Dampak:** kredensial dapat tersebar melalui Git, backup, CI, atau alat analisis.

**Perbaikan:** hapus dari seluruh file selain `.env`, rotasi password, periksa riwayat Git, dan tambahkan secret scanning. Nilai rahasia tidak direproduksi di laporan ini.

### K-02 [SELESAI] Empat belas factory rusak

**Bukti:**

- `database/factories/AssetFactory.php:5` berisi `use App\Models\;` dan `:18` berisi `::factory()` tanpa class.
- `database/factories/SppbHeaderFactory.php:5` serta beberapa FK pada `:20-35` tidak memiliki model target.
- `database/factories/WorkflowInstanceFactory.php:5` dan `:19` rusak.
- `database/factories/WorkflowStepFactory.php:5` dan `:22-23` rusak.
- `database/factories/WorkflowInstanceStepFactory.php:5` serta referensi `ActedBy` tidak valid.
- `database/factories/SppbStatusLogFactory.php:5` serta model `Correlation` tidak valid.

**Dampak:** PHP lint, PHPStan, fixture, dan test domain tidak dapat berjalan penuh. Hasil PHPUnit hijau saat ini adalah false confidence karena factory tidak dimuat oleh test contoh.

### K-03 Workflow asynchronous belum diimplementasikan

**Bukti:**

- `app/Services/WorkflowService.php:37-40` menyatakan dispatch dilakukan `afterCommit`, tetapi `queueSubmission()` hanya membuat row command.
- `app/Services/WorkflowService.php:166-169` melakukan hal serupa untuk approval.
- Tidak ada `app/Jobs`, `app/Events`, atau `app/Listeners`.
- Tidak ditemukan `dispatch()`, `ShouldQueue`, `ShouldBeUnique`, `WithoutOverlapping`, atau event `afterCommit`.

**Dampak:** SPPB berhenti pada `SUBMISSION_QUEUED`; workflow dan approval command tidak pernah diproses otomatis.

**Perbaikan:** buat Job per command, dispatch setelah commit, unique/overlap guard, retry/backoff/timeout, `failed()`, status command, correlation ID, dan test retry/idempotensi.

### K-04 Migration memiliki kolom FK duplikat/salah

**Bukti:**

- `database/migrations/2026_07_14_063611_create_workflow_instance_steps_table.php:29-32` membuat `acted_by` dan `acted_by_id`; `acted_by_id` wajib diisi.
- `database/migrations/2026_07_14_063615_create_attachments_table.php:27-29` membuat `uploaded_by` dan `uploader_id`.
- `database/migrations/2026_07_14_063618_create_goods_releases_table.php:21-40` membuat `created_by`, `sender_user_id`, `receiver_user_id`, dan `received_by`, lalu menduplikasinya menjadi `created_by_id`, `sender_user_id_id`, `receiver_user_id_id`, dan `received_by_id`.

**Dampak:** `WorkflowService::generateWorkflow()` pada `app/Services/WorkflowService.php:109-125` tidak mengisi `acted_by_id`, sehingga insert step dapat gagal. Form Goods Release juga tidak mengisi banyak kolom wajib.

**Perbaikan:** selaraskan `draft.yaml`, migration, model, relation foreign key, factory, form, dan service melalui proses perubahan schema/ADR yang berlaku.

### K-05 Foreign key constraint tidak ditegakkan

**Bukti:** migration memakai `$table->foreignId(...)` tanpa `->constrained()`, `->references()`, atau foreign key terpisah. Contoh: `database/migrations/2026_07_14_063608_create_sppb_headers_table.php:18-34`, `...063609_create_sppb_details_table.php:16-24`, dan `...063610_create_workflow_instances_table.php:17-18`.

**Dampak:** user, Plant, department, location, workflow, item, asset, dan SPPB dapat memiliki orphan reference. Aturan delete/update tidak dijamin MariaDB.

### K-06 [SELESAI] Login gagal menggunakan `dd()`

**Bukti:** `app/Filament/Pages/Auth/CustomLogin.php:74-77` menangkap exception lalu memanggil `dd()`.

**Dampak:** request berhenti dengan debug dump dan detail internal berpotensi terlihat pengguna.

**Perbaikan:** teruskan `ValidationException` ke Filament atau tampilkan form error aman; detail hanya dicatat di log terkontrol.

### K-07 Plant scoping belum ada

**Bukti:** `app/Http/Middleware/ScopePlantMiddleware.php:18-24` hanya berisi komentar dan langsung meneruskan request.

**Dampak:** query, dropdown, export, dan route binding berpotensi membaca record Plant lain.

### K-08 Resource transaksi melewati Service Layer

**Bukti:**

- `app/Filament/Resources/SppbHeaders/Pages/CreateSppbHeader.php` dan `EditSppbHeader.php` memakai CRUD default.
- `app/Filament/Resources/SppbHeaders/SppbHeaderResource.php:71-90` menyimpan detail melalui Repeater relationship langsung.
- `app/Filament/Resources/GoodsReleases/Pages/CreateGoodsRelease.php` memakai `CreateRecord` default.
- `app/Filament/Resources/GoodsReleases/GoodsReleaseResource.php:45-89` menyimpan release dan item melalui relationship langsung.

**Dampak:** validasi state, transaksi agregat, locking, nomor dokumen, quantity guard, idempotensi, audit, dan event tidak dijalankan.

## Temuan Tinggi

### T-01 Policy belum berfungsi

**Bukti:** 84 method policy mengembalikan `false`, termasuk `app/Policies/SppbHeaderPolicy.php:14-64`, `GoodsReleasePolicy.php:14-64`, dan `WorkflowInstanceStepPolicy.php:14-64`. `app/Providers/AppServiceProvider.php:40-42` hanya memberi bypass untuk `super_admin`.

**Dampak:** role normal tidak dapat menggunakan fitur. Jika policy dilonggarkan tanpa Plant scoping, akses lintas Plant dapat terjadi.

### T-02 My Approvals tidak memfilter approver

**Bukti:** `app/Filament/Resources/MyApprovals/MyApprovalResource.php:92-96` hanya memfilter `WAITING_APPROVAL`, tanpa assignment, delegasi, current approver, atau Plant. `ViewMyApproval.php:10-13` juga tidak menyediakan action approve/reject/revision.

**Dampak:** setelah policy dibuka, approver dapat melihat SPPB yang bukan tugasnya; fitur persetujuan belum operasional.

### T-03 Delegated approval rusak

**Bukti:** queue validation menerima delegate pada `WorkflowService.php:190-209`, tetapi `approve()` meng-update assignment menggunakan `approver_id = actorId` pada `:243-251`. Assignment tetap dimiliki delegator.

**Dampak:** update dapat memengaruhi nol row dan approval delegasi macet.

### T-04 Worker tidak memvalidasi ulang authorization/status

**Bukti:** validasi dilakukan saat queue, tetapi `approve()`, `reject()`, dan `requestRevision()` tidak memverifikasi ulang assignment aktif, command status, dan stale state sebelum mutasi. `reject()` pada `WorkflowService.php:359-365` meng-update assignment tanpa filter status.

**Dampak:** retry atau command stale dapat mengubah workflow yang sudah berubah.

### T-05 `lock_version` tidak digunakan

**Bukti:** kolom tersedia pada SPPB dan workflow step, tetapi service tidak membandingkan versi input atau menaikkannya saat mutasi. Contoh `WorkflowService.php:272-275`, `:378-382`, `:439-443`, dan `SppbService.php:65-75`.

**Dampak:** concurrent edit/approval dapat saling menimpa.

### T-06 Idempotensi request belum benar

**Bukti:** duplicate UUID menjadi exception pada `WorkflowService.php:51-55` dan `:172-176`, sedangkan `SppbService.php:130` selalu menghasilkan UUID baru pada setiap klik submit.

**Dampak:** klik ganda bukan retry idempoten dari command yang sama.

### T-07 Constraint detail SPPB tidak lengkap

**Bukti:** `database/migrations/2026_07_14_063609_create_sppb_details_table.php:18-25` tidak memiliki check constraint untuk XOR `item_id`/`asset_id`, quantity positif, dan konsistensi `barcode_confirmed`. Form `SppbHeaderResource.php:75-87` menampilkan item dan asset bersamaan serta tidak mengisi beberapa field wajib migration.

**Dampak:** data invalid atau kegagalan SQL saat create melalui Filament.

### T-08 Goods Release belum mempunyai service bisnis

**Bukti:** tidak ada `GoodsReleaseService`; `DuplicateGoodsReleaseException` dan `QuantityExceededException` tidak digunakan. Nomor surat jalan dibuat dengan `date()` dan `rand()` pada `GoodsReleaseResource.php:50-53`.

**Dampak:** collision nomor, over-release, release ganda, dan status delivery yang tidak konsisten.

### T-09 [SELESAI] Seeder membuat password default lemah

**Bukti:** `database/seeders/DatabaseSeeder.php:32-38` membuat akun super admin tetap dengan password `password`.

**Dampak:** deployment yang menjalankan seeder dapat memiliki akun mudah ditebak.

### T-10 [SELESAI] Model merujuk class yang tidak ada

**Bukti:**

- `app/Models/Attachment.php:62-65` merujuk `UploadedBy::class`.
- `app/Models/ActivityLog.php:53-56` dan `app/Models/SppbStatusLog.php:71-74` merujuk `Correlation::class`.

Class tersebut tidak ada di repository.

**Dampak:** class-not-found saat relasi dipanggil/eager loaded.

### T-11 [SELESAI] Redirect user inactive menuju route yang tidak ada

**Bukti:** `app/Http/Middleware/EnsureUserIsActive.php:26` memakai `route('login')`, sementara route login aktif bernama `filament.admin.auth.login`.

**Dampak:** user inactive dapat menerima `RouteNotFoundException`.

### T-12 [SELESAI] Account lockout belum ditegakkan

**Bukti:** `AuthService.php:60-68` hanya menaikkan counter; `locked_until` tidak pernah diperiksa dan komentar menyebut logika lock masih akan ditambahkan.

**Dampak:** perlindungan brute force tidak berfungsi.

### T-13 REST API belum tersedia

**Bukti:** tidak ada `routes/api.php`, controller API, Form Request API, API Resource, atau route `/api/v1`.

**Dampak:** kontrak API-first dan kesiapan Flutter belum terpenuhi.

### T-14 Test domain tidak ada

**Bukti:** hanya `tests/Unit/ExampleTest.php` dan `tests/Feature/ExampleTest.php`, total dua assertion generik. `memory/continuation-log.md:30-36` juga menyatakan Fase 6 belum dimulai.

**Dampak:** workflow, authorization, concurrency, idempotensi, Plant isolation, goods release, upload, dan role management tidak memiliki regression safety net.

## Temuan Sedang

### S-01 Dependency tidak sesuai frozen contract

**Bukti:** `composer.json:9` mengizinkan PHP `^8.2`, bukan `^8.3`; `composer.json:13` memasang Spatie Permission `^8.3`, sedangkan frozen rule menyebut v6.

Versi terpasang:

- Laravel `12.63.0`.
- Filament `5.6.8`.
- Spatie Permission `8.3.0`.

### S-02 Strict typing belum konsisten

**Bukti:** 143 dari 259 file PHP aktif tidak mengandung `declare(strict_types=1)`. Klaim pada `memory/continuation-log.md:28` bahwa model sudah mengadopsinya tidak sesuai kondisi seluruh file.

### S-03 Pint gagal luas

`vendor/bin/pint --test` gagal pada bootstrap, provider, Resource Filament, middleware, service, model, policy, seeder, generated preview, dan factory.

### S-04 PHPStan belum dapat menyelesaikan analisis

Mode debug menemukan parse error lalu berhenti dengan hasil incomplete. Error tipe/relasi lain kemungkinan masih tersembunyi setelah factory diperbaiki.

### S-05 Locale dan timezone masih default

**Bukti:** `config/app.php:68` memakai `UTC`; `:81-85` default ke `en` dan `en_US`.

**Dampak:** timestamp bisnis dapat bergeser dan pesan bawaan dapat muncul dalam bahasa Inggris.

### S-06 HTTPS dipaksa pada semua environment

**Bukti:** `app/Providers/AppServiceProvider.php:38` selalu memanggil `URL::forceScheme('https')`.

**Dampak:** URL local/test/CLI dapat salah.

### S-07 Semua proxy dipercaya

**Bukti:** `bootstrap/app.php:14` memakai `trustProxies(at: '*')`.

**Dampak:** header forwarded dapat dipalsukan bila aplikasi dapat diakses tanpa reverse proxy tepercaya.

### S-08 Correlation ID belum masuk logging context

**Bukti:** `EnsureCorrelationId.php:25-26` hanya memuat contoh Context API sebagai komentar.

**Dampak:** request, log, Job, dan audit belum dapat ditelusuri dengan satu correlation ID secara konsisten.

### S-09 Upload belum aman dan tidak sinkron dengan model Attachment

**Bukti:** `SppbHeaderResource.php:63-66` hanya mengatur multiple upload dan directory, tanpa MIME allowlist, batas ukuran, disk private eksplisit, checksum, metadata, antivirus scan, atau quarantine.

### S-10 Template kosong dapat merusak workflow

**Bukti:** `WorkflowService.php:94-108` tidak menolak template tanpa step sebelum memakai `$steps->first()->sequence`.

### S-11 `WorkflowInstance.current_sequence` tidak diperbarui

**Bukti:** `WorkflowService.php:103` mengisinya `null`; approval berikutnya hanya mengubah `SppbHeader.current_step_sequence` pada `:305`.

### S-12 Running number mempunyai race pada row pertama

**Bukti:** `RunningNumberService.php:22-39` mengunci row yang mungkin belum ada lalu membuat row baru. Dua transaksi awal dapat sama-sama tidak menemukan row dan salah satunya terkena unique violation tanpa retry.

### S-13 README dan root page masih skeleton Laravel

`README.md`, `resources/views/welcome.blade.php`, dan `routes/web.php:5-7` belum menjelaskan instalasi, queue worker, deployment, quality gate, atau entry point E-SPPB.

### S-14 Resource test internal terdaftar di panel

**Bukti:** route `admin/tests` berasal dari `app/Filament/Resources/Tests/TestResource.php`.

**Dampak:** artefak eksperimen dapat muncul pada panel production.

### S-15 Frontend tidak dapat dibangun pada kondisi audit

**Bukti:** `npm run build` gagal dengan `vite: not found`; dependency Node belum terpasang.

## Temuan Rendah

1. `git diff --check` gagal karena blank line tambahan di `app/Providers/AppServiceProvider.php:45`.
2. Mayoritas implementasi masih untracked sehingga review berbasis commit dan recovery perubahan sulit.
3. `generated-preview` ikut dipindai Pint; exclusion artefak generator perlu diperjelas.
4. Nama relation hasil generator tidak konsisten, misalnya `delegationsGivens()` dan `delegationsReceiveds()` pada `app/Models/User.php:106-114`.
5. UI masih menampilkan raw status database berbahasa Inggris, misalnya `MyApprovalResource.php:77-79` dan `GoodsReleaseResource.php:110-112`.
6. Dropdown detail Goods Release tidak dibatasi berdasarkan SPPB yang dipilih (`GoodsReleaseResource.php:73-80`).
7. `WorkflowTemplateResolver` tidak memiliki strategi memilih versi tertinggi bila beberapa versi aktif dan efektif.

## Hasil Quality Gate

| Pemeriksaan | Hasil | Catatan |
|---|---|---|
| `composer validate --strict` | Lulus | Struktur valid, bukan bukti versi sesuai blueprint. |
| `php artisan test` | Lulus semu | 2 test contoh, 2 assertion. |
| PHP lint | Gagal | 14 factory gagal parse. |
| Pint `--test` | Gagal | Banyak style issue dan parse error. |
| PHPStan | Gagal/incomplete | Berhenti setelah severe parse errors. |
| Route list | Lulus | 54 route web/Filament; tidak ada API. |
| SQLite `migrate:fresh --seed` | Lulus | Tidak membuktikan kompatibilitas MariaDB/FK. |
| SQLite rollback | Lulus | Rollback berhasil. |
| MariaDB migrate status | Tidak terverifikasi | Sandbox menolak akses socket MySQL. |
| YAML parser PHP | Tidak terverifikasi | Extension `yaml` tidak terpasang. |
| Frontend build | Gagal | `vite` tidak ditemukan. |
| `git diff --check` | Gagal | Blank line AppServiceProvider. |
| Larangan Company | Tidak ditemukan pelanggaran runtime utama | Penyebutan pada dokumen larangan/artefak lama dikecualikan. |
| Filament v3 Form signature | Tidak ditemukan pada kode aktif | Kode aktif memakai `Filament\Schemas\Schema`. |

## Gap Terhadap Blueprint

Sudah tersedia sebagian:

- Laravel 12 dan Filament 5.6.8.
- Plant sebagai tingkat organisasi tertinggi.
- Enum, DTO, contract, service awal, model, migration, dan Resource Filament dasar.
- Spatie Permission dan role minimum.
- Sebagian transaksi dan `lockForUpdate()` pada service.

Belum tersedia atau belum selesai:

- Arsitektur modular `Domain/Application/Infrastructure/Presentation`.
- Queue Job end-to-end, unique command, retry, failure handling, dan event after commit.
- Policy, permission, dan Plant authorization aktual.
- REST API `/api/v1` dan API contract test.
- Goods release service dan quantity ledger.
- Attachment service aman dan antivirus flow.
- Audit login/logout serta correlation context.
- Test workflow, concurrency, retry, idempotensi, policy, Plant isolation, upload, dan N+1.
- SLA/escalation scheduler, queue monitoring, health readiness, serta bukti backup/restore.
- Penerjemahan semua status/pesan dan validasi design system.

## Urutan Remediasi

### Prioritas 0: keamanan dan blocker build

1. Rotasi dan hapus kredensial dari repository/history.
2. Hilangkan `dd()` dari login dan benahi route user inactive.
3. Perbaiki 14 factory agar lint/PHPStan dapat berjalan penuh.
4. Koreksi draft/migration/model yang menghasilkan FK duplikat dan relation palsu melalui ADR/blueprint.
5. Tambahkan FK/check constraint dan validasi pada MariaDB 10.11.

### Prioritas 1: integritas workflow

1. Implementasikan Job submission/approval dengan unique command, retry, backoff, timeout, `failed()`, dan dispatch after commit.
2. Validasi ulang status, actor, assignment, delegation, lock version, dan command status di worker.
3. Implementasikan event, audit, notification, dan status log yang idempoten.
4. Tambahkan test end-to-end Draft sampai Completed, termasuk reject, revision, cancel, retry, dan concurrent approval.

### Prioritas 2: authorization dan Plant isolation

1. Implementasikan seluruh policy dan permission canonical.
2. Terapkan Plant scope pada query, dropdown, resolver, service, route binding, dan export.
3. Filter My Approvals berdasarkan assignment/delegation aktif.
4. Tambahkan test seluruh role dan manipulasi lintas Plant.

### Prioritas 3: transaksi SPPB dan Goods Release

1. Hubungkan Filament ke SppbService/use case, bukan CRUD langsung.
2. Implementasikan GoodsReleaseService dengan running number, quantity validation, locking, idempotensi, delivery status, dan audit.
3. Implementasikan attachment service aman.
4. Matikan action generik yang melanggar state machine.

### Prioritas 4: API dan operasi

1. Bangun `/api/v1` dengan service/use case yang sama seperti Filament.
2. Tambahkan API contract test dan Flutter readiness.
3. Benahi strict types, Pint, PHPStan, N+1 check, dan coverage domain.
4. Pasang dependency Node dan pastikan production build reproducible.
5. Selaraskan locale `id`, timezone `Asia/Jakarta`, Redis queue/cache, logging, deployment, dan monitoring.

## Kriteria Minimum Sebelum Staging

- Tidak ada secret di repository dan kredensial bocor sudah dirotasi.
- Semua file PHP lolos lint.
- Pint dan PHPStan lulus.
- `migrate:fresh --seed` dan rollback lulus pada MariaDB 10.11 dengan FK/check aktif.
- Test workflow, concurrency, retry, idempotensi, policy, dan Plant isolation lulus.
- Tidak ada CRUD transaksi yang melewati service/use case.
- Queue worker memproses command dan aman terhadap retry.
- Goods release tidak dapat melebihi quantity yang diizinkan.
- Login gagal memberikan pesan aman tanpa dump.
- Frontend production build berhasil.
- API, health check, scheduler, private upload, audit, dan backup restore terverifikasi.

## Kesimpulan

Repository sudah mempunyai dokumentasi desain dan scaffold yang luas, tetapi implementasi aktual masih berada pada tahap integrasi awal meskipun continuation log menyatakan Fase 5 selesai. Risiko utama adalah integritas schema, workflow yang tidak pernah diproses, authorization yang belum aktif, kemungkinan akses lintas Plant, dan tidak adanya test domain.

Proyek sebaiknya tidak masuk staging/production sampai Prioritas 0 dan Prioritas 1 selesai dan seluruh quality gate dijalankan ulang pada MariaDB serta queue backend yang sesuai blueprint.
