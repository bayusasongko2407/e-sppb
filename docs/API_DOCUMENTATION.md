# DOKUMENTASI RESMI API & INTEGRASI E-SPPB ENTERPRISE ENGINE

> **Versi API:** v1.0.1<br>
> **Base URL:** `https://e-sppb.engiboard.web.id/api/v1`<br>
> **Interactive Docs:** `https://e-sppb.engiboard.web.id/docs/api`<br>
> **Markdown Spec:** `https://e-sppb.engiboard.web.id/docs/api.md`<br>

Dokumen ini berisi spesifikasi lengkap REST API, kontrak payload, struktur response JSON, mekanisme autentikasi, serta daftar endpoint yang digunakan oleh **E-SPPB Mobile Enterprise**, **Web Portal**, dan **Third-Party Service**.

---

## 1. STANDAR RESPON & PROTOKOL HTTP

### Base URL
- **Production Direct**: `https://e-sppb.engiboard.web.id/api`
- **API Version 1**: `https://e-sppb.engiboard.web.id/api/v1`

### Header Wajib
Seluruh permintaan ke API disarankan mengirimkan header berikut:
```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer <access_token>
```

### Pre-flight CORS
Sistem mendukung pre-flight request (`OPTIONS`) dengan header respon:
- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS`
- `Access-Control-Allow-Headers: Authorization, Content-Type, Accept, X-Requested-With, X-Request-ID`

### Format Envelope Respon Standar
- **Respon Sukses (200 / 201)**:
  ```json
  {
    "success": true,
    "message": "Deskripsi pesan sukses",
    "data": { ... },
    "timestamp": "2026-08-21T13:45:00+07:00"
  }
  ```
- **Respon Gagal / Validasi (422 / 401 / 403 / 404 / 500)**:
  ```json
  {
    "success": false,
    "message": "Pesan deskripsi kesalahan",
    "errors": {
      "field_name": ["Detail pesan validasi"]
    },
    "timestamp": "2026-08-21T13:45:00+07:00"
  }
  ```

---

## 2. MODUL AUTENTIKASI (`/api/v1/auth/*` & `/api/*`)

### A. Login API (`POST /api/v1/auth/login` atau `POST /api/login`)
Mendukung input berupa `email` (menerima alamat email atau NIK) dan `password`.

- **Request Body**:
  ```json
  {
    "email": "user@domain.com",
    "password": "secretpassword"
  }
  ```
  atau
  ```json
  {
    "email": "NIK-00123",
    "password": "secretpassword"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Login berhasil.",
    "data": {
      "access_token": "1|sanctum_token_string...",
      "refresh_token": "2|refresh_token_string...",
      "user": {
        "id": 189,
        "nik": "NIK-00123",
        "name": "Budi Santoso",
        "email": "budi@example.com",
        "plant_id": 1,
        "department_id": 2,
        "roles": ["staff"],
        "permissions": ["view_any_sppbheader", "create_sppbheader"],
        "position": { "id": 5, "code": "ENG-01", "name": "Staff Engineering" },
        "plant": { "id": 1, "code": "SDA", "name": "Plant Sidoarjo" },
        "department": { "id": 2, "code": "ENG", "name": "Engineering" }
      }
    }
  }
  ```

### B. Refresh Access Token (`POST /api/v1/auth/refresh`)
`refresh_token` dikirim di body JSON:
```json
{
  "refresh_token": "2|refresh_token_string..."
}
```

### C. Cek Sesi / Profil User (`GET /api/v1/auth/me` atau `GET /api/me`)
- **Headers**: `Authorization: Bearer <access_token>`

### D. Logout (`POST /api/v1/auth/logout` atau `POST /api/logout`)
Mencabut seluruh sesi dan access token pengguna yang sedang aktif.

---

## 3. MODUL SPPB (`/api/v1/sppb` & `/api/v1/sppb-headers`)

### A. Daftar SPPB (`GET /api/v1/sppb`)
- **Query Params**: `status`, `plant_id`, `search`, `sort`, `direction`, `per_page` (default: 15)

### B. Detail SPPB (`GET /api/v1/sppb/{uuid}`)
- Path parameter `{uuid}` menerima UUID resmi, nomor dokumen (e.g. `SPPB/SJA...`), atau ID integer.

### C. Buat Draft SPPB (`POST /api/v1/sppb`)
```json
{
  "plant_id": 1,
  "department_id": 2,
  "destination_location_id": 4,
  "origin_location_id": 1,
  "needed_name": "Kebutuhan Pemeliharaan Mesin",
  "purpose": "Penggantian komponen rutin",
  "request_date": "2026-08-21",
  "date_needed": "2026-08-22",
  "is_urgent": false,
  "remarks": "Catatan pengajuan"
}
```

### D. Update Draft SPPB (`PUT /api/v1/sppb/{uuid}`)

### E. Hapus Draft SPPB (`DELETE /api/v1/sppb/{uuid}`)

### F. Submit SPPB (`POST /api/v1/sppb/{uuid}/submit`)
Memulai alur persetujuan (*workflow approval*) secara terisolasi.

### G. Resubmit SPPB (`POST /api/v1/sppb/{uuid}/resubmit`)
Mengajukan ulang SPPB setelah direvisi atau ditolak.

### H. Batalkan SPPB (`POST /api/v1/sppb/{uuid}/cancel`)
```json
{
  "reason": "Alasan pembatalan oleh pemohon"
}
```

### I. Generate QR Code SPPB (`GET /api/v1/sppb/{uuid}/qr-code`)
Menghasilkan QR Code terenkripsi resmi dan tautan verifikasi multi-channel.
- **Query Param**: `format=json|svg`
- **Response 200 (JSON)**:
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
      "generated_at": "2026-08-21T13:45:00+07:00"
    }
  }
  ```

### J. Sisa Kuota Barang / Releasable Items (`GET /api/v1/sppb/{uuid}/releasable-items`)
Menampilkan barang yang siap diterbitkan ke Surat Jalan berikutnya.

---

## 4. DETAIL ITEM & LAMPIRAN SPPB

### A. Item Detail
- **GET** `/api/v1/sppb/{uuid}/details`: Daftar detail barang
- **POST** `/api/v1/sppb/{uuid}/details`: Tambah item dari katalog (`item_id`, `quantity`, `remarks`)
- **PUT** `/api/v1/sppb/{uuid}/details/{detailId}`: Update kuantitas / item
- **DELETE** `/api/v1/sppb/{uuid}/details/{detailId}`: Hapus item dari draft

### B. Lampiran (Attachments)
- **GET** `/api/v1/sppb/{uuid}/attachments`: Daftar lampiran
- **POST** `/api/v1/sppb/{uuid}/attachments`: Upload berkas (`multipart/form-data`, file maks 10MB)
- **DELETE** `/api/v1/sppb/{uuid}/attachments/{attachmentUuid}`: Hapus berkas lampiran

---

## 5. SURAT JALAN / PELEPASAN BARANG (`/api/v1/goods-releases`)

### A. Buat Surat Jalan dari SPPB
- **POST** `/api/v1/sppb/{uuid}/goods-releases` atau `POST /api/v1/goods-releases`
```json
{
  "driver_name": "Ahmad Pengemudi",
  "vehicle_number": "B 1234 ABC",
  "expedition_name": "Logistik Internal",
  "delivery_date": "2026-08-21",
  "notes": "Pengiriman kloter 1",
  "items": [
    {
      "sppb_detail_id": 15,
      "quantity_released": 2.0,
      "condition_on_release": "Baik"
    }
  ]
}
```

### B. Daftar Surat Jalan (`GET /api/v1/goods-releases`)
- **Filter**: `status`, `sppb_header_id`, `sort`, `direction`, `per_page`

### C. Detail Surat Jalan (`GET /api/v1/goods-releases/{uuid}`)
- Menerima UUID, Nomor Surat Jalan (`SJ-20260821-0001`), nomor manual, atau hash verifikasi.

### D. Konfirmasi Penerimaan Barang di Lapangan
- **POST** `/api/v1/goods-releases/{uuid}/receive` (Alias: `/confirm-receipt`, `/status`)
```json
{
  "recipient_name": "Siti Penerima Lapangan",
  "receiving_notes": "Barang diterima lengkap dan segel utuh",
  "recipient_signature": "data:image/png;base64,iVBORw0KGgo..."
}
```

---

## 6. WORKFLOW & APPROVAL ENGINE (`/api/v1/workflow/*`)

### A. Daftar Tugas Menunggu Persetujuan (`GET /api/v1/workflow/tasks`)
### B. Detail Instance Workflow (`GET /api/v1/workflow/instances/{uuid}`)
### C. Setujui Langkah (`POST /api/v1/workflow/steps/{stepId}/approve` atau `/api/v1/sppb/{uuid}/approve`)
```json
{
  "remarks": "Disetujui untuk diproses."
}
```
### D. Tolak Langkah (`POST /api/v1/workflow/steps/{stepId}/reject` atau `/api/v1/sppb/{uuid}/reject`)
```json
{
  "remarks": "Kuantitas melebihi batas anggaran."
}
```
### E. Minta Revisi (`POST /api/v1/workflow/steps/{stepId}/revision`)
```json
{
  "remarks": "Mohon perbaiki spesifikasi teknis barang."
}
```
### F. Delegasi Persetujuan (`/api/v1/workflow/delegations`)
- **GET**: Daftar delegasi aktif
- **POST**: Buat delegasi baru (`to_user_id`, `start_date`, `end_date`, `reason`)
- **PUT** `{id}`: Perbarui periode delegasi
- **DELETE** `{id}`: Batalkan delegasi

---

## 7. VERIFIKASI DOKUMEN & QR DECODER

### A. Unified Document Verification Endpoint (`POST /api/v1/verify/document`)
Endpoint publik berkecepatan tinggi yang secara otomatis mendekripsi payload QR AES-256 dan mencocokkan ke database Surat Jalan atau SPPB.

- **Request Body**:
  ```json
  {
    "qr_data": "<RAW_SCANNED_STRING_FROM_QR_CODE>"
  }
  ```
- **Response 200 (SPPB Valid)**:
  ```json
  {
    "status": "VALID",
    "success": true,
    "valid": true,
    "validation_id": "7f654df2-70b9-4702-8d77-6228399580b0",
    "data": {
      "document_type": "SPPB",
      "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
      "status_sppb": "DISETUJUI",
      "plant_name": "Plant Sidoarjo",
      "department_name": "Engineering",
      "requester_name": "Budi Santoso",
      "needed_name": "Kebutuhan Pemeliharaan Mesin Line 1",
      "locations": {
        "origin": "Gudang Utama",
        "destination": "Gudang Trosobo A25"
      },
      "items_summary": {
        "total_item_types": 3,
        "total_quantity_approved": 25
      },
      "approval_summary": [
        {
          "role": "Manajer Departemen",
          "status": "DISETUJUI",
          "approved_at": "2026-08-11 10:15",
          "approver_name": "Agus Hendrawan",
          "approver_nik": "NIK-882109",
          "approver": {
            "id": 189,
            "name": "Agus Hendrawan",
            "nik": "NIK-882109"
          }
        }
      ],
      "decrypted_from_qr": true,
      "verified_at": "2026-08-21T13:45:00+07:00"
    }
  }
  ```

### B. Web Verification Page (`GET /verify/document?hash={payload}`)
Halaman sertifikat digital publik yang dapat dibuka langsung pada browser pengguna tanpa perlu login.

---

## 8. MASTER DATA, BRANDING & HEALTH CHECK

### A. Master Data
- `GET /api/v1/sppb/master/plants`: Daftar pabrik aktif
- `GET /api/v1/sppb/master/departments`: Daftar departemen (`plant_id` opsional)
- `GET /api/v1/sppb/master/locations`: Daftar lokasi asal/tujuan
- `GET /api/v1/sppb/master/items`: Katalog barang (parameter `limit`, default: 50)

### B. Branding & Logo Pengaturan
- `GET /api/v1/branding` / `GET /api/v1/public/branding`: Ambil logo publik & styling
- `POST /api/v1/settings/branding`: Upload logo (`logo_app`, `logo_pdf`, `logo_sidebar`)
- `DELETE /api/v1/settings/branding/logos/{type}`: Hapus logo kustom

### C. System Health & Diagnostic (`GET /api/v1/health`)
- **Response (200 OK)**:
  ```json
  {
    "status": "ok",
    "success": true,
    "service": "E-SPPB Backend Enterprise API",
    "version": "1.0.1",
    "environment": "production",
    "system_status": {
      "database": "OK",
      "qr_decoder": "OPERATIONAL",
      "latency_ms": 1.25
    },
    "timestamp": "2026-08-21T13:45:00+07:00"
  }
  ```
