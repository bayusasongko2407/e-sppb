# Context API E-SPPB Enterprise untuk AI Studio & Development (Prompt / Specification)

## 1. Overview & System Description
E-SPPB Enterprise adalah sistem pengelolaan **Surat Permintaan Pengeluaran Barang (SPPB)**, **Workflow Persetujuan Bertingkat (Workflow Multi-Step Approval)**, **Verifikasi Dokumen Keamanan QR/Barcode**, dan **Surat Jalan Pelepasan Barang (Goods Release)**.

Sistem dirancang untuk mendukung integrasi web, mobile, dan sistem pihak ketiga (ERP/WMS):
- **Base URL**: `{{ url('/api/v1') }}`
- **Authentication**: Bearer Token (Laravel Sanctum) via `Authorization: Bearer {token}` header atau Session Cookie.
- **Format Data**: JSON (`Content-Type: application/json`, `Accept: application/json`).
- **Paging & Meta**: Menggunakan pagination standar Laravel (`current_page`, `per_page`, `total`, `last_page`).

---

## 2. Model Data & TypeScript Interfaces

```typescript
// ==========================================
// User & Auth Interfaces
// ==========================================
export interface Position {
  id: number;
  code: string;
  name: string;
}

export interface Plant {
  id: number;
  code: string;
  name: string;
  address?: string;
  is_active?: boolean;
}

export interface Department {
  id: number;
  code: string;
  name: string;
  plant_id?: number;
  is_active?: boolean;
}

export interface Location {
  id: number;
  code: string;
  name: string;
  plant_id?: number;
  is_active?: boolean;
}

export interface Unit {
  id: number;
  name: string;
  symbol?: string;
}

export interface Item {
  id: number;
  code: string;
  name: string;
  unit_id: number;
  unit?: Unit;
  is_active?: boolean;
}

export interface User {
  id: number;
  nik: string;
  name: string;
  email: string;
  plant_id?: number;
  department_id?: number;
  roles: string[];
  permissions: string[];
  position?: Position | null;
  plant?: Plant | null;
  department?: Department | null;
}

export interface AuthLoginResponse {
  success: boolean;
  message: string;
  data: {
    access_token: string;
    token: string;
    refresh_token: string;
    user: User;
  };
}

// ==========================================
// SPPB & Detail Interfaces
// ==========================================
export type SppbStatus =
  | 'DRAFT'
  | 'SUBMISSION_QUEUED'
  | 'WAITING_APPROVAL'
  | 'APPROVED'
  | 'REVISION_REQUIRED'
  | 'REJECTED'
  | 'CANCELLED'
  | 'RELEASE_IN_PROGRESS'
  | 'COMPLETED'
  | 'WAITING_VERIFICATION_BAT'
  | 'PROCESS_VERIFICATION_BAT'
  | 'WAITING_APPROVAL_MANAGER';

export interface SppbDetail {
  id: number;
  sppb_header_id: number;
  line_no?: number;
  item_id?: number;
  asset_id?: number;
  item_asset_name: string;
  reference_code?: string;
  quantity: number;
  unit_id: number;
  unit?: Unit;
  item?: Item;
  remarks?: string;
  delivery_status?: 'PENDING' | 'PARTIALLY_DELIVERED' | 'DELIVERED';
}

export interface SppbStatusLog {
  id: number;
  sppb_header_id: number;
  from_status?: string;
  to_status: string;
  action: string;
  remarks?: string;
  actor_id?: number;
  actor?: {
    id: number;
    name: string;
    nik?: string;
    roles?: { id: number; name: string }[];
  };
  created_at: string;
}

export interface SppbAttachment {
  id: number;
  uuid: string;
  original_name: string;
  file_size: number;
  file_size_formatted?: string;
  mime_type: string;
  extension: string;
  checksum_sha256?: string;
  uploader?: { id: number; name: string } | null;
  preview_url: string;
  download_url: string;
  viewer_url?: string;
  created_at: string;
}

export interface SppbHeader {
  id: number;
  uuid: string;
  document_number: string;
  sppb_number?: string;
  status: SppbStatus;
  priority: 'urgent' | 'medium';
  is_urgent: boolean;
  needed_name: string;
  requester_name?: string;
  request_date: string;
  date_needed: string;
  purpose: string;
  plant_id: number;
  department_id: number;
  origin_location_id: number;
  destination_location_id: number;
  verification_hash: string;
  qr_code_url: string;
  plant?: Plant;
  department?: Department;
  requester?: User;
  destination_location?: Location;
  origin_location?: Location;
  details?: SppbDetail[];
  sppb_details?: SppbDetail[];
  items?: SppbDetail[];
  total_items?: number;
  current_user_pending_step_id?: number | null;
  created_at: string;
  updated_at: string;
}

// ==========================================
// Releasable Items Interface
// ==========================================
export interface ReleasableItemInfo {
  sppb_detail_id: number;
  line_no?: number;
  item_id?: number;
  asset_id?: number;
  item_asset_name: string;
  reference_code?: string;
  unit_id: number;
  unit_name?: string;
  quantity_requested: number;
  quantity_already_released: number;
  quantity_remaining: number;
  delivery_status: 'PENDING' | 'PARTIALLY_DELIVERED' | 'DELIVERED';
  delivery_status_label: string;
  is_fully_released: boolean;
}

// ==========================================
// Goods Release (Surat Jalan) Interfaces
// ==========================================
export type GoodsReleaseStatus =
  | 'DRAFT'
  | 'RELEASED'
  | 'IN_TRANSIT'
  | 'DELIVERED'
  | 'RECEIVED'
  | 'CANCELLED';

export interface GoodsReleaseItem {
  id: number;
  goods_release_id: number;
  sppb_detail_id: number;
  quantity_requested: number;
  quantity_released: number;
  quantity_received: number;
  condition_on_release?: string;
  condition_on_receipt?: string;
  sppb_detail?: SppbDetail;
}

export interface GoodsRelease {
  id: number;
  uuid: string;
  release_number: string;
  manual_release_number?: string;
  sppb_header_id: number;
  status: GoodsReleaseStatus;
  driver_name: string;
  vehicle_number: string;
  expedition_name: string;
  delivery_date: string;
  received_at?: string | null;
  recipient_name?: string | null;
  recipient_signature?: string | null;
  has_signature?: boolean;
  receiving_notes?: string | null;
  verification_hash: string;
  qr_code_url?: string;
  sppb_header?: SppbHeader;
  goods_release_items?: GoodsReleaseItem[];
  created_at: string;
  updated_at: string;
}

// ==========================================
// Workflow Interfaces
// ==========================================
export type ApproverStatus =
  | 'PENDING'
  | 'APPROVED'
  | 'REJECTED'
  | 'REVISION_REQUESTED'
  | 'CANCELLED';

export type WorkflowInstanceStatus =
  | 'QUEUED'
  | 'IN_PROGRESS'
  | 'APPROVED'
  | 'REJECTED'
  | 'REVISION_REQUIRED'
  | 'CANCELLED'
  | 'FAILED';

export type WorkflowInstanceStepStatus =
  | 'QUEUED'
  | 'PENDING'
  | 'APPROVED'
  | 'REJECTED'
  | 'REVISION_REQUESTED'
  | 'CANCELLED'
  | 'EXPIRED';

export interface WorkflowStepApprover {
  id: number;
  workflow_instance_step_id: number;
  approver_id: number;
  status: ApproverStatus;
  acted_at?: string;
  remarks?: string;
  approver?: User;
}

export interface WorkflowInstanceStep {
  id: number;
  workflow_instance_id: number;
  step_order: number;
  step_name: string;
  status: WorkflowInstanceStepStatus;
  step_approvers?: WorkflowStepApprover[];
}

export interface WorkflowInstance {
  id: number;
  uuid: string;
  sppb_header_id: number;
  status: WorkflowInstanceStatus;
  current_step_order: number;
  sppb_header?: SppbHeader;
  workflow_instance_steps?: WorkflowInstanceStep[];
}

export interface WorkflowDelegation {
  id: number;
  delegator_id: number;
  delegate_id: number;
  plant_id?: number;
  starts_at: string;
  ends_at: string;
  reason: string;
  is_active: boolean;
  delegator?: User;
  delegate?: User;
  plant?: Plant;
}

// ==========================================
// Branding & Logo Interfaces
// ==========================================
export interface LogoAssetInfo {
  path?: string | null;
  url?: string | null;
}

export interface BrandingSettings {
  app_custom_name: string;
  company_name: string;
  app_primary_color: string;
  logos: {
    light: LogoAssetInfo;
    dark: LogoAssetInfo;
    favicon: LogoAssetInfo;
    login: LogoAssetInfo;
    pdf: LogoAssetInfo;
  };
  logo_height: number;
  logo_login_height: number;
  logo_pdf_position: 'left' | 'center' | 'right';
  logo_pdf_height: number;
  logo_pdf_show_address: boolean;
}
```

