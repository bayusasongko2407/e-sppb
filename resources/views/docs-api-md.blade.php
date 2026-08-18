# Context API E-SPPB Enterprise untuk AI Studio & Development (Prompt / Specification)

## 1. Overview & System Description
E-SPPB Enterprise adalah sistem pengelolaan **Surat Permintaan Pengeluaran Barang (SPPB)** dan **Surat Jalan Pelepasan Barang**.
Aplikasi Mobile / Frontend dirancang untuk pengemudi dan penerima barang di lapangan untuk melakukan **konfirmasi penerimaan barang** secara digital dengan memindai QR Code pada Surat Jalan fisik.

- **Base URL**: `{{ url('/api/v1') }}`
- **Authentication**: Bearer Token (Laravel Sanctum) via `Authorization: Bearer {token}` header (opsional pada endpoint public receive).
- **Format Data**: JSON (`Content-Type: application/json`, `Accept: application/json`).

---

## 2. Model Data & TypeScript Interfaces

```typescript
export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'gudang' | 'manager' | 'pemohon';
}

export interface GoodsReleaseItem {
  id: number;
  goods_release_id: number;
  sppb_detail_id: number;
  quantity_requested: number;
  quantity_released: number;
  quantity_received: number;
  condition_on_release?: string;
  condition_on_receipt?: string;
  sppb_detail?: {
    item?: { name: string; code?: string };
    unit?: { name: string };
  };
}

export interface GoodsRelease {
  id: number;
  uuid: string;
  release_number: string;
  manual_release_number?: string;
  status: 'RELEASED' | 'DELIVERED' | 'RECEIVED' | 'COMPLETED' | 'CANCELLED';
  driver_name: string;
  vehicle_number: string;
  expedition_name: string;
  delivery_date: string; // YYYY-MM-DD
  received_at?: string; // ISO8601
  recipient_name?: string;
  recipient_signature?: string; // base64 Data URL (data:image/png;base64,...)
  receiving_notes?: string;
  verification_hash: string;
  sppb_header?: {
    document_number: string;
    needed_name: string;
    plant?: { name: string };
    department?: { name: string };
  };
  goods_release_items?: GoodsReleaseItem[];
}

export interface ConfirmReceiptPayload {
  recipient_name: string; // Wajib, max 255 karakter
  recipient_signature?: string; // Opsional: Base64 Data URL (data:image/png;base64,...), max 5 MB
  receiving_notes?: string; // Opsional, max 1000 karakter
  received_at?: string; // Opsional: ISO8601 datetime
}

export interface ConfirmReceiptResponse {
  success: boolean;
  message: string;
  data: {
    uuid: string;
    release_number: string;
    status: string;
    recipient_name: string;
    has_signature: boolean;
    recipient_signature?: string;
    receiving_notes?: string;
    received_at: string;
    updated_at: string;
  };
  already_confirmed: boolean;
}
```

---

## 3. Spesifikasi API Endpoint Utama

### 3.1. Konfirmasi Penerimaan Barang (Public / QR Scan)
- **Method**: `POST`
- **URL**: `/api/v1/goods-releases/{uuid}/receive`
- **Auth**: Public (Tidak wajib token, namun jika ada token Sanctum disertakan akan disimpan `received_by_id`).
- **Path Parameter**:
  - `uuid` (string, required): UUID Surat Jalan, Nomor Surat Jalan, atau Hash SHA-256 verifikasi.
- **Request Headers**:
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Request Body JSON**:
  ```json
  {
    "recipient_name": "Budi Santoso",
    "recipient_signature": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
    "receiving_notes": "Barang diterima lengkap dan kondisi baik",
    "received_at": "2026-08-13T16:30:00+07:00"
  }
  ```
- **Aturan Validasi**:
  - `recipient_name`: Wajib, string, max 255 karakter.
  - `recipient_signature`: Opsional, Base64 Data URL PNG, max 5 MB.
  - `receiving_notes`: Opsional, string, max 1000 karakter.
  - `received_at`: Opsional, ISO8601 date.
