# Dokumentasi API v1 - E-SPPB Enterprise

Dokumentasi Resmi RESTful API v1 E-SPPB Enterprise (`https://e-sppb.engiboard.web.id/api/v1`).

---

## 1. Informasi Umum & Otentikasi

* **Base URL:** `https://e-sppb.engiboard.web.id/api/v1`
* **Format Request/Response:** `application/json`
* **Metode Otentikasi:**
  1. **Bearer Token (Sanctum):** Sertakan header `Authorization: Bearer <token>` pada setiap permintaan endpoint privat.
  2. **Session Cookie:** Untuk integrasi Web SPA.

---

## 2. Authentication API (`/api/v1/auth`)

### 2.1 Login User
* **Endpoint:** `POST /api/v1/auth/login`
* **Headers:** `Content-Type: application/json`
* **Request Body:**
```json
{
  "login": "budi@example.com", // Dapat menggunakan Email atau NIK
  "password": "password123"
}
```
* **Response Status 200 OK:**
```json
{
  "status": "success",
  "message": "Login berhasil",
  "data": {
    "token": "1|token_sanctum_string...",
    "user": {
      "id": 1,
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "nik": "12345678",
      "plant_id": 1,
      "department_id": 2,
      "roles": ["pemohon"]
    }
  }
}
```

### 2.2 Profile User saat ini (`me`)
* **Endpoint:** `GET /api/v1/auth/me`
* **Headers:** `Authorization: Bearer <token>`
* **Response Status 200 OK:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "nik": "12345678",
    "plant": { "id": 1, "name": "Pabrik Sentul" },
    "department": { "id": 2, "name": "Departemen Logistik" },
    "permissions": ["view_any_sppbheader", "create_sppbheader"]
  }
}
```

### 2.3 Refresh Token
* **Endpoint:** `POST /api/v1/auth/refresh`
* **Request Body:** `{ "refresh_token": "<token>" }`

### 2.4 Logout
* **Endpoint:** `POST /api/v1/auth/logout`
* **Headers:** `Authorization: Bearer <token>`

---

## 3. SPPB API (`/api/v1/sppb`)

### 3.1 Daftar SPPB
* **Endpoint:** `GET /api/v1/sppb`
* **Query Parameters:** `page`, `per_page`, `status`, `search`
* **Response Status 200 OK:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 10,
      "uuid": "a1b2c3d4-...",
      "document_number": "SPPB/2026/07/0001",
      "status": "APPROVED",
      "request_date": "2026-07-30",
      "requester": {
        "id": 1,
        "name": "Budi Santoso",
        "nik": "12345678"
      },
      "needed_name": "Budi Santoso",
      "destination_location": {
        "id": 2,
        "name": "Gudang Utama B"
      },
      "department": {
        "id": 2,
        "name": "Logistik"
      }
    }
  ]
}
```

### 3.2 Detail SPPB (Mendukung ID, UUID, dan No. Dokumen)
* **Endpoint:** `GET /api/v1/sppb/{id_or_uuid_or_docnum}`
* **Catatan Transisi Otomatis:** Jika dokumen berstatus `WAITING_VERIFICATION_BAT` dan diakses oleh Penyetuju BAT (BAT Approver), sistem akan otomatis memperbarui status menjadi `PROCESS_VERIFICATION_BAT` dan merekam status log audit `BAT_OPENED`.
* **Response Status 200 OK:**
```json
{
  "status": "success",
  "data": {
    "id": 10,
    "uuid": "a1b2c3d4-...",
    "document_number": "SPPB/2026/07/0001",
    "status": "APPROVED",
    "request_date": "2026-07-30",
    "date_needed": "2026-08-01",
    "requester": {
      "id": 1,
      "name": "Budi Santoso",
      "nik": "12345678"
    },
    "origin_location": { "id": 1, "name": "Gudang Pabrik A" },
    "destination_location": { "id": 2, "name": "Gudang Utama B" },
    "items": [
      {
        "id": 101,
        "item_asset_name": "Pompa Hidrolik Industrial 500W",
        "quantity": 2.0,
        "unit": "Pcs",
        "remarks": "Pengantian suku cadang rusak"
      }
    ]
  }
}
```

