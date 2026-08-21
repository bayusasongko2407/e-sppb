# E-SPPB Enterprise — API Documentation v1

> **Versi dokumen:** 1.0.1 — Disinkronisasi dari source code aktual<br>
> **Base URL:** `https://e-sppb.engiboard.web.id/api/v1`<br>
> **Content-Type:** `application/json`<br>
> **Interactive Docs:** https://e-sppb.engiboard.web.id/docs/api<br>

---

## ⚠️ Hal Kritis untuk Frontend Developer

Sebelum menggunakan API ini, perhatikan hal-hal berikut yang **berbeda dari konvensi umum**:

1. **Token login menggunakan dua token terpisah:** `access_token` dan `refresh_token` (bukan satu `token`)
2. **Refresh token dikirim di body request** sebagai `{ "refresh_token": "..." }`, bukan via header
3. **Email field menerima NIK atau email** untuk login
4. **Semua response dibungkus** dengan wrapper `{ "success": true, "message": "...", "data": {...}, "timestamp": "..." }`
5. **SPPB lookup fleksibel**: UUID, nomor dokumen, atau ID numerik semua valid di `{uuid}` path parameter
6. **Surat Jalan lookup fleksibel**: UUID, `release_number`, `manual_release_number`, atau `verification_hash`
7. **Items master data TIDAK paginated** — menggunakan `limit` (bukan `per_page`), max default 50
8. **Approve/reject/revision bersifat async** (queued) — response 200 bukan berarti status sudah berubah
9. **Status SPPB stats menggunakan `WAITING_APPROVAL`** (bukan `PENDING`) untuk field pending
10. **`addDetail` hanya mendukung `item_id`** (dari catalog item) — aset tidak bisa ditambah via API

---

## Daftar Isi

