## Active Checkpoint

- Updated at: 2026-07-15 Asia/Jakarta
- Phase: Perbaikan Infrastruktur Testing & Koreksi Skema Workflow (SELESAI)
- Status: Menghilangkan infinite loop pada DocumentGenerationFactory, memperbarui test suite (WorkflowServiceTest, SppbEndToEndTest, DocumentGenerationServiceTest) agar selaras dengan skema database terbaru (kolom JSON approver_position_ids) dan perilaku eksekusi sinkron. Seluruh 27 unit/feature test lulus 100% (PASS).

## Ringkasan UI/UX Refactoring SPPB Resource (Selesai)

Berhasil merefaktor presentasi layer SppbHeaderResource tanpa mengubah database, migrasi, business logic, atau workflow:

1. **SppbHeaderResource.php**: Diperbarui untuk mendelegasikan `form()`, `infolist()`, dan `table()` ke kelas schema/table terpisah. Menambahkan metode `infolist()` untuk halaman View.
2. **SppbHeaderForm.php**: Schema form enterprise lengkap dengan:
   - Header Section 5-row (No. SPPB readonly, Tgl Permintaan readonly, Status badge, Plant, Department, Requester)
   - Lokasi Asal/Tujuan + Keperluan (span 4 kolom kanan)
   - Alamat otomatis readonly multiline di bawah setiap lokasi
   - Tanggal Kebutuhan + Keterangan (textarea span 5 kolom)
   - Lampiran FileUpload full width
   - Detail Barang Repeater full width dengan ToggleButtons Asset/Non-Asset
   - Workflow Timeline horizontal full width (tersembunyi saat create)
3. **SppbHeaderInfolist.php**: Infolist schema identik untuk halaman View (semua readonly), menggunakan TextEntry dan RepeatableEntry.
4. **SppbHeadersTable.php**: Table dengan badge Status, filter status, sort default created_at desc.
5. **Pages**: CreateSppbHeader redirect ke view, EditSppbHeader redirect ke view setelah simpan.



## Ringkasan Perombakan Master Data & Enum Controls (Selesai)

Berhasil melakukan standarisasi struktur Master Data sesuai kebutuhan bisnis operasional terbaru:
1. **EnumControls**: Mengimplementasikan arsitektur tabel dinamis (`enum_controls`) untuk manajemen opsi _dropdown_ klasifikasi yang aman diedit oleh Admin (seperti Kategori Unit, Kondisi Aset, Status Aset) tanpa mengubah source code.
2. **Perbaikan Skema Plants, Departments, Locations**: 
   - Konversi dan rasionalisasi field deskripsi menjadi alamat (di tabel Plants).
   - Penghapusan field deskripsi opsional untuk memfokuskan pendataan inti.
   - Pemasangan auto-generasi kode `LOC-0000X` yang atomik di model `Location`.
3. **Perombakan Manajemen Aset**:
   - Skema disederhanakan: `asset_location_address` dihapus, `asset_location_name` diubah menjadi referensi `asset_location_data`.
   - Menambahkan field mandatory `asset_name` dan _foreign key_ `unit_id`.
4. **Testing & QA**: Mengonversi `PlantFactory`, `LocationFactory`, `DepartmentFactory`, dan `AssetFactory` pada _test suite_. Seluruh Feature Test dan unit test tetap lulus dengan `0` error PHPStan.

## Ringkasan Modul Hak Akses & Roles Control (Selesai)
1. **RoleResource Terintegrasi**: Menggunakan standar `Spatie\Permission\Models\Role` untuk menyediakan menu *Roles / Hak Akses* pada navigasi *Sistem*. 
2. **Model Checklist Matrix**: Menggunakan `CheckboxList` dengan fitur `bulk toggleable` agar perizinan (permissions) disajikan lengkap dan mudah dikelola oleh administrator tanpa batasan layout tradisional.
3. **Seeding Otomatis Izin (Permissions)**: Mensintesis dan men-*seed* izin dasar CRUD (seperti `view`, `view_any`, `create`, `update`, `delete`) untuk seluruh modul inti (Plants, Departments, Locations, Units, Positions, Users, Items, Assets, EnumControls, Roles, SppbHeaders) sehingga opsi otorisasi menjadi berwujud nyata di database.
4. **Command Sinkronisasi Dinamis**: Membuat Custom Artisan Command `php artisan auth:sync-permissions` yang mampu memindai model-model baru di masa depan secara otomatis dan menambahkannya ke daftar Checklist perizinan di layar `RoleResource`.
5. **Policy Refactoring**: Merevisi paksa metode default seluruh Policy aplikasi (`PlantPolicy`, `UserPolicy`, dll.) agar bereaksi memeriksa `$user->hasPermissionTo(...)` dibanding nilai `false` rekaan Laravel, melengkapi siklus hidup otorisasi dari UI menuju backend. Seluruh proses audit PHPStan dan pengujian berlalu tanpa kendala.

