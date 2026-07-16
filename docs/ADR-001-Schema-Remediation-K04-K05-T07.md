# ADR 001: Schema Remediation untuk K-04, K-05, dan T-07

## Status
Diusulkan (Blokir untuk R1)

## Konteks
Audit E-SPPB Enterprise pada 14 Juli 2026 mengidentifikasi tiga temuan kritis (K-04, K-05, T-07) terkait integritas schema database:
1. **K-04**: Terdapat kolom foreign key yang duplikat atau salah penamaan (misalnya `correlation_id` diperlakukan sebagai relasi `Correlation` yang tidak ada).
2. **K-05**: Kurangnya constraint foreign key pada level database (hanya mengandalkan Eloquent) serta tidak adanya penetapan tindakan `ON DELETE` / `ON UPDATE` yang mengamankan data referensial dari anomali.
3. **T-07**: Kurangnya check constraint pada detail SPPB (seperti validasi kuantitas tidak boleh negatif) pada tingkat database, sehingga rawan dimanipulasi di luar aplikasi.

Aturan `frozen-rules.md` melarang perubahan schema secara langsung tanpa tinjauan analisis dampak dan pembaruan `draft.yaml`.

## Keputusan yang Diusulkan
Kita perlu mengeksekusi perbaikan integritas schema dengan melakukan sinkronisasi menyeluruh pada FASE R1:
1. Menghapus migrasi/kolom yang merujuk pada `Correlation` (yang seharusnya UUID string), serta menormalisasi penamaan kolom FK lain yang melanggar standar `*_id`.
2. Menambahkan `foreign()` definitions dengan klausa `onDelete('cascade')` atau `onDelete('restrict')` pada migration sesuai risiko bisnis.
3. Menambahkan Check Constraint menggunakan raw SQL pada database untuk kolom numerik krusial seperti kuantitas, agar kuantitas selalu >= 0.

## Analisis Dampak Wajib
- **Dampak database**: Migrasi harus dijalankan ulang (`migrate:fresh`). Constraint akan menolak data buruk dari level engine (MariaDB 10.11+).
- **Dampak API**: API yang mencoba menghapus parent dengan anak secara paksa mungkin akan mendapat HTTP 500 Constraint Violation jika tidak menggunakan endpoint penghapusan berantai yang tepat.
- **Dampak Flutter**: Tidak ada (kecuali response code berubah saat constraint dilanggar).
- **Dampak keamanan**: Peningkatan pertahanan data secara _defense in depth_ terhadap anomali di luar aplikasi.
- **Dampak workflow**: Workflow yang menolak / membatalkan SPPB tidak akan lagi bisa meninggalkan orphan records.
- **Dampak audit/logging**: Tidak ada.
- **Dampak queue/retry**: Jika Job mencoba insert data parsial yang melanggar constraint, Job akan gagal dan di-retry.
- **Dampak UI Filament**: Filament Resource mungkin gagal menghapus jika terkait parent constraint (`restrict`). Opsi "Delete" harus ditangani dengan elegan (menangkap exception atau menyembunyikan aksi Delete).
- **Dampak testing/deployment**: Test suite perlu disesuaikan dengan aturan strict database. Deployment memerlukan downtime singkat untuk migrate.

## Kriteria R1
Setelah ADR ini disetujui, langkah berikutnya adalah mengupdate `draft.yaml` dan memvalidasi `blueprint build` tanpa mengubah fungsionalitas lain.