- **Response 200 OK (Konfirmasi Pertama)**:
  ```json
  {
    "success": true,
    "message": "Penerimaan barang berhasil dikonfirmasi.",
    "data": {
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "release_number": "SJ-20260813-0042-1",
      "status": "DELIVERED",
      "recipient_name": "Budi Santoso",
      "has_signature": true,
      "recipient_signature": "data:image/png;base64,...",
      "receiving_notes": "Barang diterima lengkap dan kondisi baik",
      "received_at": "2026-08-13T16:30:00+07:00",
      "updated_at": "2026-08-13T16:30:05+07:00"
    },
    "already_confirmed": false
  }
  ```
- **Response 200 OK (Sudah Dikonfirmasi Sebelumnya - Idempoten)**:
  ```json
  {
    "success": true,
    "message": "Surat Jalan ini sudah pernah dikonfirmasi sebelumnya.",
    "data": { "..." : "..." },
    "already_confirmed": true
  }
  ```
- **Response 422 Unprocessable Entity (Validasi / Dibatalkan)**:
  ```json
  {
    "success": false,
    "message": "Nama penerima wajib diisi."
  }
  ```
- **Response 404 Not Found**:
  ```json
  {
    "success": false,
    "message": "No query results for model [App\\Models\\GoodsRelease]."
  }
  ```

---

### 3.2. Detail Surat Jalan (Preview sebelum Konfirmasi)
- **Method**: `GET`
- **URL**: `/api/v1/goods-releases/{uuid}`
- **Auth**: Public / Sanctum Bearer Token
- **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Detail pelepasan barang berhasil ditampilkan.",
    "data": {
      "id": 42,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "release_number": "SJ-20260813-0042-1",
      "status": "RELEASED",
      "driver_name": "Ahmad Pengemudi",
      "vehicle_number": "B 1234 ABC",
      "delivery_date": "2026-08-13",
      "received_at": null,
      "recipient_name": null,
      "sppb_header": {
        "document_number": "SPPB/PLT1/PROD/2026/08/0042",
        "needed_name": "Proyek Perbaikan Mesin"
      },
      "goods_release_items": [
        {
          "quantity_released": 10,
          "quantity_received": 0,
          "sppb_detail": {
            "item": { "name": "Baut Steel M10" },
            "unit": { "name": "PCS" }
          }
        }
      ]
    }
  }
  ```

---

### 3.3. Login Pengguna (Mendapatkan Sanctum Token)
- **Method**: `POST`
- **URL**: `/api/v1/auth/login`
- **Request Body JSON**:
  ```json
  {
    "email": "gudang@perusahaan.com",
    "password": "password123",
    "device_name": "mobile-app"
  }
  ```
- **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Login berhasil.",
    "data": {
      "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz...",
      "token_type": "Bearer",
      "user": {
        "id": 12,
        "name": "Operator Gudang",
        "email": "gudang@perusahaan.com",
        "role": "gudang"
      }
    }
  }
  ```

---

## 4. Workflows & Aturan Bisnis Mobile

1. **Alur Scan QR Code Surat Jalan**:
   - Pengguna membuka kamera / QR Scanner di mobile app.
   - Pindai QR Code fisik Surat Jalan -> ambil payload URL / string UUID.
   - Panggil `GET /api/v1/goods-releases/{uuid}` untuk mengambil detail barang.
   - Jika `received_at !== null` atau `already_confirmed === true`, tampilkan status **"Sudah Dikonfirmasi Diterima"** dengan tanda tangan & nama penerima.
   - Jika belum dikonfirmasi, tampilkan Form Konfirmasi Penerimaan.

2. **Pengisian Form & Canvas Tanda Tangan**:
   - Input `Nama Penerima` (wajib).
   - Elemen Canvas Tanda Tangan (opsional tapi disarankan).
   - Ekspor canvas menjadi Base64 PNG URL: `canvas.toDataURL('image/png')`.
   - Kirim payload ke `POST /api/v1/goods-releases/{uuid}/receive`.

3. **Status Surat Jalan**:
   - `RELEASED`: Surat Jalan telah terbit dan barang sedang dikirim.
   - `DELIVERED`: Barang telah dikonfirmasi diterima oleh penerima di lapangan.
   - `CANCELLED`: Surat Jalan dibatalkan (tidak bisa dikonfirmasi penerimaannya).