---

## 3. Spesifikasi Endpoint API Lengkap

### 3.1. Autentikasi & Profile

#### 1. Login (Dapatkan Token Sanctum)
- **POST** `/api/v1/auth/login` (Alias: `POST /api/login`)
- **Body**:
  ```json
  {
    "email": "user@perusahaan.com", // atau NIK / username
    "password": "password123"
  }
  ```
- **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Login berhasil.",
    "data": {
      "access_token": "1|token-string...",
      "refresh_token": "2|token-string...",
      "user": {
        "id": 1,
        "nik": "EMP-001",
        "name": "Budi Hartono",
        "email": "budi@perusahaan.com",
        "plant_id": 1,
        "department_id": 2,
        "roles": ["pemohon", "approver"],
        "permissions": ["view_any_sppbheader", "create_sppbheader"],
        "position": { "id": 1, "code": "SPV", "name": "Supervisor Produksi" },
        "plant": { "id": 1, "code": "PLT1", "name": "Pabrik Utama", "address": "Jl. Industri No. 1" },
        "department": { "id": 2, "code": "PROD", "name": "Produksi" }
      }
    }
  }
  ```

#### 2. Refresh Token
- **POST** `/api/v1/auth/refresh`
- **Body**: `{ "refresh_token": "2|token-string..." }`
- **Response 200 OK**: `{ "success": true, "data": { "access_token": "3|new-token...", "refresh_token": "2|same-refresh..." } }`

#### 3. Logout
- **POST** `/api/v1/auth/logout` (Auth: Bearer Token)
- **Response 200 OK**: `{ "success": true, "message": "Logout berhasil." }`

#### 4. Profil Pengguna (Me)
- **GET** `/api/v1/auth/me` (Alias: `GET /api/me`, Auth: Bearer Token)
- **Response 200 OK**: Mengembalikan profil pengguna, roles, permissions, plant, department, dan posisi.

---

### 3.2. Pengelolaan SPPB (Surat Permintaan Pengeluaran Barang)

#### 1. Daftar SPPB (List & Filter)
- **GET** `/api/v1/sppb` (Auth: Bearer Token)
- **Query Params**:
  - `status`: `DRAFT`, `WAITING_APPROVAL`, `APPROVED`, `RELEASE_IN_PROGRESS`, `COMPLETED`, `REJECTED`, `CANCELLED`
  - `plant_id`: integer
  - `search`: string (nomor dokumen, peruntukan, nama pemohon, NIK)
  - `per_page` / `limit`: integer (default: 15)
  - `sort`: string (default: `created_at`), `direction`: `asc` | `desc`
- **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Daftar SPPB berhasil ditampilkan.",
    "data": [
      {
        "id": 10,
        "uuid": "99351052-a5e2-4bd5-8f6a-93f87c08a94b",
        "document_number": "SPPB/PLT1/PROD/2026/08/0010",
        "status": "APPROVED",
        "priority": "urgent",
        "needed_name": "Perbaikan Lini A",
        "requester": { "name": "Budi Hartono", "nik": "EMP-001" },
        "total_items": 3,
        "qr_code_url": "https://e-sppb.engiboard.web.id/v1/verify/document/hash123..."
      }
    ],
    "meta": { "current_page": 1, "per_page": 15, "total": 1, "last_page": 1 }
  }
  ```

