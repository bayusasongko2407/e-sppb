# ADR 001: Legacy FPPB Data Alignment

## Konteks
Aplikasi E-SPPB Enterprise akan menggantikan aplikasi legacy PHPRunner (FPPB). Pengguna memberikan instruksi eksplisit untuk menganalisis skema `db_fppb` dan menyesuaikan Master Blueprint E-SPPB (`draft.yaml`) agar selaras dengan data legacy tanpa menghilangkan integritas arsitektur Enterprise yang telah dirancang. 

Analisis terhadap `db_fppb` menunjukkan beberapa atribut data legacy yang tidak terpetakan secara implisit di skema baru:
1. `tbl_fppb_requests.fppb_hash`: Legacy memakai hash string untuk validasi, sedangkan arsitektur baru memakai UUID.
2. `tbl_fppb_requests.surat_jalan_number`: Di legacy, SJ number dapat diketik langsung di header SPPB sebelum form SJ sebenarnya dibuat, atau merujuk ke `tbl_manual_sj`.
3. `tbl_fppb_items.is_from_master` & `barcode_confirmed`: Flag eksplisit ini digunakan di UI legacy. Di arsitektur baru, jika `asset_id` tidak null maka barang dari master, namun UI legacy mungkin mengandalkan flag boolean ini untuk state.

## Keputusan
Untuk mengakomodasi migrasi dan kompabilitas data dari `db_fppb` ke `e_sppb_enterprise` tanpa merusak konsep Clean Architecture, diputuskan:
1. **SppbHeader**: Menambahkan `legacy_fppb_hash` (char:64 nullable) dan `legacy_sj_number` (varchar:50 nullable) agar data historis dapat disimpan utuh. 
2. **SppbDetail**: Menambahkan `is_from_master` (boolean default:false) dan `barcode_confirmed` (boolean default:false) untuk mendukung state UI legacy jika dibutuhkan saat masa transisi.
3. **GoodsRelease**: Konsep `tbl_manual_sj` akan disatukan dengan `GoodsRelease` yang ditandai dengan flag `is_manual = true` atau `status = 'MANUAL'`.
4. Relasi `employee_id` di `tbl_users` dipetakan ke `nik` di model `User` yang baru (sudah sesuai).

## Status
Disetujui dan diterapkan pada Blueprint (14 Juli 2026).

## Konsekuensi
Perubahan ini tidak melanggar batasan `Plant` sebagai top organization. Schema `draft.yaml` akan di-update, dan Blueprint akan diregenerasi untuk menerapkan atribut-atribut baru ini.
