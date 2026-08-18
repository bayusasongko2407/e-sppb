# DOKUMENTASI RESMI API & INTEGRASI E-SPPB ENTERPRISE ENGINE

Dokumen ini berisi spesifikasi lengkap REST API, kontrak payload, struktur response JSON, mekanisme autentikasi, serta daftar endpoint yang digunakan oleh **E-SPPB Mobile Enterprise**, **Web Portal**, dan **Third-Party Service**.

---

## 1. STANDAR RESPON & PROTOKOL HTTP

### Base URL
- **Production**: `https://e-sppb.engiboard.web.id/api`
- **API Version 1**: `https://e-sppb.engiboard.web.id/api/v1`

### Header Wajib
Seluruh permintaan ke API disarankan mengirimkan header berikut:
```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer <sanctum_token>
```

### Pre-flight CORS
Sistem telah mendukung pre-flight request (`OPTIONS`) dengan header respon:
- `Access-Control-Allow-Origin: *`
- `Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS`
- `Access-Control-Allow-Headers: Authorization, Content-Type, Accept, X-Requested-With`

### Format Envelope Respon Standard
- **Respon Sukses (200 / 201)**:
  ```json
  {
    "success": true,
    "message": "Deskripsi pesan sukses",
    "data": { ... }
  }
  ```
- **Respon Gagal / Validasi (422 / 401 / 404 / 500)**:
  ```json
  {
    "success": false,
    "message": "Pesan deskripsi kesalahan",
    "errors": {
      "field_name": ["Detail pesan validasi"]
    }
  }
  ```

---

## 2. MODUL AUTENTIKASI (`/api/v1/auth/*` & `/api/*`)

### A. Login API (`POST /api/v1/auth/login` atau `POST /api/login`)
Mendukung input berupa `email`, `username`, maupun `nik`.

- **Request Body**:
  ```json
  {
    "username": "user@domain.com",
    "password": "secretpassword"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Login berhasil.",
    "token": "1|sanctum_token_string...",
    "user": {
      "id": 189,
      "name": "Bayu Sasongko",
      "email": "bayusasongko@admin.com",
      "nik": "EMP-00123",
      "nip": "EMP-00123",
      "roles": ["super_admin"],
      "department": "Engineering"
    },
    "data": {
      "access_token": "1|sanctum_token_string...",
      "token": "1|sanctum_token_string...",
      "refresh_token": "2|refresh_token_string...",
      "user": { ... }
    }
  }
  ```

### B. Cek Sesi / Profil User (`GET /api/v1/auth/me` atau `GET /api/me`)
- **Headers**: `Authorization: Bearer <token>`
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "id": 189,
      "name": "Bayu Sasongko",
      "email": "bayusasongko@admin.com",
      "nik": "EMP-00123",
      "nip": "EMP-00123",
      "roles": ["super_admin"],
      "permissions": ["view_any_sppbheader", ...]
    }
  }
  ```

### C. Logout API (`POST /api/v1/auth/logout` atau `POST /api/logout`)
- **Headers**: `Authorization: Bearer <token>`
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Logout berhasil."
  }
  ```

---

## 3. MODUL DOKUMEN SPPB (`/api/v1/sppb-headers` atau `/api/v1/sppb`)

### A. List Dokumen SPPB (`GET /api/v1/sppb-headers` atau `GET /api/v1/sppb`)
Mendukung query params:
- `status`: `SUBMITTED`, `WAITING_APPROVAL`, `APPROVED`, `REJECTED`, `RELEASED`, `COMPLETED`
- `search`: Pencarian pada nomor dokumen, pemohon, keperluan, atau keterangan.
- `page`: Nomor halaman pagination (default: `1`)
- `per_page` / `limit`: Jumlah data per halaman (default: `15`)

- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Daftar SPPB berhasil ditampilkan.",
    "data": [
      {
        "id": 30,
        "uuid": "b6a8f112-...",
        "sppb_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
        "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
        "request_date": "2026-08-11",
        "status": "APPROVED",
        "priority": "medium",
        "purpose": "Perbaikan unit mesin conveyor",
        "requester_name": "Bayu Sasongko",
        "creator": {
          "id": 189,
          "name": "Bayu Sasongko",
          "email": "bayusasongko@admin.com"
        },
        "total_items": 2,
        "barcode": "95886afc60e655ab0bf333da5072b6edcc04a746c2e3b5d98f5024f681656472"
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 15,
      "total": 1,
      "last_page": 1
    }
  }
  ```

### B. Detail Dokumen SPPB (`GET /api/v1/sppb-headers/{id_or_uuid}`)
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "id": 30,
      "uuid": "b6a8f112-...",
      "sppb_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
      "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
      "status": "APPROVED",
      "purpose": "Perbaikan unit mesin conveyor",
      "creator": { ... },
      "items": [
        {
          "id": 14,
          "item_asset_name": "VIDEOJET DATAFLEX THERMAL...",
          "quantity": "1.00",
          "reference_code": "2608003324",
          "delivery_status": "PENDING"
        }
      ]
    }
  }
  ```

### C. Buat Pengajuan Baru SPPB (`POST /api/v1/sppb-headers` atau `POST /api/v1/sppb`)
- **Request Body**:
  ```json
  {
    "purpose": "Pengambilan bahan baku produksi",
    "priority": "urgent",
    "department": "Operasional",
    "items": [
      {
        "item_name": "Oli Mesin Shell 10W-40",
        "item_code": "OLI-10W40",
        "qty_requested": 2,
        "unit": "Liter",
        "notes": "Unit genset"
      }
    ]
  }
  ```
- **Response (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Draft SPPB berhasil dibuat.",
    "data": {
      "id": 31,
      "sppb_number": "SPPB/SJA SPJ/ENG/2026/08/00003",
      "status": "DRAFT"
    }
  }
  ```

---

## 4. MODUL PERSETUJUAN / APPROVAL (`/api/v1/sppb-headers/{id}/approve` & `/reject`)

### A. Approve SPPB (`POST /api/v1/sppb-headers/{uuid}/approve`)
- **Request Body**:
  ```json
  {
    "notes": "Disetujui sesuai kuota pengeluaran",
    "approved_items": [
      { "item_id": 14, "qty_approved": 1 }
    ]
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Step approval berhasil disetujui.",
    "data": { ... }
  }
  ```

### B. Reject SPPB (`POST /api/v1/sppb-headers/{uuid}/reject`)
- **Request Body**:
  ```json
  {
    "reason": "Alokasi barang sedang dikunci untuk maintenance"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Workflow step berhasil ditolak."
  }
  ```

---

## 5. MODUL SCANNER & VERIFIKASI BARCODE / QR (`/api/v1/verify-barcode`)

### A. Verifikasi Kode Barcode / QR (`POST /api/v1/verify-barcode` atau `GET /api/v1/sppb/verify`)
Mendukung input payload berupa **Full URL Scan** (`https://e-sppb.engiboard.web.id/verify/document/44f3ef...`), **Token SHA256 (64-char hex)**, **Nomor Dokumen SPPB**, atau **Nomor Surat Jalan**.
Key payload yang didukung: `"barcode"`, `"code"`, `"barcode_string"`, `"token"`, `"hash"`, `"qr_data"`.

- **Request Body (Contoh 1: Full URL dari Scanner Camera)**:
  ```json
  {
    "barcode": "https://e-sppb.engiboard.web.id/verify/document/44f3ef822de04311b39a9a2324a052c3ad5f1af8a2ae12c2f3b5a3d8fbad42a9"
  }
  ```
- **Request Body (Contoh 2: Token / Kode Singkat)**:
  ```json
  {
    "code": "44f3ef822de04311b39a9a2324a052c3ad5f1af8a2ae12c2f3b5a3d8fbad42a9"
  }
  ```