#### 2. Buat Draft SPPB Baru
- **POST** `/api/v1/sppb` (Auth: Bearer Token)
- **Body**:
  ```json
  {
    "plant_id": 1,
    "department_id": 2,
    "origin_location_id": 1,
    "destination_location_id": 3,
    "needed_name": "Proyek Penggantian Bearing Pompa",
    "request_date": "2026-08-20",
    "date_needed": "2026-08-22",
    "purpose": "Kebutuhan mendesak pemeliharaan mesin",
    "is_urgent": true,
    "items": [
      { "item_name": "Bearing SKF 6205", "item_code": "BRG-6205", "quantity": 4, "unit": "Pcs", "notes": "Untuk Pompa 01" },
      { "item_name": "Grease High Temp", "item_code": "GRS-HT", "quantity": 1, "unit": "Can", "notes": "Pelumas" }
    ]
  }
  ```
- **Response 201 Created**: Mengembalikan resource SPPB yang baru dibuat dalam status `DRAFT`.

#### 3. Detail SPPB (Show)
- **GET** `/api/v1/sppb/{uuid}` (Mendukung UUID, ID numerik, atau No. Dokumen)
- **Catatan Transisi BAT**: Jika status dokumen `WAITING_VERIFICATION_BAT` dan diakses oleh BAT Approver, status otomatis beralih ke `PROCESS_VERIFICATION_BAT` dan dicatat di status log `BAT_OPENED`.

