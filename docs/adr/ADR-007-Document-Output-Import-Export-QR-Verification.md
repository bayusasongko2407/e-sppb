# ADR-007: Document Output, Import/Export, and Per-Page QR Verification

- Status: DITERIMA
- Tanggal: 2026-07-15
- Pemilik keputusan: Pemilik Proyek dan Arsitektur Perangkat Lunak
- Terkait: Antigravity 2.0 Master Blueprint, keamanan dokumen, pelaporan, audit, dan integrasi data

## Konteks

E-SPPB Enterprise memerlukan kemampuan untuk mencetak dokumen resmi ke PDF, mengekspor data, mengimpor data terkontrol, dan memverifikasi keaslian dokumen melalui QR pada setiap halaman. Blueprint sebelumnya telah menyebut listener `GenerateApprovedSppbPdf`, `GenerateGoodsReleasePdf`, serta queue `documents` dan `reports`, tetapi belum mendefinisikan kontrak arsitektur, siklus hidup artefak, keamanan verifikasi publik, skema data, izin, API, maupun quality gate.

Tanpa kontrak tersebut, implementasi berisiko menghasilkan PDF yang dapat ditimpa tanpa jejak, QR yang hanya menjadi tautan dekoratif, ekspor yang melewati scope Plant/Policy, atau impor yang mengubah transaksi dan histori secara tidak terkendali.

## Keputusan

### 1. Batas Modul

Tambahkan bounded context `Document` untuk template, pembuatan PDF, penyimpanan artefak, checksum, QR per halaman, validasi, dan pencabutan dokumen. Tambahkan bounded context `DataExchange` untuk impor dan ekspor data terkontrol. Filament dan REST API wajib memanggil Application Service/use case yang sama.

Service minimum:

- `DocumentTemplateService`
- `DocumentGenerationService`
- `DocumentVerificationService`
- `DataImportService`
- `DataExportService`

Implementasi renderer PDF harus berada di balik contract/adapter agar blueprint tidak terikat pada satu library PDF.

### 2. Dokumen PDF Resmi

- Dokumen resmi minimum adalah SPPB yang telah `APPROVED` dan Surat Jalan/Goods Release yang memenuhi status cetak sesuai aturan domain.
- Pratinjau sebelum status resmi boleh dibuat, tetapi wajib diberi watermark `DRAF - TIDAK SAH` dan tidak boleh menghasilkan status verifikasi `VALID`.
- Pembuatan PDF dijalankan secara asinkron pada queue `documents`, idempoten melalui `command_uuid`, dan dipublikasikan setelah commit.
- PDF dibuat dari snapshot data dan versi template yang tercatat. Perubahan master atau template setelah pembuatan tidak boleh mengubah artefak lama.
- Regenerasi menghasilkan generation baru. Generation lama tetap disimpan untuk audit dan diberi status `SUPERSEDED`, `REVOKED`, atau `EXPIRED`; file tidak ditimpa diam-diam.
- Artefak disimpan pada private storage dengan MIME yang divalidasi, nama acak, ukuran file, checksum SHA-256, waktu retensi, dan akses unduh melalui Policy.

### 3. QR pada Setiap Halaman

- Setiap halaman PDF resmi wajib memiliki QR unik yang terikat pada generation, nomor halaman, total halaman, checksum artefak, dan checksum halaman hasil render.
- QR mengarah ke identifier publik acak/bertanda tangan. Nilai yang dapat dipakai untuk lookup tidak boleh berupa ID database berurutan; token validasi disimpan dalam bentuk hash jika menggunakan token acak.
- Verifikasi membuktikan bahwa halaman tersebut diterbitkan sebagai bagian dari generation tertentu, artefak tersimpan masih utuh, dan status generation masih berlaku. Sistem tidak boleh mengklaim bahwa QR sendiri dapat mendeteksi manipulasi fisik bila QR dipindahkan ke halaman lain.
- Halaman verifikasi publik bersifat read-only, rate-limited, tidak memerlukan login, dan hanya menampilkan data minimum: jenis dan nomor dokumen, Plant penerbit, waktu terbit, status validasi, nomor/total halaman, serta fingerprint checksum pendek.
- Data pemohon, daftar barang, alamat, lampiran, token internal, dan informasi sensitif lain tidak boleh ditampilkan pada endpoint publik.
- Status publik minimum: `VALID`, `SUPERSEDED`, `REVOKED`, `EXPIRED`, dan `NOT_FOUND`. Percobaan verifikasi dicatat tanpa menyimpan rahasia atau data pribadi berlebihan.

### 4. Impor Data

- Impor hanya tersedia untuk tipe data yang masuk allowlist dan mempunyai schema/template berversi. Impor umum tidak boleh menjalankan SQL, menentukan nama tabel/kolom bebas, atau melewati Service Layer.
- Baseline impor mencakup master data yang disetujui. Impor SPPB, keputusan approval, status workflow, activity log, dan histori Surat Jalan dilarang melalui fitur impor umum; migrasi historis harus memakai pipeline migrasi khusus dan ADR terpisah.
- Alur impor wajib dua tahap: unggah/validasi lalu commit. Validasi menghasilkan ringkasan baris valid/gagal dan laporan kesalahan sebelum mutasi dilakukan.
- Commit impor dijalankan melalui queue, transaction per chunk yang terkontrol, idempotensi, Policy, audit, checksum file sumber, serta strategi kegagalan yang tidak meninggalkan status ambigu.
- File impor disimpan secara private, divalidasi terhadap MIME/ukuran/ekstensi, dipindai sesuai kebijakan upload, dan dihapus berdasarkan retensi.

### 5. Ekspor Data