- **Response Valid (200 OK)**:
  ```json
  {
    "status": "VALID",
    "success": true,
    "valid": true,
    "validation_id": "85d349c9-aee3-4368-a515-d8a80a376b17",
    "data": {
      "document_type": "sppb",
      "document_number": "SPPB/SJA SPJ/ENG/2026/08/00002",
      "status_sppb": "DISETUJUI",
      "plant_name": "PT Santos Jaya Abadi - Sepanjang Plant",
      "department_name": "Engineering",
      "requester_name": "Laksana Adi Nugroho",
      "is_urgent": false,
      "request_date": "2026-08-11",
      "date_needed": "2026-08-12",
      "purpose": "Percobaan tahap 2 aplikasi versi 1.2",
      "locations": {
        "origin": "Gudang Trosobo A25",
        "destination": "Workshop Engineering"
      },
      "items_summary": {
        "total_item_types": 2,
        "total_quantity_approved": 11
      },
      "approval_summary": [
        {
          "role": "Verifikasi barang yang akan dikirim",
          "status": "DISETUJUI",
          "approved_at": "2026-08-11 11:46"
        },
        {
          "role": "Persetujuan pegiriman barang",
          "status": "DISETUJUI",
          "approved_at": "2026-08-11 11:47"
        }
      ]
    }
  }
  ```
- **Response Tidak Valid (400 / 404)**:
  ```json
  {
    "status": "NOT_FOUND",
    "success": false,
    "valid": false,
    "message": "Payload QR Code atau token verifikasi tidak valid.",
    "error_code": "INVALID_OR_EXPIRED_BARCODE"
  }
  ```

---

## 6. MODUL SURAT JALAN & EPOD (`/api/v1/goods-releases`)

### A. Buat Surat Jalan Baru (`POST /api/v1/goods-releases`)
- **Request Body**:
  ```json
  {
    "sppb_header_id": 30,
    "driver_name": "Ferry",
    "vehicle_number": "L 1201 WW",
    "expedition_name": "Tetuko",
    "delivery_date": "2026-08-14",
    "notes": "Pengiriman barang ke lokasi workshop"
  }
  ```
- **Response (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Surat Jalan berhasil dibuat.",
    "data": {
      "id": 17,
      "release_number": "SJ-20260814-0001",
      "status": "RELEASED"
    }
  }
  ```

### B. Konfirmasi Penerimaan Barang / EPOD (`POST /api/v1/goods-releases/{uuid}/confirm-receipt`)
- **Request Body**:
  ```json
  {
    "recipient_name": "Budi Hartono",
    "signature": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAB...",
    "notes": "Diterima lengkap dan kondisi baik"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Konfirmasi penerimaan barang berhasil disimpan.",
    "data": {
      "status": "DELIVERED",
      "has_signature": true,
      "recipient_name": "Budi Hartono"
    }
  }
  ```

---

## 7. DASHBOARD METRICS, NOTIFIKASI & HEALTH CHECK

### A. Dashboard Metrics Overview (`GET /api/v1/dashboard/metrics`)
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "total_sppb": 142,
      "pending_approvals": 8,
      "ready_for_release": 15,
      "completed_today": 24,
      "critical_alerts": 0
    }
  }
  ```

### B. Notifikasi Mobile (`GET /api/v1/notifications`)
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "9a382c4e-...",
        "type": "goods_released",
        "data": {
          "message": "Surat Jalan #SJ-20260814-0001 telah diterbitkan."
        },
        "read_at": null,
        "created_at": "2026-08-14T15:00:00Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 20,
      "total": 1,
      "unread_count": 1
    }
  }
  ```

### C. Health Check System (`GET /api/health` atau `GET /api/v1/health`)
- **Response (200 OK)**:
  ```json
  {
    "status": "ok",
    "success": true,
    "service": "E-SPPB Backend Enterprise API",
    "version": "1.0.0",
    "environment": "production",
    "system_status": {
      "database": "OK",
      "qr_decoder": "OPERATIONAL",
      "latency_ms": 1.25
    },
    "timestamp": "2026-08-14T15:45:00Z"
  }
  ```

---

## 8. CHECKLIST INTEGRASI ZERO-ERROR
1. **Tidak Ada Output HTML untuk Error API**: `Accept: application/json` dipaksa pada seluruh rute API.
2. **Backward Compatibility**: Seluruh endpoint lama maupun baru didukung penuh tanpa memutus integrasi aplikasi terpasang.
3. **CORS Safe**: Pre-flight HTTP `OPTIONS` merespons `200 OK` tanpa blokade browser/mobile webview.