#### 4. Update Draft SPPB
- **PUT** `/api/v1/sppb/{uuid}`
- **Body**: `{ "origin_location_id": 1, "destination_location_id": 3, "needed_name": "...", "date_needed": "2026-08-23", "purpose": "...", "is_urgent": false }`

#### 5. Hapus Draft SPPB
- **DELETE** `/api/v1/sppb/{uuid}` (Hanya untuk dokumen berstatus `DRAFT`)

#### 6. Submit SPPB ke Alur Persetujuan
- **POST** `/api/v1/sppb/{uuid}/submit`
- **Response 200 OK**: `{ "success": true, "message": "SPPB berhasil disubmit dan sedang diproses.", "data": { "command_uuid": "..." } }`

#### 7. Ajukan Ulang SPPB (Resubmit)
- **POST** `/api/v1/sppb/{uuid}/resubmit` (Untuk dokumen berstatus `REVISED` atau `REJECTED`)

#### 8. Batalkan Permohonan SPPB (Cancel)
- **POST** `/api/v1/sppb/{uuid}/cancel`
- **Body**: `{ "reason": "Alasan pembatalan minimal 10 karakter..." }`

#### 9. Persetujuan & Penolakan Langsung (Direct Compatibility)
- **POST** `/api/v1/sppb/{uuid}/approve` (Body: `{ "remarks": "Disetujui" }`)
- **POST** `/api/v1/sppb/{uuid}/reject` (Body: `{ "remarks": "Alasan penolakan..." }`)

---

### 3.3. Item Detail SPPB

- **GET** `/api/v1/sppb/{uuid}/details` : Daftar item barang dalam SPPB.
- **POST** `/api/v1/sppb/{uuid}/details` : Tambah item ke SPPB.
  - Body: `{ "item_id": 12, "quantity": 5.0, "remarks": "Catatan item" }`
- **PUT** `/api/v1/sppb/{uuid}/details/{detailId}` : Edit kuantitas/catatan item.
- **DELETE** `/api/v1/sppb/{uuid}/details/{detailId}` : Hapus item dari SPPB draft.

---

### 3.4. Lampiran SPPB (Attachments)

