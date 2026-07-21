# 📦 E-SPPB Enterprise

> **Elektronik Surat Permintaan & Pelepasan Barang (E-SPPB)** — Sistem manajemen pengajuan, otorisasi berjenjang (*multi-stage approval*), pelepasan barang (surat jalan), serta gateway notifikasi multi-saluran berbasis **Laravel 12** dan **Filament v5**.

---

## 📋 Daftar Isi
1. [Gambaran Umum](#-gambaran-umum)
2. [Teknologi Utama (Tech Stack)](#-teknologi-utama-tech-stack)
3. [Fitur-Fitur Utama](#-fitur-fitur-utama)
4. [Arsitektur Sistem & Aturan Beku](#-arsitektur-sistem--aturan-beku)
5. [Panduan Instalasi & Memulai](#-panduan-instalasi--memulai)
6. [Pengujian & Gate Kualitas](#-pengujian--gate-kualitas)
7. [Cron Jobs & Scheduled Commands](#-cron-jobs--scheduled-commands)
8. [Informasi Repository & Branch](#-informasi-repository--branch)

---

## 🌟 Gambaran Umum

**E-SPPB Enterprise** dirancang untuk mengotomatiskan dan memperketat alur kerja pengajuan barang, otorisasi verifikasi berjenjang, hingga pelepasan barang di lingkungan manufaktur multi-plant. Aplikasi ini menerapkan pola hirarki **Plant-based scoping** di mana `Plant` adalah entitas organisasi tertinggi tanpa ketergantungan scope Company.

Seluruh transaksi dilindungi dengan penguncian data (*pessimistic locking*), log audit status (*SppbStatusLog*), token verifikasi SHA256, serta notifikasi terpadu melalui **Lonceng In-App**, **Email SMTP**, dan **WhatsApp OpenWA Gateway**.

---

## 🛠️ Teknologi Utama (Tech Stack)

| Komponen | Teknologi / Library | Versi |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP | `^8.3` |
| **Framework Utama** | Laravel Framework | `v12.x` |
| **Admin Panel UI** | Filament Admin | `v5.x` |
| **Frontend Reactive** | Livewire | `v4.x` |
| **Styling & CSS** | Tailwind CSS | `v4.x` |
| **Database** | MariaDB / MySQL | `10.11+` |
| **Role & Permission** | Spatie Laravel Permission | `v6.x` |
| **API Authentication** | Laravel Sanctum | `v4.x` |
| **WA Gateway Engine** | OpenWA Node.js Gateway | Integration REST API |

---

## 🚀 Fitur-Fitur Utama

### 1. 📄 Modul Pengajuan SPPB
- **Pengajuan Multi-Item**: Pengisian detail barang, aset, barcode, satuan, qty, lokasi asal/tujuan, dan alasan keperluan.
- **Auto Running Number**: Format penomoran dokumen otomatis terisolasi per Plant dan periode bulan/tahun.
- **Pratinjau & Cetak Dokumen PDF**: Tampilan PDF resmi dengan Kop Surat Perusahaan dan **QR Code Keaslian Dokumen** berbasis `verification_sha256_token`.

### 2. ⚡ Modul Multi-Stage Workflow Approval Engine
- **Template & Step Urutan Approval**: Penentuan alur persetujuan dinamis (BAT, Verifikator, Manager, Plant Manager).
- **Mode Persetujuan**: Dukungan mode `ANY` (salah satu), `ALL` (semua), dan Minimum Quorum.
- **Tindakan Approver**: **Approve**, **Reject** (Penolakan), dan **Request Revision** (Permintaan Revisi Kembali ke Pemohon).
- **Delegasi Otorisasi (*Workflow Delegation*)**: Pelimpahan hak approval sementara waktu dengan sistem *circular-loop guard* dan verifikasi masa berlaku otomatis.

### 3. 🚚 Modul Surat Jalan / Pelepasan Barang (Goods Release / SAT)
- Penerbitan Surat Jalan (*Goods Release*) berdasarkan dokumen SPPB yang telah disetujui penuh (*Final Approved*).
- Dukungan konsolidasi **Multiple SPPB dalam 1 Surat Jalan**.
- Manajemen status penerbitan (*Draft* vs *Released/Final*).

### 4. 🔔 Modul Pengaturan Notifikasi Terpadu (Notification Settings - 3 Tabs)
Terletak pada menu **Pengaturan > Pengaturan Notifikasi** (`App\Filament\Pages\NotificationSettings`):
- **Tab 1: Notifikasi Sistem (In-App Lonceng)**:
  - Master Switch pengaktifan notifikasi lonceng.
  - Durasi retensi penyimpanan log notifikasi (`30` / `60` / `90` hari).
  - Checklist Matriks Event: *Pengajuan SPPB Baru*, *Permintaan Persetujuan*, *Update Tahap Approval*, *SPPB Disetujui*, *Revisi/Penolakan*, dan *Penerbitan Surat Jalan*.
- **Tab 2: Notifikasi Email (SMTP Mail Setup)**:
  - Master Switch Email, Driver, Host, Port, Username, Password, Email & Nama Pengirim.
  - Action Button **"Kirim Email Uji Coba"** untuk pengujian koneksi SMTP.
- **Tab 3: Notifikasi WhatsApp (OpenWA Gateway)**:
  - Master Switch WA, Server URL (`http://127.0.0.1:3000/send-message`), API Secret Token Header, dan Nomor Bot WA Pengirim.
  - **Live Status Viewer** (`CONNECTED` / `DISCONNECTED`) dan **QR Code Pairing Viewer**.
  - Action Button **"Kirim WA Uji Coba"** dan integrasi otomatis `WhatsAppService` & `WhatsAppChannel`.
- **Pengaturan Nomor WhatsApp Pengguna**: Pengguna dapat mengisi nomor WA aktif secara mandiri via menu *Profil & Pengaturan Akun* (`MyProfile.php`) atau dikelola oleh Super Admin.

### 5. 🔒 Modul Hak Akses & Hybrid Security Policy
- Spatie Role & Permission tanpa atribut `role_id` pada tabel `users`.
- Matriks Hak Akses Dokumen (`DocumentAccesses`) yang membatasi hak `view`, `create`, `edit`, `delete` terisolasi per `plant_id`, `department_id`, dan `module`.

### 6. 📊 Modul Pelaporan & Analytics (Reports Engine)
- Laporan Rekapitulasi SPPB dan Laporan Detail Barang/Item berbasis rentang tanggal, Plant, dan Departemen.
- Ekspor laporan langsung ke file Excel dan CSV.

### 7. 📤 Modul Import & Export Data Master
- Import & Export data Master (`Plant`, `Department`, `Location`, `Unit`, `Item`, `Asset`).
- Live Progress Widget tracking status proses impor data.

### 8. 🎨 Modul Branding & Pengaturan Aplikasi Global
- Kustomisasi Logo (Mode Terang, Mode Gelap, Favicon, Login Page, Kop PDF).
- Preset Tema Visual Hasnayeen Style (*Default*, *Nord*, *Sunset*, *Forest*, *Dracula*, *Min*).
- Kamus Istilah/Label Kustom (*Label Dictionary*) untuk seluruh modul operasional.

---

## 🏛️ Arsitektur Sistem & Aturan Beku

Aplikasi ini mematuhi **Frozen Rules Blueprint E-SPPB**:

1. **Strictly No Company Scope**: `Plant` adalah entitas organisasi tertinggi aplikasi.
2. **Clean Architecture**: Aturan bisnis diletakkan pada Service Layer (`app/Services/`), DTO (`app/DTOs/`), dan Policy (`app/Policies/`).
3. **Transaction Safety**: Seluruh perubahan status SPPB, workflow, dan pelepasan barang dieksekusi dalam `DB::transaction()` dengan *pessimistic locking* (`lockForUpdate()`).
4. **Bahasa Indonesia UI**: Tampilan antarmuka Filament menggunakan Bahasa Indonesia yang profesional, sedangkan identifier PHP dan database tetap dalam Bahasa Inggris.

---

## ⚙️ Panduan Instalasi & Memulai

### 1. Prasyarat Sistem
- PHP `>= 8.3` dengan ekstensi: `pdo`, `mbstring`, `openssl`, `bcmath`, `curl`, `json`
- Composer `^2.x`
- MariaDB `>= 10.11` atau MySQL `>= 8.0`
- Node.js `>= 18.x` & NPM

### 2. Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/bayusasongko2407/e-sppb.git
cd e-sppb

# 2. Switch ke branch dev-beta
git checkout dev-beta

# 3. Install dependency PHP
composer install

# 4. Salin file lingkungan .env
cp .env.example .env

# 5. Generate Application Key
php artisan key:generate

# 6. Konfigurasi database pada .env lalu jalankan migrasi & seeder
php artisan migrate --seed

# 7. Install & build frontend asset
npm install
npm run build
```

### 3. Menjalankan Server Lokal

```bash
# Menjalankan Laravel development server
php artisan serve
```

Akses admin panel Filament melalui browser di `http://127.0.0.1:8000/admin`.

---

## 🧪 Pengujian & Gate Kualitas

Sebelum setiap perilisan, jalankan rangkaian uji kualitas berikut:

```bash
# 1. Format kode sesuai standar Pint
vendor/bin/pint --format agent

# 2. Analisis statis kode dengan PHPStan
vendor/bin/phpstan analyse app/ --memory-limit=2G

# 3. Jalankan seluruh Test Suite PHPUnit
php artisan test --compact
```

---

## ⏱️ Cron Jobs & Scheduled Commands

Jalankan perintah ini di server (*scheduler*) untuk pembersihan otomatis riwayat notifikasi lama sesuai retensi log yang diset di Admin Panel:

```bash
php artisan notifications:prune
```

---

## 🔗 Informasi Repository & Branch

- **Repository**: [`https://github.com/bayusasongko2407/e-sppb.git`](https://github.com/bayusasongko2407/e-sppb.git)
- **Branch Aktif**: `dev-beta`
- **Lisensi**: Hak Cipta Dilindungi — PT Santos Jaya Abadi / E-SPPB Enterprise.
