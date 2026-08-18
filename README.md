# 📦 E-SPPB Enterprise

> **Elektronik Surat Permintaan & Pelepasan Barang (E-SPPB)** — Sistem backend RESTful API & Management Panel tingkat enterprise (multi-plant) untuk pengajuan barang, otorisasi persetujuan berjenjang (*multi-stage workflow approval*), penerbitan Surat Jalan (*Goods Release*), verifikasi digital publik (SHA-256), serta microservice WhatsApp Gateway berbasis **Laravel 12** (PHP 8.3+) & **Filament v5**.

---

## 📋 Daftar Isi
1. [Gambaran Umum](#-gambaran-umum)
2. [Teknologi Utama (Tech Stack)](#-teknologi-utama-tech-stack)
3. [Fitur-Fitur Utama](#-fitur-fitur-utama)
4. [Standar RESTful API & Response Envelope](#-standar-restful-api--response-envelope)
5. [Struktur Role & Hak Akses (Spatie Permission)](#-struktur-role--hak-akses-spatie-permission)
6. [Daftar Endpoint API v1 Utama](#-daftar-endpoint-api-v1-utama)
7. [Microservice WhatsApp Gateway](#-microservice-whatsapp-gateway)
8. [Panduan Instalasi & Deployment Produksi](#-panduan-instalasi--deployment-produksi)
9. [Master Data Initial Seeder & Akun Simulasi](#-master-data-initial-seeder--akun-simulasi)
10. [Pengujian & Quality Gate](#-pengujian--quality-gate)
11. [Informasi Repositori & Lisensi](#-informasi-repositori--lisensi)

---

## 🌟 Gambaran Umum

**E-SPPB Enterprise** dirancang untuk mengotomatiskan dan memperketat alur kerja pengajuan pengeluaran barang, verifikasi aset tetap (BAT), persetujuan berjenjang oleh atasan/manager, penerbitan Surat Jalan (*Goods Release*), hingga konfirmasi penerimaan barang (*e-POD*) di lingkungan industri/manufaktur multi-plant.

Setiap dokumen dilindungi oleh:
- **Kriptografi Token SHA-256**: Generasi otomatis `verification_hash` unik 64 karakter untuk verifikasi integritas berkas digital.
- **Dynamic QR Code**: Generasi URL QR Code resmi yang dapat dipindai oleh publik tanpa perlu login (`/verify/document/{hash}`).
- **Pessimistic Data Locking (`lockForUpdate()`)**: Menjamin keamanan transaksi konkurensi tinggi dan mencegah kondisi balapan (*race condition*).
- **Audit Status Trail (`sppb_status_logs`)**: Pencatatan riwayat perubahan status dokumen beserta metadata aktor penanggung jawab (Nama & NIK).

---

## 🛠️ Teknologi Utama (Tech Stack)

| Komponen | Teknologi / Library | Versi / Keterangan |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP | `^8.3` |
| **Framework Utama** | Laravel Framework | `v12.x` |
| **Admin Panel UI** | Filament Admin | `v5.x` |
| **Frontend Reactive** | Livewire | `v4.x` |
| **Styling & UI** | Tailwind CSS | `v4.x` |
| **Database Engine** | MariaDB / MySQL | `10.11+` / `8.0+` |
| **Otentikasi API** | Laravel Sanctum | `v4.x` (Bearer Token) |
| **Role & Permission** | Spatie Laravel Permission | `v8.x` |
| **Code Formatter** | Laravel Pint | `v1.x` |
| **Testing Suite** | PHPUnit | `v11.x` (166 Test Suites) |
| **WA Microservice** | Node.js Express + `whatsapp-web.js` | Port `3000` |

---

## 🚀 Fitur-Fitur Utama

### 1. 📄 Modul SPPB (Surat Permintaan Pengeluaran Barang)
- **Dynamic Master Autocomplete**: Pilihan barang terhubung ke Master Data `items` & `units`, dengan kemampuan pencarian dinamis dan pemetaan otomatis.
- **Constraint Relasi Lokasi**: Mencegah kesalahan input jika Lokasi Asal (*Origin Location*) dan Lokasi Tujuan (*Destination Location*) bernilai sama.
- **Auto Running Number Generator**: Penomoran otomatis terisolasi secara unik per Pabrik (`plant_id`) dan periode bulan/tahun berjalan.
- **Cetak PDF & Validasi Public QR Code**: Penerbitan dokumen PDF resmi dengan Kop Surat, rincian item, TTD Digital, dan QR Code verifikasi publik SHA-256.

### 2. ⚡ Multi-Stage Workflow Approval Engine
- **Alur Persetujuan Berjenjang**: Otorisasi bertahap dari Pemohon $\rightarrow$ Supervisor $\rightarrow$ Manager $\rightarrow$ Verifikator BAT (Aset Tetap).
- **Tindakan Penyetuju**:
  - `Approve`: Meneruskan berkas ke langkah berikutnya.
  - `Request Revision`: Mengembalikan dokumen ke pemohon untuk direvisi tanpa membatalkan transaksi.
  - `Reject`: Menolak dokumen secara permanen disertai catatan alasan penolakan.
- **Delegasi Wewenang**: Pelimpahan otoritas persetujuan sementara (misal: saat cuti) dengan penanggalan otomatis.

### 3. 🚚 Modul Surat Jalan & Penerimaan Barang (Goods Release & e-POD)
- **Penerbitan Surat Jalan**: Pengeluaran berkas Surat Jalan (*Goods Release*) untuk SPPB berstatus `APPROVED`.
- **Nomor Manual vs Otomatis**: Mendukung opsi penomoran manual fisik yang difungsikan sebagai nomor utama, serta penomoran otomatis sistem sebagai nomor referensi.
- **Over-Release Protection**: Validasi kuantitas rilis barang agar tidak melebihi sisa pengajuan SPPB.
- **Konfirmasi Penerimaan (e-POD)**: Catatan waktu penerimaan (`received_at`), nama penerima (`recipient_name`), Tanda Tangan Digital (`recipient_signature`), dan foto bukti penerimaan.

### 4. 🔔 Gateway Notifikasi Multi-Saluran
- **In-App Lonceng Notifikasi**: Notifikasi real-time di aplikasi web dan PWA Mobile.
- **Email Notification (SMTP)**: Pengiriman notifikasi status dokumen via Email.
- **WhatsApp Microservice Gateway**: Pengiriman Notifikasi WhatsApp via API Node.js gateway (Port 3000) dengan fitur QR Code pairing interaktif di admin panel.

---

## 🌐 Standar RESTful API & Response Envelope

Seluruh endpoint API mengembalikan JSON Envelope yang konsisten untuk kompatibilitas Web Frontend & PWA Mobile:

### Response Sukses (`HTTP 200 OK / 201 Created`):
```json
{
  "success": true,
  "message": "Deskripsi sukses transaksi",
  "data": { ... },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 50,
    "last_page": 4
  }
}
```

### Response Error (`HTTP 400 / 401 / 403 / 404 / 422 / 500`):
```json
{
  "success": false,
  "message": "Pesan deskripsi kesalahan / validasi",
  "errors": {
    "field_name": ["Detail pesan kesalahan validasi"]
  }
}
```

### Konfigurasi CORS & Preflight:
- **Methods**: `GET, POST, PUT, PATCH, DELETE, OPTIONS`
- **Headers**: `Authorization, Content-Type, Accept, X-Requested-With, Origin`
- **Preflight OPTIONS**: Otomatis merespons `200 OK` untuk seluruh rute `/api/*`.

---

## 🔒 Struktur Role & Hak Akses (Spatie Permission)

Aplikasi memiliki **8 Peran Resmi** berbasis Spatie Laravel Permission dan **267 Permission** granular:

| Peran / Role | Deskripsi & Hak Akses |
| :--- | :--- |
| **`super_admin`** | Super Administrator (Full Access 267 Permissions & System Configuration). |
| **`admin`** | Administrator Sistem (Full Access Administrasi Data & Pengguna). |
| **`Pemohon`** | Pemohon SPPB (Membuat, mengedit draft, mengunggah lampiran, & membatalkan SPPB milik sendiri). |
| **`Supervisor`** | Supervisor Penyetuju (Menyetujui/menolak SPPB & mengelola delegasi wewenang). |
| **`Manager`** | Manager Penyetuju (Persetujuan tingkat Manager & tinjauan KPI Departemen). |
| **`BAT Verifier`** | Verifikator Bagian Aset Tetap (Pemeriksaan & verifikasi pengeluaran barang bernilai aset). |
| **`Sekuriti/Gudang`** | Petugas Eksekusi Gudang (Penerbitan Surat Jalan, pelepasan barang, & konfirmasi e-POD). |
| **`Auditor`** | Tim Audit Internal (77 Permissions - Akses *Read-Only* seluruh transaksi & log audit trail). |

---

## 📡 Daftar Endpoint API v1 Utama

### 🔑 Auth & Session Management
- `POST /api/v1/auth/login` — Login pengguna via NIK/Email & Password.
- `POST /api/v1/auth/logout` — Logout dan revokasi token Sanctum.
- `GET /api/v1/auth/me` — Ambil profil akun aktif & daftar role/permissions.
- `POST /api/v1/auth/refresh` — Refresh token otentikasi.

### 📄 Document SPPB
- `GET /api/v1/sppb` — Listing SPPB dengan pencarian, filter status, plant, & paginasi.
- `POST /api/v1/sppb` — Buat draft SPPB baru.
- `GET /api/v1/sppb/{uuid}` — Detail SPPB lengkap beserta item detail & lampiran.
- `PUT /api/v1/sppb/{uuid}` — Update draft / SPPB revisi.
- `DELETE /api/v1/sppb/{uuid}` — Hapus draft SPPB.
- `POST /api/v1/sppb/{uuid}/submit` — Kirim SPPB ke alur persetujuan workflow.
- `POST /api/v1/sppb/{uuid}/resubmit` — Kirim ulang SPPB setelah direvisi.
- `POST /api/v1/sppb/{uuid}/cancel` — Batalkan pengajuan SPPB.
- `POST /api/v1/sppb/{uuid}/approve` — Eksekusi persetujuan (Approve) langkah workflow.
- `POST /api/v1/sppb/{uuid}/reject` — Eksekusi penolakan (Reject) / permintaan revisi.
- `GET /api/v1/sppb/{uuid}/status-logs` — Riwayat log audit perubahan status SPPB.
- `GET /api/v1/sppb/{uuid}/releasable-items` — Daftar item yang siap dirilis ke Surat Jalan.

### 🚚 Surat Jalan (Goods Release) & e-POD
- `GET /api/v1/goods-releases` — Listing dokumen Surat Jalan.
- `POST /api/v1/goods-releases` — Penerbitan Surat Jalan baru berdasarkan SPPB.
- `GET /api/v1/goods-releases/{uuid}` — Detail dokumen Surat Jalan.
- `POST /api/v1/goods-releases/{uuid}/receive` — Konfirmasi penerimaan barang (e-POD) dengan foto TTD & nama penerima.

### 🔍 Verification & Diagnostics
- `GET /api/v1/verify/document/{hash}` — Verifikasi publik keabsahan dokumen via SHA-256 hash.
- `GET /api/v1/dashboard/metrics` — Metrik indikator dashboard ringkasan transaksi.
- `GET /api/v1/health` — Real-time Health Check status database, antrean, & WhatsApp gateway.

---

## 📱 Microservice WhatsApp Gateway

Folder `/whatsapp-gateway` berisi microservice Node.js untuk menangani pengiriman pesan notifikasi WhatsApp:

### Cara Menjalankan Microservice WA Gateway:
```bash
cd whatsapp-gateway
npm install
npm run dev # Menjalankan di port 3000
```

- **Endpoint Express**: `POST http://127.0.0.1:3000/send-message`
- **Pairing QR Code**: Tampil langsung di terminal Node.js atau di Admin Panel Filament (`Pengaturan Notifikasi > Tab WhatsApp`).

---

## ⚙️ Panduan Instalasi & Deployment Produksi

### 1. Clone & Setup Dependensi
```bash
git clone https://github.com/bayusasongko2407/e-sppb.git
cd e-sppb
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

### 2. Migrasi & Seeder Database Initial
```bash
# Menjalankan migrasi dan seeder awal
php artisan migrate --seed --force
```

### 3. Kompilasi Asset Frontend
```bash
npm install
npm run build
```

### 4. Cache & Optimasi Produksi
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 🔑 Master Data Initial Seeder & Akun Simulasi

Setelah eksekusi `php artisan db:seed`, sistem siap diisi data lapangan dengan initial master terpasang:
- **Master Unit (`units`)**: 24 Satuan Ukuran Standar (Pcs, Unit, Set, Box, Karton, Kg, Liter, Meter, dll.).
- **Master Posisi (`positions`)**: 7 Posisi Jabatan (Staff, Supervisor, BAT, Asisten Manager, Manager, Gudang, Auditor).
- **Master Status (`enum_controls`)**: 41 Label Status Bahasa Indonesia resmi.
- **Akun Super Admin Default**:
  - **Email**: `superadmin@esppb.local`
  - **Password**: `password` (pada environment lokal/testing)

---

## 🧪 Pengujian & Quality Gate

Proyek ini diproteksi oleh **166 Feature & Unit Test Suites** dengan total **573 assertions** yang menguji seluruh endpoint API, alur workflow approval, otorisasi Spatie, hingga konfirmasi Surat Jalan.

```bash
# 1. Menjalankan Format Kode Pint
vendor/bin/pint --format agent

# 2. Menjalankan Seluruh PHPUnit Test Suite
php artisan test --compact
```

Hasil eksekusi test:
```text
Tests:    166 passed (573 assertions)
Duration: 35.15s
```

---

## 🔗 Informasi Repositori & Lisensi

- **Repository**: [https://github.com/bayusasongko2407/e-sppb.git](https://github.com/bayusasongko2407/e-sppb.git)
- **Branch Utama**: `main`
- **Lisensi**: Enterprise Closed Source / Internal Enterprise Application.
