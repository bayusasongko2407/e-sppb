# ADR-002: Schema Revision — Hasil Review Database 14 Juli 2026

**Status**: DITERIMA  
**Tanggal**: 2026-07-14  
**Pengaju**: Pemilik Proyek (via review langsung)

---

## Konteks

Setelah FASE 1 selesai dan database pertama kali dibuat, pemilik proyek melakukan review menyeluruh terhadap struktur tabel dan memberikan konfirmasi atas setiap keputusan perubahan. ADR ini merekam seluruh keputusan tersebut sebagai baseline kanonik yang mengikat.

---

## Keputusan

### 1. TABLE `assets` — Restrukturisasi Konsep

| Field | Keputusan | Alasan |
|-------|-----------|--------|
| `item_id` | ❌ Dihapus | Aset adalah entitas mandiri, bukan turunan item |
| `serial_number` | ❌ Dihapus | Barcode sudah cukup sebagai identifier unik aset |
| `plant_id` | ✅ Dipertahankan, **nullable** | Scoping plant tetap diperlukan tapi bersifat opsional |
| `location_id` | ✅ Dipertahankan sebagai FK nullable | Tetap terhubung ke tabel `locations` |
| `asset_location_name` | ✅ **Ditambahkan** (varchar 255, nullable) | Snapshot denormalisasi nama lokasi dari sistem eksternal |
| `asset_location_address` | ✅ **Ditambahkan** (text, nullable) | Snapshot denormalisasi alamat lokasi |

**Perilaku sistem**: Saat `location_id` dipilih/disimpan, sistem otomatis menyalin `name` dan `address` dari tabel `locations` ke kolom snapshot. Ini memungkinkan data lokasi dari sistem eksternal tetap tersimpan meskipun relasi FK tidak selalu tersedia.

---

### 2. TABLE `items` — Klarifikasi Kategori

| Field | Keputusan | Alasan |
|-------|-----------|--------|
| `item_type` | ❌ Dihapus, diganti `item_category` | `item_type` membingungkan karena ada juga `type` di aset |
| `item_category` | ✅ **Ditambahkan** (varchar 50, nullable) | Kategori spesifik item: CONSUMABLE, EQUIPMENT, MATERIAL, SPARE_PART, dll. |

**Catatan**: `items` tidak lagi memiliki relasi `hasMany` ke `assets`. Kedua entitas berdiri sendiri.

---

### 3. TABLE `attachments` — Penyederhanaan Scope

| Field | Keputusan | Alasan |
|-------|-----------|--------|
| `sppb_detail_id` | ❌ Dihapus | Tampilan attachment hanya di level `sppb_header`, tidak per detail item |

---

### 4. TABLE `running_numbers` — Persiapan Format Nomor Berbasis Departemen

| Field | Keputusan | Alasan |
|-------|-----------|--------|
| `department_id` | ✅ **Ditambahkan** (FK nullable ke `departments`) | Mendukung format nomor dokumen berbasis departemen |

**Format masa depan yang direncanakan**: `SPPB/[PLANT_CODE]/[DEPT_CODE]/[YEAR]/[SEQ]`

---

### 5. TABLE `sppb_headers` — Rename Field

| Field | Keputusan | Alasan |
|-------|-----------|--------|
| `project_name` | ✅ Rename → `needed_name` | Nama lebih deskriptif sesuai fungsi field |

**Catatan UI** (bukan perubahan schema, dicatat untuk implementasi FASE 5):
- `plant_id`: dropdown sesuai hak akses user
- `department_id`: autofill berdasarkan departemen user login
- `requester_id`: autofill dari nama user yang login
- `origin_location_id`: dropdown difilter sesuai plant yang dipilih
- `destination_location_id`: semua lokasi, tidak boleh sama dengan origin

---

### 6. TABLE `sppb_details` — Restrukturisasi Signifikan

| Field | Keputusan | Alasan |
|-------|-----------|--------|
| `item_type` | ❌ Dihapus | Tidak relevan setelah pemisahan konsep item/aset |
| `barcode` | ❌ Dihapus | Digantikan oleh `reference_code` |
| `item_code` | ❌ Dihapus | Digantikan oleh `reference_code` |
| `unit_name` | ❌ Dihapus | Ambil dari relasi ke tabel `units` |
| `item_name` | ✅ Rename → `item_asset_name` | Netral untuk item maupun aset |
| `specification` | ❌ Dihapus | Digabung ke `remarks` (auto-fill dari item) |
| `approved_quantity` | ❌ Dihapus | Digantikan oleh `delivery_status` |
| `released_quantity` | ❌ Dihapus | Digantikan oleh `delivery_status` |
| `reference_code` | ✅ **Ditambahkan** (varchar 100, nullable, indexed) | Denormalisasi barcode/kode untuk tampilan UI |
| `barcode_confirmed` | ✅ Dipertahankan, **posisi digeser** setelah `line_no` | Flag penentu jenis referensi di UI |
| `delivery_status` | ✅ **Ditambahkan** (varchar 20, nullable, indexed) | Status pengiriman per detail/item |

**Nilai `delivery_status`**:
- `NULL` = belum relevan (SPPB belum approved)
- `PENDING` = menunggu pengiriman
- `IN_TRANSIT` = sedang dalam perjalanan
- `DELIVERED` = sudah diterima di tujuan

**Perilaku UI `reference_code`**:
- `barcode_confirmed = TRUE` → UI menampilkan dropdown dari tabel `assets` (pencarian berdasarkan barcode aset)
- `barcode_confirmed = FALSE` → UI menampilkan dropdown dari tabel `items` (pencarian berdasarkan kode item)
- `reference_code` terisi otomatis dari pilihan tersebut

**Perilaku `remarks`**:
- Jika item dipilih → auto-fill dari field `specification` item (dapat diedit manual)
- Jika aset dipilih → kosong (dapat diedit manual)

**Integritas data**: `item_id` dan `asset_id` tetap ada sebagai dua FK nullable. Hanya salah satu yang terisi. `reference_code` adalah representasi denormalisasi untuk kebutuhan tampilan saja.

---

## Konsekuensi

- **Positif**: Schema lebih bersih, konsep item dan aset lebih jelas terpisah, `delivery_status` lebih ekspresif daripada quantity tracking
- **Perhatian**: `goods_release_items` yang masih menyimpan `quantity_released` perlu disesuaikan atau diperjelas perannya terhadap `delivery_status` di `sppb_details` pada FASE berikutnya
- **File diperbarui**: `draft.yaml` dan `antigravity-blueprint.md` telah disesuaikan dengan keputusan ini sebelum eksekusi migrasi

---

## Referensi

- ADR-001: Legacy FPPB Data Alignment
- `technical-work/e-sppb-enterprise/draft.yaml` (versi setelah review 14 Juli 2026)
- `technical-work/e-sppb-enterprise/antigravity-blueprint.md`