1. [Autentikasi](#1-autentikasi)
2. [SPPB](#2-sppb)
3. [Detail Item SPPB](#3-detail-item-sppb)
4. [Lampiran SPPB](#4-lampiran-sppb)
5. [Surat Jalan (Goods Release)](#5-surat-jalan-goods-release)
6. [Workflow Persetujuan](#6-workflow-persetujuan)
7. [Master Data](#7-master-data)
8. [Sistem](#8-sistem)
9. [Referensi Status](#9-referensi-status)

---

## 1. Autentikasi

### Header Autentikasi
```
Authorization: Bearer {access_token}
```

---

### 1.1 Login
`POST /auth/login`

Field `email` menerima **email address ATAU NIK** pengguna.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```
atau
```json
{
  "email": "NIK1234567",
  "password": "password123"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "access_token": "1|abc123...",
    "refresh_token": "2|xyz789...",
    "user": {
      "id": 1,
      "nik": "NIK1234567",
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "plant_id": 1,
      "department_id": 2,
      "roles": ["staff"],
      "permissions": ["create sppb", "view sppb"],
      "position": {
        "id": 5,
        "code": "ENG-01",
        "name": "Staff Engineering"
      },
      "plant": {
        "id": 1,
        "code": "SDA",
        "name": "Plant Sidoarjo",
        "address": "Jl. Raya Sidoarjo..."
      },
      "department": {
        "id": 2,
        "code": "ENG",
        "name": "Engineering"
      }
    }
  }
}
```

**Response 422:** Validasi gagal (email/NIK atau password salah).
**Response 403:** Akun tidak aktif.

> ⚠️ **Simpan kedua token**: `access_token` untuk Authorization header, `refresh_token` untuk mendapatkan access token baru.

---

### 1.2 Refresh Access Token
`POST /auth/refresh`

`refresh_token` dikirim di **body**, bukan header. Hanya `access_token` yang diperbarui; `refresh_token` tetap sama (reused).

**Request Body:**
```json
{
  "refresh_token": "2|xyz789..."
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Token berhasil diperbarui.",
  "data": {
    "access_token": "3|newtoken...",
    "refresh_token": "2|xyz789..."
  }
}
```

**Response 401:** Refresh token tidak valid atau sudah digunakan.

---

### 1.3 Logout
`POST /auth/logout` *(Membutuhkan autentikasi)*

Menghapus **semua token** milik user yang sedang login.

**Response 200:**
```json
{ "success": true, "message": "Logout berhasil." }
```

---

### 1.4 Data User Aktif
`GET /auth/me` *(Membutuhkan autentikasi)*

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nik": "NIK1234567",
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "plant_id": 1,
    "department_id": 2,
    "roles": ["staff"],
    "permissions": ["create sppb", "view sppb"],
    "position": { "id": 5, "code": "ENG-01", "name": "Staff Engineering" },
    "plant": { "id": 1, "code": "SDA", "name": "Plant Sidoarjo", "address": "..." },
    "department": { "id": 2, "code": "ENG", "name": "Engineering" }
  }
}
```

---

### 1.5 Login (Session-based)
`POST /auth/session/login`

Untuk aplikasi web yang menggunakan cookie session.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "remember": false
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Session login berhasil.",
  "data": {
    "session_id": "abc123sessionid",
    "user": { ... }
  }
}
```

---

### 1.6 Logout (Session)
`POST /auth/session/logout`

**Response 200:** `{ "success": true, "message": "Session logout berhasil." }`

---

### 1.7 Data User Aktif (Session)
`GET /auth/session/me`

Response sama seperti `/auth/me`.

---

## 2. SPPB

### 2.1 Daftar SPPB
`GET /sppb`

**Query Parameters:**

| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `status` | string | - | Filter status (lihat Referensi Status) |
| `plant_id` | integer | - | Filter berdasarkan plant |
| `sort` | string | `created_at` | Field untuk sorting |
| `direction` | string | `desc` | Arah sort: `asc` / `desc` |
| `per_page` | integer | 15 | Jumlah per halaman |

**Response 200:**
```json
{
  "success": true,
  "message": "Daftar SPPB berhasil ditampilkan.",
  "data": [
    {
      "id": 30,
      "uuid": "019fef10-ba9e-70fb-9b9e-e4f24c6bb4ff",
      "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
      "status": "RELEASE_IN_PROGRESS",
      "needed_name": "Percobaan tahap 2",
      "request_date": "2026-08-11",
      "date_needed": "2026-08-12",
      "is_urgent": false,
      "plant_id": 455,
      "department_id": 190,
      "requester_id": 188,
      "plant": { "id": 455, "code": "SDA", "name": "Plant A", "address": "..." },
      "department": { "id": 190, "code": "ENG", "name": "Engineering" },
      "requester": { "id": 188, "name": "John Doe" },
      "origin_location": { "id": 80, "name": "Gudang Trosobo A25" },
      "destination_location": { "id": 79, "name": "Workshop Engineering" },
      "details": [ ... ],
      "sppb_status_logs": [ ... ]
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 30,
    "last_page": 2
  },
  "links": {
    "first": "https://e-sppb.engiboard.web.id/api/v1/sppb?page=1",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "errors": null,
  "timestamp": "2026-08-11T14:00:00+07:00",
  "request_id": "uuid-string"
}
```

> ⚠️ Response daftar SPPB menggunakan struktur `{ data: [], meta: {}, links: {} }` — berbeda dari endpoint lain yang menggunakan `{ data: {} }`.

---

### 2.2 Buat SPPB
`POST /sppb`

**Request Body:**
```json
{
  "plant_id": 1,
  "department_id": 2,
  "origin_location_id": 10,
  "destination_location_id": 11,
  "needed_name": "Perbaikan Mesin Line 3",
  "request_date": "2026-08-11",
  "date_needed": "2026-08-13",
  "purpose": "Untuk keperluan maintenance rutin",
  "is_urgent": false
}
```

> ⚠️ **SPPB dibuat tanpa item.** Item ditambahkan secara terpisah via `POST /sppb/{uuid}/details`.

| Field | Wajib | Keterangan |
|-------|:-----:|------------|
| `plant_id` | Ya | ID plant |
| `department_id` | Ya | ID departemen |
| `origin_location_id` | Ya | ID lokasi asal |
| `destination_location_id` | Ya | ID lokasi tujuan |
| `needed_name` | Ya | Nama/keperluan penggunaan |
| `request_date` | Ya | `YYYY-MM-DD` |
| `date_needed` | Ya | `YYYY-MM-DD` |
| `purpose` | Tidak | Tujuan/keterangan tambahan |
| `is_urgent` | Tidak | Boolean, default `false` |

**Response 201:**
```json
{
  "success": true,
  "message": "Draft SPPB berhasil dibuat.",
  "data": { ... objek SPPB ... },
  "timestamp": "..."
}
```

---

### 2.3 Detail SPPB
`GET /sppb/{uuid}`

> Path parameter `{uuid}` menerima: **UUID**, **nomor dokumen**, atau **ID numerik**.

**Response 200:**
```json
{
  "success": true,
  "message": "Detail SPPB berhasil ditampilkan.",
  "data": {
    "id": 30,
    "uuid": "019fef10-ba9e-70fb-9b9e-e4f24c6bb4ff",
    "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
    "status": "RELEASE_IN_PROGRESS",
    "needed_name": "Percobaan tahap 2",
    "request_date": "2026-08-11",
    "date_needed": "2026-08-12",
    "is_urgent": false,
    "purpose": null,
    "revision_no": 2,
    "submitted_at": "2026-08-11T04:24:57.000000Z",
    "approved_at": "2026-08-11T04:47:13.000000Z",
    "rejected_at": null,
    "cancelled_at": null,
    "completed_at": null,
    "plant": { "id": 455, "code": "SDA", "name": "Plant Sidoarjo", "address": "..." },
    "department": { "id": 190, "code": "ENG", "name": "Engineering" },
    "requester": { "id": 188, "name": "John Doe", "email": "john@example.com" },
    "origin_location": { "id": 80, "name": "Gudang Trosobo A25" },
    "destination_location": { "id": 79, "name": "Workshop Engineering" },
    "details": [
      {
        "id": 14,
        "sppb_header_id": 30,
        "line_no": 1,
        "barcode_confirmed": true,
        "item_id": 24,
        "asset_id": null,
        "reference_code": "CS-SL-002",
        "item_asset_name": "Teflon Tape 1/2 Inch",
        "quantity": "10.00",
        "remarks": null,
        "delivery_status": "FULLY_RELEASED",
        "unit": { "id": 47, "name": "Roll" },
        "item": { "id": 24, "name": "Teflon Tape 1/2 Inch", "code": "CS-SL-002" }
      }
    ],
    "sppb_status_logs": [ ... ]
  },
  "timestamp": "..."
}
```

> ⚠️ **Catatan khusus**: Saat endpoint ini diakses oleh **BAT Approver** dan status SPPB adalah `WAITING_VERIFICATION_BAT`, sistem otomatis memperbarui status menjadi `PROCESS_VERIFICATION_BAT`.

---

### 2.4 Update SPPB
`PUT /sppb/{uuid}`

**Request Body:**
```json
{
  "needed_name": "Nama Kebutuhan Baru",
  "request_date": "2026-08-12",
  "date_needed": "2026-08-14",
  "purpose": "Tujuan diperbarui",
  "is_urgent": true,
  "origin_location_id": 10,
  "destination_location_id": 11
}
```

**Response 200:** `{ "success": true, "message": "Draft SPPB berhasil diperbarui.", "data": {...} }`

---

### 2.5 Hapus SPPB
`DELETE /sppb/{uuid}`

**Response 200:** `{ "success": true, "message": "Draft SPPB berhasil dihapus." }`

---

### 2.6 Submit SPPB
`POST /sppb/{uuid}/submit`

Submit bersifat **async** (diproses via queue). Response berisi `command_uuid` untuk tracking.

**Response 200:**
```json
{
  "success": true,
  "message": "SPPB berhasil disubmit dan sedang diproses.",
  "data": {
    "command_uuid": "uuid-untuk-tracking"
  }
}
```

> ⚠️ Status SPPB **tidak langsung berubah** saat response diterima. Lakukan polling `GET /sppb/{uuid}` untuk memantau perubahan status.

---

### 2.7 Resubmit SPPB
`POST /sppb/{uuid}/resubmit`

Setelah SPPB dikembalikan untuk revisi. Juga bersifat **async**.

**Response 200:**
```json
{
  "success": true,
  "message": "SPPB berhasil diajukan ulang dan sedang diproses.",
  "data": { "command_uuid": "..." }
}
```

---

### 2.8 Batalkan SPPB
`POST /sppb/{uuid}/cancel`

**Request Body:**
```json
{ "reason": "Kebutuhan sudah tidak diperlukan." }
```

> Validasi `reason`: **wajib**, string, min **10** karakter, max 1000 karakter.

**Response 200:** `{ "success": true, "message": "Permohonan SPPB berhasil dibatalkan." }`

---

### 2.9 Statistik SPPB
`GET /sppb/stats`

**Response 200:**
```json
{
  "success": true,
  "message": "Statistik SPPB berhasil ditampilkan.",
  "data": {
    "total": 50,
    "draft": 5,
    "waiting_approval": 8,
    "approved": 10,
    "release_in_progress": 7,
    "completed": 15,
    "revised": 2,
    "rejected": 1,
    "cancelled": 2
  },
  "timestamp": "..."
}
```

> ⚠️ Key untuk SPPB pending adalah **`waiting_approval`** (bukan `pending`). Status aktual di database adalah `WAITING_APPROVAL`.

---

### 2.10 Riwayat Status SPPB
`GET /sppb/{uuid}/status-logs`

**Response 200:**
```json
{
  "success": true,
  "message": "Histori status SPPB berhasil ditampilkan.",
  "data": [
    {
      "id": 1,
      "sppb_header_id": 30,
      "action": "SUBMITTED",
      "from_status": "DRAFT",
      "to_status": "WAITING_APPROVAL",
      "remarks": "SPPB diajukan untuk persetujuan.",
      "logged_at": "2026-08-11T04:24:57.000000Z",
      "actor": {
        "id": 188,
        "name": "John Doe",
        "positions": [ { "position": { "id": 5, "code": "ENG-01", "name": "Staff Engineering" } } ],
        "roles": [ { "name": "staff" } ]
      },
      "workflow_instance_step": null
    }
  ]
}
```

---

### 2.11 Item yang Dapat Dirilis (Releasable Items)
`GET /sppb/{uuid}/releasable-items`

Digunakan untuk menentukan **apakah tombol "Kirim Barang" perlu ditampilkan** dan item mana saja yang masih bisa dikirim.

**Response 200:**
```json
{
  "success": true,
  "message": "Daftar sisa kuota barang SPPB berhasil ditampilkan.",
  "data": {
    "sppb_header_id": 30,
    "sppb_uuid": "019fef10-ba9e-70fb-9b9e-e4f24c6bb4ff",
    "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
    "header_status": "RELEASE_IN_PROGRESS",
    "items": [
      {
        "sppb_detail_id": 14,
        "line_no": 1,
        "item_id": 24,
        "asset_id": null,
        "item_asset_name": "Teflon Tape 1/2 Inch",
        "reference_code": "CS-SL-002",
        "unit_id": 47,
        "unit_name": "Roll",
        "quantity_requested": 10.0,
        "quantity_already_released": 10.0,
        "quantity_remaining": 0.0,
        "delivery_status": "DELIVERED",
        "delivery_status_label": "Pengiriman Penuh",
        "is_fully_released": true
      }
    ],
    "releasable_items": []
  },
  "timestamp": "..."
}
```

**Logika Frontend:**
- Tombol "Kirim Barang" **ditampilkan** jika `data.releasable_items` tidak kosong (ada item yang `is_fully_released: false`)
- Tombol **disembunyikan** jika `data.releasable_items` kosong (semua item sudah `is_fully_released: true`)

**Nilai `delivery_status` di endpoint ini** (berbeda dari status detail SPPB utama):

| `delivery_status` | `delivery_status_label` | `is_fully_released` | Keterangan |
|---|---|:---:|---|
| `PENDING` | Belum Dikirim | `false` | Belum ada Surat Jalan |
| `PARTIALLY_DELIVERED` | Pengiriman Sebagian | `false` | Sebagian sudah ada SJ aktif |
| `DELIVERED` | Pengiriman Penuh | `true` | Semua sudah ada di SJ aktif |

---

### 2.7 Generate QR Code SPPB
`GET /sppb/{uuid}/qr-code` *(Membutuhkan autentikasi & permission `view_sppbheader`)*

Menghasilkan QR Code terenkripsi resmi untuk lembar dokumen SPPB, yang kompatibel dengan endpoint verifikasi dokumen (`/api/v1/verify/document`).

**Query Parameters:**

| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `format` | string | `json` | Pilihan: `json` (mengembalikan JSON payload & base64 image data URI) atau `svg` (mengembalikan stream gambar SVG mentah) |

**Response 200 (JSON):**
```json
{
  "success": true,
  "message": "QR Code dokumen SPPB berhasil dibuat.",
  "data": {
    "sppb_id": 30,
    "sppb_uuid": "019fef10-ba9e-70fb-9b9e-e4f24c6bb4ff",
    "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
    "status": "APPROVED",
    "verification_type": "LARAVEL_CRYPT_AES256",
    "qr_payload": "eyJpdiI6Ilp4TlVrc2R2cTRsVGt3c2EiLCJ2YWx1ZSI6Ilk4aXBYL3Z...",
    "verification_url": "https://e-sppb.engiboard.web.id/verify/document?hash=eyJpdiI6...",
    "api_verification_url": "https://e-sppb.engiboard.web.id/api/v1/verify/document",
    "public_verification_url": "https://e-sppb.engiboard.web.id/api/v1/public/sppb/verify/SPPB%2FSJA%20SPJ%2FENG%2F2026%2F08%2F00002",
    "qr_image_base64": "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIi...",
    "generated_at": "2026-08-21T10:30:00+07:00"
  },
  "timestamp": "2026-08-21T10:30:00+07:00",
  "request_id": "9b12a831-..."
}
```

* **`verification_url`**: Link verifikasi web resmi (dapat langsung dibuka di browser ponsel/komputer untuk melihat sertifikat keabsahan dokumen).
* **`api_verification_url`**: Endpoint API verifikasi (digunakan untuk request `POST` dari aplikasi Frontend/Mobile Scanner).
* **`public_verification_url`**: Endpoint verifikasi publik instan berbasis kode SPPB.

**Response 200 (SVG):**
- Request: `GET /sppb/{uuid}/qr-code?format=svg`
- `Content-Type: image/svg+xml`
- Output: `<svg xmlns="http://www.w3.org/2000/svg" ...>...</svg>`

---

## 3. Detail Item SPPB

### 3.1 Daftar Detail Item
`GET /sppb/{uuid}/details`

**Response 200:**
```json
{
  "success": true,
  "message": "Daftar Detail SPPB berhasil ditampilkan.",
  "data": [
    {
      "id": 14,
      "sppb_header_id": 30,
      "line_no": 1,
      "barcode_confirmed": false,
      "item_id": 24,
      "asset_id": null,
      "reference_code": "CS-SL-002",
      "item_asset_name": "Teflon Tape 1/2 Inch",
      "quantity": "10.00",
      "remarks": null,
      "delivery_status": "FULLY_RELEASED",
      "unit": { "id": 47, "name": "Roll" },
      "item": { "id": 24, "name": "Teflon Tape 1/2 Inch", "code": "CS-SL-002", "unit": { "id": 47, "name": "Roll" } }
    }
  ]
}
```

---

### 3.2 Tambah Detail Item
`POST /sppb/{uuid}/details`

> ⚠️ **Hanya mendukung item dari catalog (`item_id`)**. Item berupa aset tidak dapat ditambahkan via API.

**Request Body:**
```json
{
  "item_id": 24,
  "quantity": 10,
  "remarks": "Keterangan opsional"
}
```

| Field | Wajib | Keterangan |
|-------|:-----:|------------|
| `item_id` | Ya | ID item dari master catalog (wajib, harus ada di tabel `items`) |
| `quantity` | Ya | Min 0.01 |
| `remarks` | Tidak | Keterangan tambahan, maks 1000 karakter |

> Unit dan nama item otomatis diambil dari data item catalog.

**Response 201:** `{ "success": true, "message": "Detail SPPB berhasil ditambahkan.", "data": {...} }`

---

### 3.3 Update Detail Item
`PUT /sppb/{uuid}/details/{detailId}`

**Request Body:**
```json
{
  "item_id": 25,
  "quantity": 5,
  "remarks": "Diperbarui"
}
```

| Field | Wajib | Keterangan |
|-------|:-----:|------------|
| `item_id` | Ya | ID item yang menggantikan item lama |
| `quantity` | Ya | Min 0.01 |
| `remarks` | Tidak | Keterangan tambahan |

**Response 200:** `{ "success": true, "message": "Detail SPPB berhasil diperbarui.", "data": {...} }`

---

### 3.4 Hapus Detail Item
`DELETE /sppb/{uuid}/details/{detailId}`

**Response 200:** `{ "success": true, "message": "Detail SPPB berhasil dihapus." }`

---

## 4. Lampiran SPPB

### 4.1 Daftar Lampiran
`GET /sppb/{uuid}/attachments`

**Response 200:**
```json
[
  {
    "id": 1,
    "uuid": "abc-uuid...",
    "original_name": "purchase_order.pdf",
    "mime_type": "application/pdf",
    "size": 204800,
    "description": "PO Referensi",
    "uploaded_by": { "id": 188, "name": "John Doe" },
    "created_at": "2026-08-11T04:30:00.000000Z"
  }
]
```

---

### 4.2 Upload Lampiran
`POST /sppb/{uuid}/attachments`
`Content-Type: multipart/form-data`

| Field | Wajib | Keterangan |
|-------|:-----:|------------|
| `file` | Ya | Tipe: `pdf,jpg,jpeg,png,doc,docx,xls,xlsx`. Maks **10MB** |
| `description` | Tidak | Deskripsi lampiran |

**Response 201:** Objek lampiran yang dibuat.

---

### 4.3 Hapus Lampiran
`DELETE /sppb/{uuid}/attachments/{attachmentUuid}`

**Response 200:** `{ "message": "Lampiran berhasil dihapus." }`

---

## 5. Surat Jalan (Goods Release)

### 5.1 Daftar Surat Jalan
`GET /goods-releases`

| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `status` | string | - | Filter status |
| `sppb_header_id` | integer | - | Filter berdasarkan ID SPPB |
| `sort` | string | `created_at` | Field sorting |
| `direction` | string | `desc` | `asc` / `desc` |
| `per_page` | integer | 15 | Jumlah per halaman |

**Response 200:**
```json
{
  "success": true,
  "message": "Daftar pelepasan barang berhasil ditampilkan.",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 10,
    "last_page": 1
  }
}
```

---

### 5.2 Detail Surat Jalan
`GET /goods-releases/{uuid}`

> Path parameter `{uuid}` menerima: **UUID**, **release_number** (e.g. `SJ-20260811-0001`), **manual_release_number**, atau **verification_hash**.

**Response 200:**
```json
{
  "success": true,
  "message": "Detail pelepasan barang berhasil ditampilkan.",
  "data": {
    "id": 12,
    "uuid": "84cb8924-3eeb-4383-954f-72399073efb9",
    "release_number": "SJ-20260811-0001",
    "manual_release_number": null,
    "sppb_header_id": 30,
    "release_sequence": 1,
    "is_manual": false,
    "sender_name": "Gudang Trosobo A25",
    "sender_address": "Jl. Kencana Trosobo No.A-25...",
    "receiver_name": "Workshop Engineering",
    "receiver_address": "Jl. Raya Gilang No.159...",
    "driver_name": "Ferry",
    "vehicle_number": "W1239WE",
    "expedition_name": "Tetuko",
    "delivery_date": "2026-08-11",
    "status": "RELEASED",
    "recipient_name": null,
    "recipient_signature": null,
    "receiving_notes": null,
    "received_at": null,
    "verification_hash": "46da0826...",
    "notes": null,
    "sppb_header": {
      "id": 30,
      "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
      "plant": { ... },
      "department": { ... },
      "requester": { ... },
      "origin_location": { ... },
      "destination_location": { ... }
    },
    "created_by": { "id": 189, "name": "Warehouse Staff" },
    "sender_user": null,
    "receiver_user": null,
    "goods_release_items": [
      {
        "id": 7,
        "goods_release_id": 12,
        "sppb_detail_id": 14,
        "quantity_requested": "10.00",
        "quantity_released": "10.00",
        "quantity_received": "0.00",
        "condition_on_release": "Baik",
        "condition_on_receipt": null,
        "is_checked": false,
        "sppb_detail": {
          "id": 14,
          "item_asset_name": "Teflon Tape 1/2 Inch",
          "item": { "id": 24, "name": "Teflon Tape 1/2 Inch" },
          "unit": { "id": 47, "name": "Roll" }
        }
      }
    ]
  }
}
```

---

### 5.3 Buat Surat Jalan untuk SPPB
`POST /sppb/{uuid}/goods-releases`

Item yang masih memiliki sisa kuota (`quantity_remaining > 0`) **otomatis dimasukkan** ke Surat Jalan — tidak perlu mengirim daftar item secara manual.

**Request Body:**
```json
{
  "driver_name": "Ferry",
  "recipient_name": "Ferry",
  "vehicle_number": "W1239WE",
  "recipient_vehicle_number": "W1239WE",
  "expedition_name": "Tetuko",
  "delivery_date": "2026-08-11",
  "notes": "Harap segera diterima."
}
```

| Field | Wajib | Keterangan |
|-------|:-----:|------------|
| `driver_name` | Tidak | Nama pengemudi (alternatif: `recipient_name`) |
| `recipient_name` | Tidak | Nama pengemudi (alias dari `driver_name`) |
| `vehicle_number` | Tidak | Nomor kendaraan (alternatif: `recipient_vehicle_number`) |
| `recipient_vehicle_number` | Tidak | Alias dari `vehicle_number` |
| `expedition_name` | Tidak | Nama ekspedisi |
| `delivery_date` | Tidak | `YYYY-MM-DD`, default: tanggal hari ini |
| `notes` | Tidak | Catatan, maks 1000 karakter |

**Response 201:**
```json
{
  "success": true,
  "message": "Surat jalan pelepasan barang berhasil dibuat.",
  "data": { ... objek Surat Jalan ... }
}
```

**Response 422:**
```json
{ "success": false, "message": "Semua barang dalam SPPB ini sudah dilepaskan." }
```

---

### 5.4 Update Status Surat Jalan
`PATCH /goods-releases/{uuid}/status`

**Request Body:**
```json
{
  "status": "IN_TRANSIT",
  "notes": "Barang sedang dalam perjalanan."
}
```

**Transisi Status yang Diizinkan:**

| Status Sekarang | Boleh ke |
|----------------|----------|
| `DRAFT` | `RELEASED`, `CANCELLED` |
| `RELEASED` | `IN_TRANSIT`, `CANCELLED` |
| `IN_TRANSIT` | `CANCELLED` |

**Response 200:** Objek Surat Jalan yang diperbarui.
**Response 422:** `{ "message": "Tidak dapat mengubah status dari X ke Y." }`

---

### 5.5 Konfirmasi Penerimaan Barang
`POST /goods-releases/{uuid}/receive`

**Request Body:**
```json
{
  "received_by_name": "Andi Prasetyo",
  "recipient_name": "Andi Prasetyo",
  "receiving_notes": "Barang diterima dalam kondisi baik.",
  "notes": "Catatan tambahan",
  "recipient_signature": "base64_string",
  "signature": "base64_string",
  "received_by_id": 190,
  "received_at": "2026-08-11T14:00:00"
}
```

> Semua field **opsional**. `received_by_name` dan `recipient_name` adalah alias yang saling menggantikan. `recipient_signature` dan `signature` juga alias.

**Response 200:**
```json
{
  "status": "success",
  "success": true,
  "message": "Surat Jalan berhasil dikonfirmasi diterima",
  "data": {
    "id": 12,
    "uuid": "84cb8924-...",
    "release_number": "SJ-20260811-0001",
    "status": "DELIVERED",
    "notes": null,
    "recipient_name": "Andi Prasetyo",
    "recipient_signature": "base64...",
    "receiving_notes": "Barang diterima dalam kondisi baik.",
    "received_at": "2026-08-11T14:00:00.000000Z",
    "updated_at": "2026-08-11T14:00:00.000000Z"
  }
}
```

---

## 6. Workflow Persetujuan

### 6.1 Daftar Tugas Persetujuan
`GET /workflow/tasks`

Mengembalikan tugas yang menunggu tindakan dari **user yang sedang login** (termasuk delegasi).

| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `per_page` | integer | 15 | Jumlah per halaman |

**Response 200:**
```json
{
  "success": true,
  "message": "Daftar tugas persetujuan berhasil ditampilkan.",
  "data": [
    {
      "id": 1,
      "workflow_instance_step_id": 42,
      "approver_id": 189,
      "status": "PENDING",
      "workflow_instance_step": {
        "id": 42,
        "workflow_instance_id": 10,
        "sequence": 1,
        "status": "PENDING",
        "workflow_instance": {
          "id": 10,
          "uuid": "instance-uuid...",
          "sppb_header": {
            "id": 30,
            "uuid": "019fef10-...",
            "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
            "needed_name": "Percobaan tahap 2",
            "is_urgent": false,
            "status": "WAITING_APPROVAL",
            "plant": { ... },
            "department": { ... },
            "requester": { ... },
            "origin_location": { ... },
            "destination_location": { ... },
            "details": [ ... ]
          }
        }
      }
    }
  ],
  "meta": { "current_page": 1, "per_page": 15, "total": 1, "last_page": 1 },
  "timestamp": "..."
}
```

---

### 6.2 Detail Workflow Instance
`GET /workflow/instances/{uuid}`

**Response 200:**
```json
{
  "success": true,
  "message": "Detail alur kerja berhasil ditampilkan.",
  "data": {
    "id": 10,
    "uuid": "instance-uuid...",
    "status": "IN_PROGRESS",
    "sppb_header": { "id": 30, "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002", "plant": {...}, "department": {...}, "requester": {...} },
    "workflow_instance_steps": [
      {
        "id": 42,
        "sequence": 1,
        "status": "APPROVED",
        "step_approvers": [
          {
            "approver": { "id": 189, "name": "Manager Engineering" },
            "status": "APPROVED"
          }
        ]
      }
    ]
  },
  "timestamp": "..."
}
```

---

### 6.3 Setujui (Approve)
`POST /workflow/steps/{stepId}/approve`

> ⚠️ **Async** — keputusan diproses via queue. Response 200 bukan berarti status sudah berubah.

**Request Body:**
```json
{
  "remarks": "Disetujui, silahkan diproses.",
  "note": "Alternatif field untuk remarks"
}
```

`remarks` dan `note` adalah alias — keduanya bisa digunakan.

**Response 200:**
```json
{
  "success": true,
  "message": "Persetujuan SPPB berhasil diproses.",
  "timestamp": "..."
}
```

---

### 6.4 Tolak (Reject)
`POST /workflow/steps/{stepId}/reject`

**Request Body:** *(`remarks` wajib, min 5 karakter)*
```json
{ "remarks": "Kuantitas tidak sesuai dengan kebutuhan aktual." }
```

**Response 200:**
```json
{ "success": true, "message": "Penolakan SPPB berhasil diproses.", "timestamp": "..." }
```

---

### 6.5 Minta Revisi
`POST /workflow/steps/{stepId}/revision`

**Request Body:** *(`remarks` wajib, min 5 karakter)*
```json
{ "remarks": "Mohon lengkapi spesifikasi item nomor 2." }
```

**Response 200:**
```json
{ "success": true, "message": "Permintaan revisi SPPB berhasil diproses.", "timestamp": "..." }
```

---

## 7. Master Data

### 7.1 Daftar Plant
`GET /sppb/master/plants`

Mengembalikan plant yang **aktif** (`is_active: true`).

**Response 200:**
```json
{
  "success": true,
  "message": "Daftar Pabrik berhasil ditampilkan.",
  "data": [
    { "id": 1, "code": "SDA", "name": "Plant Sidoarjo", "address": "...", "is_active": true }
  ]
}
```

---

### 7.2 Daftar Departemen
`GET /sppb/master/departments`

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `plant_id` | integer | Filter berdasarkan plant (opsional) |

**Response 200:**
```json
{
  "success": true,
  "message": "Daftar Departemen berhasil ditampilkan.",
  "data": [
    { "id": 1, "code": "ENG", "name": "Engineering", "plant_id": 1 }
  ]
}
```

---

### 7.3 Daftar Lokasi
`GET /sppb/master/locations`

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `plant_id` | integer | Filter berdasarkan plant (opsional) |

**Response 200:**
```json
{
  "success": true,
  "message": "Daftar Lokasi berhasil ditampilkan.",
  "data": [
    { "id": 1, "name": "Gudang Trosobo A25", "address": "Pergudangan Kencana...", "plant_id": 1, "is_active": true }
  ]
}
```

---

### 7.4 Daftar Item / Barang (Catalog)
`GET /sppb/master/items`

> ⚠️ Endpoint ini **TIDAK paginated**. Menggunakan parameter `limit` (bukan `per_page`).

| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `search` | string | - | Cari berdasarkan `name` atau `code` |
| `limit` | integer | **50** | Maks item yang dikembalikan |

**Response 200:**
```json
{
  "success": true,
  "message": "Daftar Barang berhasil ditampilkan.",
  "data": [
    {
      "id": 24,
      "code": "CS-SL-002",
      "name": "Teflon Tape 1/2 Inch",
      "is_active": true,
      "unit": { "id": 47, "name": "Roll" }
    }
  ]
}
```

---

## 8. Sistem

### 8.1 Health Check
`GET /health` *(Tanpa autentikasi)*

**Response 200:**
```json
{
  "success": true,
  "service": "E-SPPB Backend Enterprise API",
  "version": "1.0.0",
  "environment": "production",
  "base_url": "https://e-sppb.engiboard.web.id/api/v1",
  "system_status": {
    "database": "OK",
    "qr_decoder": "OPERATIONAL",
    "latency_ms": 12.5
  },
  "metrics": {
    "recent_24h_validations": 5
  },
  "timestamp": "2026-08-11T14:00:00+07:00"
}
```

---

### 8.2 Verifikasi Dokumen
`GET /verify/document/{hash}` *(Tanpa autentikasi)*

Verifikasi keaslian Surat Jalan via QR code hash (terenkripsi Laravel Crypt).

**Response 200:** Data dokumen jika hash valid.
**Response 404:** Hash tidak ditemukan.

---

## 9. Referensi Status

### Status SPPB (Field `status`)

| Value di DB/API | Label UI | Keterangan |
|----------------|----------|------------|
| `DRAFT` | Draft | Baru dibuat, belum diajukan |
| `WAITING_APPROVAL` | Menunggu Persetujuan | Sudah diajukan, menunggu approval |
| `APPROVED` | Disetujui | Sudah disetujui, siap dikirim |
| `RELEASE_IN_PROGRESS` | Proses Pengiriman | Ada Surat Jalan aktif, belum selesai |
| `COMPLETED` | Selesai | Semua barang sudah diterima |
| `REVISED` | Dikembalikan | Diminta revisi oleh approver |
| `REJECTED` | Ditolak | Ditolak approver |
| `CANCELLED` | Dibatalkan | Dibatalkan pemohon |
| `WAITING_VERIFICATION_BAT` | Menunggu Verifikasi BAT | Khusus alur BAT |
| `PROCESS_VERIFICATION_BAT` | Proses Verifikasi BAT | Sedang diverifikasi BAT |

---

### Status Surat Jalan (Field `status`)

| Value | Label | Keterangan |
|-------|-------|------------|
| `DRAFT` | Draft | Belum final |
| `RELEASED` | Diterbitkan | Final, siap dikirim |
| `IN_TRANSIT` | Dalam Perjalanan | Sedang dalam pengiriman |
| `DELIVERED` | Diterima | Sudah dikonfirmasi diterima |
| `CANCELLED` | Dibatalkan | Dibatalkan |

---

### Status Pengiriman Item SPPB (Field `delivery_status` di `details`)

Ini adalah status di objek `details` dalam response `GET /sppb/{uuid}`:

| Value | Label | Keterangan |
|-------|-------|------------|
| `PENDING` | Belum Dikirim | Belum ada Surat Jalan |
| `PARTIALLY_RELEASED` | Pengiriman Sebagian | Sebagian sudah ada di SJ aktif |
| `FULLY_RELEASED` | Pengiriman Penuh | Semua sudah ada di SJ aktif |
| `PARTIALLY_DELIVERED` | Diterima Sebagian | Sebagian sudah dikonfirmasi |
| `DELIVERED` | Diterima / Terkirim | Semua sudah dikonfirmasi diterima |

---

### Status Workflow Task (Field `status` di `WorkflowStepApprover`)

| Value | Keterangan |
|-------|------------|
| `PENDING` | Menunggu tindakan approver |
| `APPROVED` | Disetujui |
| `REJECTED` | Ditolak |
| `REVISION` | Dikembalikan untuk revisi |

---

## Error Response Format

Semua error mengikuti format:
```json
{
  "success": false,
  "message": "Deskripsi error.",
  "errors": {
    "field_name": ["Pesan validasi untuk field ini."]
  }
}
```

## HTTP Status Codes

| Code | Keterangan |
|------|------------|
| `200` | OK |
| `201` | Created |
| `401` | Unauthenticated |
| `403` | Forbidden |
| `404` | Not Found |
| `405` | Method Not Allowed (e.g., GET ke endpoint POST-only) |
| `422` | Validasi gagal / transisi status tidak valid |
| `500` | Internal Server Error |

---

## Checklist Penyesuaian Frontend

Gunakan checklist ini untuk memverifikasi implementasi frontend sudah sesuai:

- [ ] Login menyimpan **dua token terpisah**: `data.access_token` dan `data.refresh_token`
- [ ] Refresh token mengirim `{ "refresh_token": "..." }` di **body** (bukan header)
- [ ] Profil user mencakup field `nik` dan `position`
- [ ] Daftar SPPB membaca dari `data` array dan pagination dari `meta` (bukan `data.data`)
- [ ] SPPB dibuat **tanpa item** — item ditambah via endpoint terpisah
- [ ] Tambah item SPPB hanya menggunakan `item_id` (catalog) — bukan `asset_id`
- [ ] Submit/Approve bersifat **async** — lakukan polling untuk cek status terbaru
- [ ] Filter SPPB pending menggunakan value `WAITING_APPROVAL` (bukan `PENDING`)
- [ ] Statistik menggunakan key `waiting_approval` (bukan `pending`)
- [ ] Items master data menggunakan parameter `limit` (bukan `per_page`)
- [ ] Tombol "Kirim Barang": tampil jika `releasable_items` tidak kosong
- [ ] Konfirmasi terima Surat Jalan: gunakan `recipient_name` atau `received_by_name`
- [ ] Filter Surat Jalan menggunakan `sppb_header_id` (ID integer, bukan UUID)
- [ ] Lookup Surat Jalan mendukung `release_number` sebagai path param (bukan hanya UUID)