- **GET** `/api/v1/sppb/{uuid}/attachments` : Daftar berkas lampiran dengan signed temporary URL (`preview_url`, `download_url`, `viewer_url`).
- **POST** `/api/v1/sppb/{uuid}/attachments` : Unggah lampiran (multipart/form-data: `file`, max 10MB).
- **DELETE** `/api/v1/sppb/{uuid}/attachments/{attachmentUuid}` : Hapus lampiran.

---

### 3.5. Audit Histori Status & Sisa Kuota Pelepasan Barang

#### 1. Histori Status (Status Logs)
- **GET** `/api/v1/sppb/{uuid}/status-logs`
- **Response 200 OK**: Riwayat lengkap alur persetujuan, waktu transisi, aktor/approver, dan catatan.

#### 2. Sisa Kuota Pelepasan Barang (Releasable Items)
- **GET** `/api/v1/sppb/{uuid}/releasable-items`
- **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Daftar sisa kuota barang SPPB berhasil ditampilkan.",
    "data": {
      "sppb_header_id": 10,
      "sppb_uuid": "99351052-a5e2-4bd5-8f6a-93f87c08a94b",
      "document_number": "SPPB/PLT1/PROD/2026/08/0010",
      "header_status": "APPROVED",
      "items": [
        {
          "sppb_detail_id": 15,
          "item_asset_name": "Bearing SKF 6205",
          "quantity_requested": 4.0,
          "quantity_already_released": 2.0,
          "quantity_remaining": 2.0,
          "delivery_status": "PARTIALLY_DELIVERED",
          "delivery_status_label": "Pengiriman Sebagian",
          "is_fully_released": false
        }
      ],
      "releasable_items": [ /* items dengan remaining > 0 */ ]
    }
  }
  ```

---

### 3.6. Surat Jalan & Pelepasan Barang (Goods Release)

#### 1. Buat Surat Jalan dari SPPB (Pelepasan Sebagian / Penuh)
- **POST** `/api/v1/sppb/{uuid}/goods-releases` (Alias: `POST /api/v1/goods-releases` dengan `{ "sppb_header_id": "..." }`)
- **Body**:
  ```json
  {
    "driver_name": "Ahmad Pengemudi",
    "vehicle_number": "B 1234 ABC",
    "expedition_name": "Logistik Internal",
    "delivery_date": "2026-08-20",
    "notes": "Pengiriman kloter 1",
    "items": [ // Opsional: jika dikosongkan/diabaikan, sistem otomatis merilis seluruh sisa kuota barang SPPB
      {
        "sppb_detail_id": 15,
        "quantity_released": 2.0,
        "condition_on_release": "Baik",
        "notes": "Karton 1 & 2"
      }
    ]
  }
  ```
- **Response 201 Created**: Mengembalikan Surat Jalan baru dengan nomor seri otomatis (misal `SJ-20260820-0010-1`), items terlampir, dan `verification_hash`.

#### 2. Daftar Surat Jalan
- **GET** `/api/v1/goods-releases` (Filter: `status`, `sppb_header_id`, `search`, `per_page`)

#### 3. Detail Surat Jalan
- **GET** `/api/v1/goods-releases/{uuid}` (Mendukung UUID, Nomor SJ, atau Hash Verifikasi)

#### 4. Konfirmasi Penerimaan Barang di Lapangan (Public / QR Scan)
- **POST** `/api/v1/goods-releases/{uuid}/receive` (Alias: `/confirm-receipt`, `/status`)
- **Auth**: Public (dapat dipanggil tanpa token via QR scan fisik). Jika Bearer token disertakan, ID pengguna disimpan sebagai `received_by_id`.
- **Body**:
  ```json
  {
    "recipient_name": "Budi Santoso",
    "recipient_signature": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
    "receiving_notes": "Barang diterima lengkap dan berfungsi normal",
    "received_at": "2026-08-20T14:30:00+07:00"
  }
  ```
- **Response 200 OK (Konfirmasi Pertama)**:
  ```json
  {
    "success": true,
    "message": "Penerimaan barang berhasil dikonfirmasi.",
    "data": {
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "release_number": "SJ-20260820-0010-1",
      "status": "DELIVERED",
      "recipient_name": "Budi Santoso",
      "has_signature": true,
      "recipient_signature": "data:image/png;base64,...",
      "receiving_notes": "Barang diterima lengkap dan berfungsi normal",
      "received_at": "2026-08-20T14:30:00+07:00",
      "updated_at": "2026-08-20T14:30:05+07:00"
    },
    "already_confirmed": false
  }
  ```
- **Response 200 OK (Idempoten - Sudah Pernah Dikonfirmasi)**:
  `already_confirmed: true`, mempertahankan data konfirmasi asli tanpa menimpa nama/tanda tangan.

---

### 3.7. Workflow Engine & Persetujuan Bertingkat

#### 1. Inbox Tugas Persetujuan (Pending Approvals)
- **GET** `/api/v1/workflow/tasks` (Auth: Bearer Token)
- Menampilkan seluruh antrean dokumen yang memerlukan tindakan persetujuan dari pengguna yang sedang aktif (termasuk tugas delegasi).

#### 2. Detail Instansi Workflow
- **GET** `/api/v1/workflow/instances/{uuid}` : Menampilkan tahapan step, status urutan, dan approver terkait.

#### 3. Setujui Tahapan (Approve Step)
- **POST** `/api/v1/workflow/steps/{stepId}/approve`
- **Body**: `{ "remarks": "Disetujui sesuai kuota gudang" }`

#### 4. Tolak Tahapan (Reject Step)
- **POST** `/api/v1/workflow/steps/{stepId}/reject`
- **Body**: `{ "remarks": "Stok barang sedang kosong" }` *(Wajib diisi, min 5 karakter)*

#### 5. Minta Revisi Tahapan (Request Revision)
- **POST** `/api/v1/workflow/steps/{stepId}/revision`
- **Body**: `{ "remarks": "Mohon sesuaikan kuantitas sesuai kebutuhan 1 minggu" }` *(Wajib diisi, min 5 karakter)*

---

### 3.8. Delegasi Wewenang (Workflow Delegations)

- **GET** `/api/v1/workflow/delegations` : Daftar delegasi wewenang aktif/riwayat.
- **POST** `/api/v1/workflow/delegations` : Buat delegasi baru.
  - Body:
    ```json
    {
      "delegate_id": 5,
      "plant_id": 1,
      "starts_at": "2026-08-20T08:00:00+07:00",
      "ends_at": "2026-08-27T17:00:00+07:00",
      "reason": "Cuti tahunan",
      "is_active": true
    }
    ```
- **PUT** `/api/v1/workflow/delegations/{id}` : Edit tanggal/alasan delegasi.
- **DELETE** `/api/v1/workflow/delegations/{id}` : Nonaktifkan delegasi wewenang.

---

### 3.9. Verifikasi Dokumen & Universal QR Decoder

#### 1. Universal QR Decoder (JSON / String / Encrypted Payload)
- **POST** `/api/v1/verify/document` atau `GET /api/v1/verify/document/{hash?}`
- **Accepts**: Base64 encrypted string, SHA-256 hash token, Nomor SPPB, atau Nomor Surat Jalan.
- **Response 200 OK**:
  ```json
  {
    "status": "VALID",
    "success": true,
    "valid": true,
    "document_type": "GOODS_RELEASE",
    "data": {
      "release_number": "SJ-20260820-0010-1",
      "document_number": "SPPB/PLT1/PROD/2026/08/0010",
      "driver_name": "Ahmad Pengemudi",
      "vehicle_number": "B 1234 ABC",
      "status": "RELEASED",
      "is_delivered": false
    }
  }
  ```

#### 2. Portal Web Verifikasi Dokumen
- **GET** `/verify/document/{sha256Token}` : Tampilan web publik responsif untuk pemindaian barcode/QR code oleh pihak eksternal/auditor.

---

### 3.10. Metrik, Notifikasi & Kesehatan Sistem

- **GET** `/api/v1/dashboard/metrics` : Ringkasan metrik dashboard (`total_sppb`, `pending_approvals`, `ready_for_release`, `completed_today`).
- **GET** `/api/v1/notifications` : Daftar notifikasi pengguna terautentikasi (dengan pagination dan `unread_count`).
- **PATCH** `/api/v1/notifications/{id}/read` : Menandai notifikasi telah dibaca.
- **GET** `/api/v1/health` (Alias: `GET /api/health`) : Diagnostik real-time status database, latency ms, dan verifikasi enkripsi QR decoder.
- **GET** `/api/v1/public/sandbox-info` : Informasi konfigurasi sandbox & rate limiting.

---

### 3.11. Pengaturan Logo & Branding Aplikasi (Branding & Logo API)

#### 1. Dapatkan Pengaturan Branding & Logo (Public)
- **GET** `/api/v1/branding` (Alias: `GET /api/v1/public/branding`, `GET /api/branding`)
- **Auth**: Public (tidak membutuhkan token, dapat digunakan oleh Frontend, Mobile, Web PWA)
- **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Pengaturan logo dan branding aplikasi berhasil diambil.",
    "data": {
      "app_custom_name": "E-SPPB Enterprise",
      "company_name": "PT SANTOS JAYA ABADI",
      "app_primary_color": "#2563EB",
      "logos": {
        "light": {
          "path": "logos/logo-light.png",
          "url": "https://e-sppb.engiboard.web.id/storage/logos/logo-light.png"
        },
        "dark": {
          "path": "logos/logo-dark.png",
          "url": "https://e-sppb.engiboard.web.id/storage/logos/logo-dark.png"
        },
        "favicon": {
          "path": "logos/favicon.ico",
          "url": "https://e-sppb.engiboard.web.id/storage/logos/favicon.ico"
        },
        "login": {
          "path": "logos/logo-login.png",
          "url": "https://e-sppb.engiboard.web.id/storage/logos/logo-login.png"
        },
        "pdf": {
          "path": "logos/logo-pdf.png",
          "url": "https://e-sppb.engiboard.web.id/storage/logos/logo-pdf.png"
        }
      },
      "logo_height": 36,
      "logo_login_height": 60,
      "logo_pdf_position": "left",
      "logo_pdf_height": 40,
      "logo_pdf_show_address": true
    },
    "timestamp": "2026-08-20T13:10:00+07:00"
  }
  ```