### 3.3 Buat SPPB Baru
* **Endpoint:** `POST /api/v1/sppb`
* **Request Body:**
```json
{
  "request_date": "2026-07-30",
  "date_needed": "2026-08-02",
  "origin_location_id": 1,
  "destination_location_id": 2,
  "purpose": "Permintaan pengiriman alat maintenance",
  "details": [
    {
      "item_id": 5,
      "quantity": 2,
      "unit_id": 1,
      "remarks": "Segera dikirim"
    }
  ]
}
```

### 3.4 Ajukan SPPB ke Workflow Persetujuan (`submit`)
* **Endpoint:** `POST /api/v1/sppb/{uuid}/submit`

### 3.5 Ajukan Ulang SPPB setelah Revisi/Ditolak (`resubmit`)
* **Endpoint:** `POST /api/v1/sppb/{uuid}/resubmit`
* **Response Status 200 OK:**
```json
{
  "success": true,
  "message": "SPPB berhasil diajukan ulang dan sedang diproses.",
  "data": { "command_uuid": "f9f54593-7ac6-470d-84c8-4e57c7b39c27" }
}
```

### 3.6 Batalkan Permohonan SPPB (`cancel`)
* **Endpoint:** `POST /api/v1/sppb/{uuid}/cancel`
* **Catatan Hak Akses:** Dapat dipanggil saat status `DRAFT`, `REJECTED`, atau status non-terminal lainnya oleh pemohon atau admin.
* **Request Body:**
```json
{
  "reason": "Permohonan dibatalkan karena pengajuan tidak lagi dibutuhkan."
}
```
* **Response Status 200 OK:**
```json
{
  "success": true,
  "message": "Permohonan SPPB berhasil dibatalkan."
}
```

### 3.7 Item SPPB Berpengiriman Parsial & Sisa Kuota (`releasable-items`)
* **Endpoint:** `GET /api/v1/sppb/{uuid}/releasable-items`
* **Kegunaan Frontend:** Dipanggil saat frontend membuat Surat Jalan baru untuk SPPB agar **hanya menampilkan barang yang belum lunas dikirim (`quantity_remaining > 0`)** serta mengetahui status pengiriman parsial tiap item.
* **Headers:** `Authorization: Bearer <token>`
* **Response Status 200 OK:**
```json
{
  "success": true,
  "message": "Daftar sisa kuota barang SPPB berhasil ditampilkan.",
  "data": {
    "sppb_header_id": 10,
    "sppb_uuid": "a1b2c3d4-...",
    "document_number": "SPPB/2026/07/0001",
    "header_status": "RELEASE_IN_PROGRESS",
    "items": [
      {
        "sppb_detail_id": 101,
        "line_no": 1,
        "item_id": 5,
        "asset_id": null,
        "item_asset_name": "Semen Tiga Roda 50kg",
        "reference_code": "SMN-50KG",
        "unit_id": 48,
        "unit_name": "Sak",
        "quantity_requested": 100.0,
        "quantity_already_released": 40.0,
        "quantity_remaining": 60.0,
        "delivery_status": "PARTIALLY_DELIVERED",
        "delivery_status_label": "Pengiriman Sebagian",
        "is_fully_released": false
      }
    ],
    "releasable_items": [
      {
        "sppb_detail_id": 101,
        "line_no": 1,
        "item_id": 5,
        "asset_id": null,
        "item_asset_name": "Semen Tiga Roda 50kg",
        "reference_code": "SMN-50KG",
        "unit_id": 48,
        "unit_name": "Sak",
        "quantity_requested": 100.0,
        "quantity_already_released": 40.0,
        "quantity_remaining": 60.0,
        "delivery_status": "PARTIALLY_DELIVERED",
        "delivery_status_label": "Pengiriman Sebagian",
        "is_fully_released": false
      }
    ]
  },
  "timestamp": "2026-08-11T12:00:00+07:00"
}
```
* **Keterangan Status Pengiriman Item (`delivery_status`):**
  - `PENDING` (`Belum Dikirim`): Total `quantity_already_released` = 0.
  - `PARTIALLY_DELIVERED` (`Pengiriman Sebagian`): `quantity_already_released` > 0 dan `quantity_remaining` > 0.
  - `DELIVERED` (`Pengiriman Penuh`): `quantity_remaining` = 0 (`is_fully_released` = true).