- Ekspor hanya boleh memakai query object/dataset yang diizinkan dan selalu menerapkan Policy serta scope Plant/department yang sama dengan UI/API sumbernya.
- Pengguna tidak dapat memilih SQL, tabel, relasi, atau field sensitif secara bebas. Kolom ekspor menggunakan allowlist dan masking bila diperlukan.
- Ekspor besar dijalankan pada queue `reports`, mempunyai status yang dapat dipantau, checksum, masa kedaluwarsa, audit, dan private download berotorisasi.
- Format awal yang direncanakan adalah CSV/XLSX untuk data tabular dan PDF untuk dokumen/laporan terformat; library final dipilih saat implementasi melalui adapter.

### 6. Data Model yang Direncanakan

Perubahan `draft.yaml` dilakukan pada fase implementasi terpisah setelah review skema. Entitas minimum yang disetujui secara konseptual:

- `document_templates`: kode, jenis dokumen, versi, renderer/template, checksum, periode efektif, dan status aktif.
- `document_generations`: UUID, sumber dokumen, template/version snapshot, `command_uuid`, status, pembuat, lokasi file private, MIME, ukuran, checksum, jumlah halaman, waktu terbit/kedaluwarsa/pencabutan, dan informasi kegagalan.
- `document_pages`: generation, nomor halaman, UUID publik, checksum halaman, hash token/signature validation, dan unique `(document_generation_id, page_number)`.
- `document_validations`: generation/page, hasil validasi, actor internal nullable, correlation ID, fingerprint request yang diminimalkan, dan waktu verifikasi.
- `data_imports`: UUID, tipe/schema version, file/checksum, pemohon, status, jumlah baris, laporan validasi, `command_uuid`, waktu proses, dan informasi kegagalan.
- `data_exports`: UUID, tipe/dataset, filter dan kolom yang telah disanitasi, pemohon, status, file/checksum, `command_uuid`, waktu kedaluwarsa, dan informasi kegagalan.

Semua PK/FK mengikuti aturan `BIGINT UNSIGNED`, UUID diberi unique index, status memakai backed enum, dan relasi/scope harus tetap berbasis Plant tanpa `Company`.

### 7. Queue, Event, Job, dan Permission

Job minimum:

- `GenerateSppbPdfJob`
- `GenerateGoodsReleasePdfJob`
- `ProcessDataImportJob`
- `GenerateDataExportJob`
- `PurgeExpiredDocumentExportJob`

QR per halaman dibuat sebagai bagian atomik dari pipeline generation; pemecahan menjadi job per halaman hanya diperbolehkan bila orchestration menjamin generation tidak berstatus `READY` sebelum seluruh halaman selesai.

Event minimum:

- `DocumentGenerationRequested`, `DocumentGenerated`, `DocumentGenerationFailed`
- `DocumentVerified`
- `DataImportQueued`, `DataImportCompleted`, `DataImportFailed`
- `DataExportQueued`, `DataExportCompleted`, `DataExportFailed`

Permission minimum:

- `document.print`
- `document.download`
- `document.verify`
- `document.manage_template`
- `import.manage`
- `export.manage`
- `report.export`

Endpoint API yang direncanakan:

- `POST /api/v1/documents/{type}/{id}/generate`
- `GET /api/v1/documents/generations/{uuid}`
- `GET /api/v1/documents/generations/{uuid}/download`
- `GET /verify/document/{verification_uuid}/page/{page}`
- `POST /api/v1/imports`
- `GET /api/v1/imports/{uuid}`
- `POST /api/v1/imports/{uuid}/commit`
- `POST /api/v1/exports`
- `GET /api/v1/exports/{uuid}`
- `GET /api/v1/exports/{uuid}/download`

Command asinkron mengembalikan HTTP `202 Accepted` dan menyediakan endpoint polling status.

## Konsekuensi

### Positif

- Dokumen resmi mempunyai provenance, versi template, checksum, status, dan jejak audit yang jelas.
- QR setiap halaman dapat memverifikasi bahwa halaman diterbitkan sebagai anggota generation yang sah.
- Impor dan ekspor tidak menjadi jalan pintas untuk melewati Policy, scope organisasi, atau aturan domain.
- Filament, API, dan integrasi masa depan menggunakan kontrak use case yang sama.

### Biaya dan Risiko

- Menambah tabel, storage, queue workload, retensi file, monitoring, dan skenario kegagalan parsial.
- Template PDF dan renderer harus diuji deterministik; perubahan library dapat mengubah checksum/rendering.
- Endpoint publik memerlukan rate limiting, respons minim data, pemantauan abuse, dan kebijakan retensi log.
- QR tidak dapat menjamin isi fisik halaman tidak dimanipulasi jika QR asli ditempelkan pada halaman palsu; verifikasi dibatasi pada provenance dan integritas artefak digital tersimpan.

## Dampak Implementasi

- Database: perubahan `draft.yaml`, migration, model, constraint, dan index dilakukan pada fase terpisah; tidak dilakukan oleh ADR ini.
- API/Flutter: kontrak status asinkron dan verifikasi harus stabil serta dapat digunakan klien web maupun Flutter.
- Keamanan: perlu Policy, permission, private signed download, rate limiting publik, validasi upload, dan audit.
- Operasional: worker queue `documents`/`reports`, storage capacity, cleanup scheduler, backup, dan health check harus diperbarui.
- Testing: wajib mencakup checksum, QR per halaman, revocation/supersession, IDOR, rate limit, retry/idempotensi, file validation, scope ekspor, dan atomicity impor.

## Batas Keputusan Saat Ini

ADR ini menyetujui arsitektur dan revisi blueprint saja. ADR ini tidak mengizinkan perubahan `draft.yaml`, migration, dependency, source code, worker, route, atau storage configuration sebelum fase implementasi disetujui secara eksplisit.