#### 2. Dapatkan Pengaturan Branding (Admin)
- **GET** `/api/v1/settings/branding` (Auth: Bearer Token)

#### 3. Perbarui Logo, Favicon & Branding (Admin)
- **POST** `/api/v1/settings/branding` (Auth: Bearer Token)
- **Content-Type**: `multipart/form-data` atau `application/json`
- **Fields / Upload Files**:
  - `app_custom_name` (string, optional, max 100): Nama aplikasi kustom
  - `company_name` (string, optional, max 200): Nama perusahaan
  - `app_primary_color` (string, optional, format hex `#RRGGBB` atau `#RGB`)
  - `logo_height` (integer, optional, 16-200 px)
  - `logo_login_height` (integer, optional, 20-300 px)
  - `logo_pdf_position` (string, optional: `left`, `center`, `right`)
  - `logo_pdf_height` (integer, optional, 10-150 mm)
  - `logo_pdf_show_address` (boolean, optional)
  - `logo_light` (file, image: png, jpg, jpeg, webp, svg, max 5MB): Logo tema terang
  - `logo_dark` (file, image: png, jpg, jpeg, webp, svg, max 5MB): Logo tema gelap
  - `logo_favicon` (file: ico, png, svg, max 2MB): Favicon tab browser
  - `logo_login` (file, image: png, jpg, jpeg, webp, svg, max 5MB): Logo halaman login
  - `logo_pdf` (file, image: png, jpg, jpeg, webp, svg, max 5MB): Logo dokumen cetak PDF