---

## 4. Goods Release / Surat Jalan API (`/api/v1/goods-releases`)

### 4.1 Daftar Surat Jalan
* **Endpoint:** `GET /api/v1/goods-releases`

### 4.2 Detail Surat Jalan (Mendukung ID, UUID, No. Surat Jalan)
* **Endpoint:** `GET /api/v1/goods-releases/{id_or_uuid}`
* **Response Status 200 OK:**
```json
{
  "status": "success",
  "data": {
    "id": 5,
    "uuid": "e9b23f80-...",
    "release_number": "SJ-20260730-0001",
    "manual_release_number": "SJ-MANUAL-001",
    "status": "DELIVERED",
    "delivery_date": "2026-07-30",
    "driver_name": "Agus Driver",
    "vehicle_number": "B 1234 CD",
    "expedition_name": "Internal Delivery",
    "recipient_name": "Siti Rahma",
    "received_at": "2026-07-30T13:30:00+07:00",
    "recipient_signature": "data:image/png;base64,iVBORw0KG...",
    "receiving_notes": "Barang diterima lengkap dan sesuai segel utuh",
    "sppb_references": [
      {
        "document_number": "SPPB/2026/07/0001",
        "request_date": "2026-07-30",
        "requester_name": "Budi Santoso",
        "status": "COMPLETED"
      }
    ]
  }
}
```

### 4.3 Konfirmasi Penerimaan Barang / Surat Jalan (`receive`)
* **Endpoint:** `POST /api/v1/goods-releases/{id_or_uuid}/receive`
* **Alias:** `PATCH /api/v1/goods-releases/{id_or_uuid}/status`
* **Public / Cors Support:** Dapat diakses via API Publik dengan throttle rate-limiting.
* **Request Body:**
```json
{
  "status": "DELIVERED",
  "recipient_name": "Siti Rahma",
  "received_at": "2026-07-30T13:30:00Z",
  "recipient_signature": "data:image/png;base64,iVBORw0KG...",
  "receiving_notes": "Barang telah diterima lengkap dan sesuai segel utuh",
  "notes": "Barang telah diterima lengkap dan sesuai segel utuh"
}
```
* **Response Status 200 OK:**
```json
{
  "status": "success",
  "success": true,
  "message": "Surat Jalan berhasil dikonfirmasi diterima",
  "data": {
    "id": 5,
    "uuid": "e9b23f80-...",
    "release_number": "SJ-20260730-0001",
    "status": "DELIVERED",
    "recipient_name": "Siti Rahma",
    "recipient_signature": "data:image/png;base64,iVBORw0KG...",
    "receiving_notes": "Barang telah diterima lengkap dan sesuai segel utuh",
    "received_at": "2026-07-30T13:30:00+07:00",
    "updated_at": "2026-07-30T13:30:00+07:00"
  }
}
```

---

## 5. Verifikasi QR Code Dokumen (`/api/v1/verify`)

### 5.1 Verifikasi Dokumen via Hash / Token Terenkripsi
* **Endpoint:** `GET /api/v1/verify/document/{hash}`
* **Endpoint:** `POST /api/v1/verify/document` (Request Body: `{ "hash": "<token_sha256>" }`)
* **Public CORS Enabled.**
* **Response Status 200 OK:**
```json
{
  "status": "success",
  "document_type": "goods_release",
  "data": {
    "release_number": "SJ-20260730-0001",
    "status": "DELIVERED",
    "created_by": "Budi Santoso",
    "driver_name": "Agus Driver",
    "vehicle_number": "B 1234 CD",
    "recipient_name": "Siti Rahma",
    "received_at": "2026-07-30T13:30:00+07:00",
    "receiving_notes": "Barang telah diterima lengkap dan sesuai segel utuh",
    "recipient_signature": "data:image/png;base64,iVBORw0KG...",
    "requester": {
      "id": 1,
      "name": "Budi Santoso",
      "nik": "12345678"
    },
    "destination_location": {
      "id": 2,
      "code": "PLT2",
      "name": "Gudang Utama B"
    },
    "verification_hash": "a5d8b9f0e1c2...",
    "verified_at": "2026-07-30T13:45:00+07:00"
  }
}
```