## Ringkasan FASE 5 (Selesai)

Berhasil membangun seluruh kerangka Resource Filament v5 murni menggunakan `Filament\Schemas\Schema`, seluruhnya berbahasa Indonesia, dan mematuhi aturan ketat blueprint:

1. **Master Data Resources:**
   - PlantResource, DepartmentResource, LocationResource, UnitResource, PositionResource, UserResource, ItemResource, AssetResource.
2. **Workflow Master:**
   - WorkflowTemplateResource (beserta setingan repeater step berurutan).
   - WorkflowDelegationResource.
3. **Transaksi SPPB (`SppbResource`):**
   - Diatur sebagai Master-Detail form dengan Tabs (Informasi, Daftar Barang Repeater, Rekam Workflow).
4. **Persetujuan & Monitoring:**
   - `MyApprovalResource`: Form Split-Screen (Grid 3) untuk Approver, tersambung query `WAITING_APPROVAL`.
   - `GoodsReleaseResource`: Khusus modul pengiriman barang (Admin).
   - `WorkflowInstanceResource`: Monitoring transparan Mode Read-Only khusus untuk viewer (Gudang).

## Keputusan User (Terkonfirmasi)

- UI Layout SPPB: Master-Detail dengan mode Tab (bukan wizard penuh).
- Plant sebagai entitas hierarki tertinggi (Company dilarang).
- Penggunaan mutlak antarmuka dan label Bahasa Indonesia.
- Model telah mengadopsi standar PHP `declare(strict_types=1);`.

## Ringkasan FASE R0 (Selesai)

Berhasil meremediasi temuan Prioritas 0 (Keamanan dan Blocker Build):
1. **K-01**: Kredensial hardcoded telah dihapus.
2. **K-02**: Empat belas factory rusak telah diperbaiki, relasi model dibenahi, unique constraint email diterapkan, dan efisiensi hashing password dioptimalkan.
3. **K-06**: Penggunaan `dd()` saat login gagal diubah menjadi `ValidationException` yang aman.
4. **T-09**: Seeder tidak lagi menghasilkan super admin dengan password default `password`.
5. **T-10**: Relasi model ke class yang tidak ada dihapus (Correlation) atau disesuaikan (UploadedBy ke User).
6. **T-11 & T-12**: Account lockout telah diterapkan (5 gagal login, kunci 15 menit), model User dikonfigurasi dengan cast `datetime` untuk field login, serta exception redirect yang aman.
7. **Pint, PHPStan, Tests**: Seluruh Quality Gate lulus dengan status OK.
8. **ADR-001**: Proposal perbaikan schema (K-04, K-05, T-07) disusun sebagai blocker masuk FASE R1.



## Ringkasan FASE R1 (Selesai)

Berhasil melaksanakan tahap penjaminan mutu menyeluruh (Quality Assurance) dan integritas workflow:
1. **Schema Remediation:** Constraints K-04, K-05, T-07 dari ADR-001 telah diimplementasikan penuh. Long index names disesuaikan dan Check Constraints ditambahkan via migrations.
2. **Unit & Feature Testing:** Dibuat `WorkflowServiceTest` dengan 6 test case yang mencakup pengujian queuesubmission, assign approvers, authorization, delegasi, dan idempotency.
3. **End-to-End Testing:** Dibuat `SppbEndToEndTest` yang menguji siklus lengkap (Draft → Submit → Manager Approval → BAT Approval → Approved).
4. **Security Testing:** WorkflowService memverifikasi bahwa actor yang melakukan approval adalah approver yang sah (atau delegated approver).
5. **Quality Gate:** PHPStan level maksimal dan 15 test suite berlalu dengan status hijau (OK).

## Ringkasan FASE 6 (Selesai)