- **Response 200 OK**: Mengembalikan data branding terbaru beserta URL publik aset yang diunggah.

#### 4. Hapus Aset Logo / Favicon Tertentu (Admin)
- **DELETE** `/api/v1/settings/branding/logos/{type}` (Auth: Bearer Token)
- **Path Parameter**:
  - `type` (string, required): `light`, `dark`, `favicon`, `login`, atau `pdf`
- **Response 200 OK**:
  ```json
  {
    "success": true,
    "message": "Logo favicon berhasil dihapus.",
    "data": { /* updated branding object */ },
    "timestamp": "2026-08-20T13:10:00+07:00"
  }
  ```

---

## 4. Master Data Lookup

- **GET** `/api/v1/sppb/master/plants` : Daftar pabrik/plant aktif.
- **GET** `/api/v1/sppb/master/departments?plant_id={id}` : Daftar departemen.
- **GET** `/api/v1/sppb/master/locations?plant_id={id}` : Daftar lokasi gudang/area.
- **GET** `/api/v1/sppb/master/items?search={query}&limit=50` : Pencarian master item barang.
- **GET** `/api/v1/sppb/stats` : Statistik agregat jumlah SPPB per status.

---

## 5. Ringkasan Status & Alur Bisnis (State Machine)

1. **SPPB Lifecycle**:
   - `DRAFT` ➔ *(Submit)* ➔ `WAITING_APPROVAL` (atau `SUBMISSION_QUEUED` pada proses antrian)
   - `WAITING_APPROVAL` ➔ *(Approver BAT membuka dokumen)* ➔ `PROCESS_VERIFICATION_BAT`
   - `WAITING_APPROVAL` / `PROCESS_VERIFICATION_BAT` ➔ *(Approve Step)* ➔ `APPROVED` (atau `WAITING_APPROVAL_MANAGER` jika bertingkat)
   - `WAITING_APPROVAL` / `PROCESS_VERIFICATION_BAT` ➔ *(Minta Revisi)* ➔ `REVISION_REQUIRED` ➔ *(Resubmit)* ➔ `WAITING_APPROVAL`
   - `WAITING_APPROVAL` / `PROCESS_VERIFICATION_BAT` ➔ *(Tolak)* ➔ `REJECTED`
   - `APPROVED` ➔ *(Buat Surat Jalan Sebagian)* ➔ `RELEASE_IN_PROGRESS`
   - `RELEASE_IN_PROGRESS` ➔ *(Semua Barang Terkirim & Dikonfirmasi)* ➔ `COMPLETED`
   - `DRAFT` / `WAITING_APPROVAL` / `REJECTED` / `APPROVED` ➔ *(Cancel)* ➔ `CANCELLED`

2. **Goods Release (Surat Jalan) Lifecycle**:
   - `DRAFT` ➔ *(Terbitkan)* ➔ `RELEASED` *(Dalam Pengiriman)*
   - `RELEASED` ➔ *(Dalam Perjalanan)* ➔ `IN_TRANSIT`
   - `RELEASED` / `IN_TRANSIT` ➔ *(Konfirmasi QR Scan Lapangan)* ➔ `DELIVERED` *(Sudah Diterima)*
   - `DRAFT` / `RELEASED` ➔ *(Batal)* ➔ `CANCELLED` *(Dibatalkan)*

3. **Workflow Instance & Step Lifecycle**:
   - **Workflow Instance**: `QUEUED` ➔ `IN_PROGRESS` ➔ `APPROVED` / `REJECTED` / `REVISION_REQUIRED` / `CANCELLED` / `FAILED`
   - **Workflow Step**: `QUEUED` ➔ `PENDING` ➔ `APPROVED` / `REJECTED` / `REVISION_REQUESTED` / `CANCELLED` / `EXPIRED`
   - **Workflow Step Approver**: `PENDING` ➔ `APPROVED` / `REJECTED` / `REVISION_REQUESTED` / `CANCELLED`
