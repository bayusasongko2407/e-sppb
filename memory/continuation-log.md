## Active Checkpoint

- Updated at: 2026-07-14 Asia/Jakarta
- Phase: UI/UX Refactoring SPPB Resource (SELESAI)
- Status: Refactoring UI/UX SppbHeaderResource selesai 100%. Layout enterprise sesuai desain yang disetujui: Header 5-row, Detail Barang full width, Workflow Timeline horizontal. Quality gate lulus: PHP lint OK, Pint OK, PHPStan OK.

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

## Next Steps

Lanjutkan ke iterasi pengembangan berikutnya (FASE 6 / Peluncuran).

## Credentials
- Rahasia (seperti kredensial database) hanya boleh berada di dalam file `.env`. Rotasi kredensial merupakan tindakan eksternal wajib yang harus dilakukan oleh pemilik sistem.
- Filament: v5.6.8

## In-progress files
Tidak ada. Semua tugas FASE 5 telah selesai digabungkan ke codebase.