Berhasil mengimplementasikan fase penjaminan mutu dan mengunci Core Engine:
1. **GoodsReleaseService:** Mengembangkan implementasi definitif untuk Pelepasan Barang (`createGoodsRelease`). Secara atomik memvalidasi SPPB yang sudah disetujui, mencatat snapshot alamat asal/tujuan, mengunci database, mengatur pembuatan *running sequence number* dokumen SJ, serta mengubah status SPPB (dari `APPROVED` ke `RELEASE_IN_PROGRESS` hingga menjadi `COMPLETED`).
2. **DTO & Exceptions:** Menambahkan DTO `CreateGoodsReleaseData` dan exception `InvalidGoodsReleaseQuantityException` untuk melindungi integritas rilis fisik.
3. **SppbServiceTest:** Menambahkan unit dan feature test lengkap untuk `SppbService` (memvalidasi alur draft, manipulasi detail, larangan SPPB asal/tujuan sama, dan proteksi draft yang tidak boleh disubmit bila detail kosong).
4. **GoodsReleaseServiceTest:** Mengimplementasikan uji coba validasi (tidak dapat merilis SPPB belum disetujui, kuantitas dilarang melewati batas, dan *happy path* partial -> full goods release).
5. **Quality Gate:** PHPStan level maksimum dan 24 Feature Test suite berlalu dengan status hijau (OK). Lint dan statis kode (Pint, Larastan) telah lulus sempurna.

## Next Steps

Tugas terkait Core Engine dan Testing Pelepasan Barang telah lulus secara penuh. Dokumen ini dapat diandalkan sebagai baseline SPPB Enterprise. Silakan konfirmasi untuk perbaikan atau deployment/pembersihan tahap selanjutnya.

## Credentials
- Rahasia (seperti kredensial database) hanya boleh berada di dalam file `.env`. Rotasi kredensial merupakan tindakan eksternal wajib yang harus dilakukan oleh pemilik sistem.
- Filament: v5.6.8

## In-progress files
Tidak ada. FASE 6 telah selesai dan *clean*.

## Ringkasan FASE 7 / ADR-007 (Sedang Berjalan)

Memulai implementasi arsitektur pertukaran data dan dokumen sesuai keputusan ADR-007:
1. **Pembangunan Skema Data Asinkron**: Berhasil mengeksekusi *Blueprint Build* untuk 6 model baru pendukung ekosistem ini:
   - `DocumentTemplate`: Skema pengelolaan layout.
   - `DocumentGeneration`: Skema jejak pencetakan PDF.
   - `DocumentPage` & `DocumentValidation`: Skema pendukung validasi QR per halaman.
   - `DataImport` & `DataExport`: Skema pusat antrean unggah/unduh data bulk.
2. **Standardisasi Integritas & Audit (Quality Gate)**:
   - Menyempurnakan relasi antar-tabel dan batas karakter unik indeks khusus (64-char limit pada MariaDB/MySQL).
   - Seluruh model dan *Factories* bawaan Blueprint telah lolos perbaikan `phpstan` dengan *0 errors*.
3. **Pembangunan Service Layer Dokumen (Selesai)**:
   - `DocumentGenerationService` & `ProcessDocumentGenerationJob`: Antrean pembuatan PDF aman, asinkron, dan sanggup menggantikan versi lama (supersede) tanpa menimpa *file* fisik.
   - `DocumentVerificationController` & `DocumentVerificationService`: Endpoint publik *Rate-limited* (tanpa login) untuk pengecekan validitas QR per halaman beserta pencatatan *IP Address* / *Fingerprint* setiap kali diverifikasi.
4. **Pembangunan Service Layer Pertukaran Data (Selesai)**:
   - `DataImportService` & `ProcessDataImportJob`: Logika antrean *import* dua tahap (Unggah -> Validasi -> *Commit* / Eksekusi), yang mencegah data kotor merusak tabel utama.
   - `DataExportService` & `GenerateDataExportJob`: Generator laporan besar terstruktur (*background-job*) agar sistem utama tidak membeku (*timeout*) saat menarik ribuan baris data ke CSV/Excel/PDF.
   - `PurgeExpiredDocumentExportJob`: Fitur keamanan otomatis (Scheduled Task/Job) untuk menghapus berkas-berkas ekspor & PDF lama yang masa retensinya/kadaluwarsanya sudah lewat, guna mencegah kebocoran data terabaikan.
5. **Status Saat Ini**: FASE 7 telah **Selesai 100%**. Seluruh infrastruktur inti aplikasi beserta modul pelaporan, verifikasi, otorisasi dinamis, rekam log audit, dan *business logic* *Goods Release* sudah terbangun & tervalidasi. 

## Kesimpulan Blueprint
Pembangunan core engine, data exchange, role authorization, UI master data dan workflow berdasarkan `Antigravity 2.0 Master Blueprint` (Fase 1-7) secara fungsional di tataran sistem telah sukses diimplementasikan.