---

## 6. Workflow & Persetujuan API (`/api/v1/workflow`)

### 6.1 Daftar Tugas Persetujuan (Approval Tasks)
* **Endpoint:** `GET /api/v1/workflow/tasks`
* **Response Status 200 OK:**
```json
{
  "success": true,
  "message": "Daftar tugas persetujuan berhasil ditampilkan.",
  "data": [
    {
      "id": 15,
      "workflow_instance_step_id": 42,
      "approver_id": 5,
      "status": "PENDING",
      "workflow_instance_step": {
        "id": 42,
        "sequence": 1,
        "name": "Persetujuan Manager",
        "workflow_instance": {
          "id": 10,
          "uuid": "a1b2c3d4-...",
          "sppb_header": {
            "document_number": "SPPB/2026/07/0001",
            "needed_name": "Budi Santoso"
          }
        }
      }
    }
  ],
  "meta": { "current_page": 1, "per_page": 15, "total": 1, "last_page": 1 },
  "timestamp": "2026-08-11T09:28:00+07:00"
}
```

### 6.2 Setujui Step Persetujuan Dokumen
* **Endpoint:** `POST /api/v1/workflow/steps/{stepId}/approve`
* **Request Body:**
```json
{
  "remarks": "Disetujui untuk proses pengiriman",
  "require_plant_manager": false
}
```
* **Response Status 200 OK:**
```json
{
  "success": true,
  "message": "Persetujuan SPPB berhasil diproses.",
  "timestamp": "2026-08-11T09:28:00+07:00"
}
```

### 6.3 Tolak Dokumen SPPB
* **Endpoint:** `POST /api/v1/workflow/steps/{stepId}/reject`
* **Request Body:**
```json
{
  "remarks": "Jumlah barang melebihi kuota stok pabrik"
}
```
* **Response Status 200 OK:**
```json
{
  "success": true,
  "message": "Penolakan SPPB berhasil diproses.",
  "timestamp": "2026-08-11T09:28:00+07:00"
}
```

### 6.4 Minta Revisi Dokumen SPPB
* **Endpoint:** `POST /api/v1/workflow/steps/{stepId}/revision`
* **Request Body:**
```json
{
  "remarks": "Harap perbaiki deskripsi dan jumlah spesifikasi barang."
}
```
* **Response Status 200 OK:**
```json
{
  "success": true,
  "message": "Permintaan revisi SPPB berhasil diproses.",
  "timestamp": "2026-08-11T09:28:00+07:00"
}
```

### 6.5 Response Error Otorisasi (403 Forbidden Format JSON)
* Jika pengguna tidak memiliki hak akses / bukan penanggung jawab step:
```json
{
  "success": false,
  "message": "Anda tidak memiliki hak akses untuk melakukan tindakan ini.",
  "data": null,
  "errors": null,
  "timestamp": "2026-08-11T09:28:00+07:00"
}
```

---

## 7. System Health & Sandbox Diagnostics (`/api/v1/health` & `/api/v1/public/sandbox-info`)

### 7.1 Diagnostic Real-time System
* **Endpoint:** `GET /api/v1/health`
* **Response Status 200 OK:**
```json
{
  "status": "healthy",
  "environment": "production",
  "checks": {
    "database": "connected",
    "cache": "ok",
    "storage": "writable"
  },
  "timestamp": "2026-07-30T13:47:00+07:00"
}
```

---

## 8. Kode Error HTTP

| Status Code | Keterangan |
|---|---|
| `200 OK` | Permintaan berhasil diproses |
| `201 Created` | Resource baru berhasil dibuat |
| `400 Bad Request` | Form/Payload request tidak valid |
| `401 Unauthorized` | Token Bearer/Session tidak valid atau kadaluarsa |
| `403 Forbidden` | Tidak memiliki hak akses (Role/Permission) |
| `404 Not Found` | Data atau Dokumen tidak ditemukan |
| `422 Unprocessable` | Error validasi field request |
| `500 Internal Server Error` | Terjadi kesalahan pada server |
