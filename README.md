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
| **WA Gateway Engine** | OpenWA Node.js Gateway | Integration REST API |

---

## 🚀 Fitur-Fitur Utama

### 1. 📄 Modul Pengajuan SPPB (Surat Permintaan Pengeluaran Barang)
Sistem utama untuk mengelola siklus hidup permintaan pengeluaran barang operasional dan aset perusahaan:
- **Formulir Input Adaptif**: Antarmuka dinamis di mana bidang masukan *Barcode/Kode* otomatis disembunyikan jika pengguna memilih jenis barang **Non-Aset**, yang secara responsif melebarkan masukan *Nama Barang* demi kebersihan tampilan layout.
- **Dropdown Search & Free Text Autocomplete**: Pilihan nama barang menggunakan model pencarian dinamis (*datalist*) yang terhubung ke Master Data untuk standarisasi penulisan, tetapi tetap membolehkan pengetikan teks kustom secara bebas (non-master).
- **Auto-Reselect Master Data**: Jika nama barang yang diketik secara bebas cocok dengan data master, sistem secara reaktif akan otomatis memetakan kode barang, referensi kode, dan satuan secara instan.
- **Pencegahan Redundansi Alur**: Logika bisnis melarang pengajuan SPPB jika Lokasi Asal dan Lokasi Tujuan bernilai sama untuk menjaga akurasi pergerakan barang.
- **Auto Running Number Generator**: Format penomoran dokumen otomatis terisolasi secara aman per Pabrik (Plant) dan periode waktu berjalan (bulan/tahun), mencegah tumpang tindih penomoran antar-plant.
- **Pessimistic Locking & Safety Transaction**: Setiap perubahan status dokumen SPPB diproses dalam blok `DB::transaction()` menggunakan penguncian database SQL `lockForUpdate()` untuk menjamin konsistensi data dari akses multi-user secara bersamaan.
- **Ekspor Resmi PDF & Public Verification QR Code**:
  - Dokumen SPPB dapat dicetak menjadi PDF resmi dengan Kop Surat Perusahaan, rincian barang, serta riwayat *Approval Timeline*.
  - Dokumen dilengkapi **QR Code Validasi** berbasis token kriptografi SHA256 (`verification_sha256_token`).
  - Scanner QR Code akan mengarah ke halaman verifikasi publik tanpa login (`verify.blade.php`) dengan proteksi *Rate-limiting* ketat untuk mencegah brute force. Halaman ini menyajikan keabsahan dokumen serta mencatat metadata audit (alamat IP pengakses, sidik jari peramban, dan waktu akses).
- **Pemisahan Tampilan (Infolist View)**: Untuk menyajikan data yang bersih di halaman detil, tabel rincian barang dipisahkan menjadi dua bagian: **Detail Aset (Ber-Barcode)** dan **Detail Barang Non-Asset (Input Bebas)**.

### 2. ⚡ Modul Multi-Stage Workflow Approval Engine
Mesin alur kerja persetujuan berjenjang yang fleksibel dan aman untuk otorisasi dokumen:
- **Template Approval Dinamis**: Konfigurasi urutan persetujuan berjenjang (seperti BAT, Verifikator, Manager, Plant Manager) yang disesuaikan per departemen dan jenis dokumen.
- **Mode Persetujuan Fleksibel**:
  - `ANY`: Cukup salah satu dari daftar pemegang jabatan yang menyetujui.
  - `ALL`: Seluruh pejabat yang terdaftar wajib menyetujui secara kolektif.
  - `Quorum`: Persetujuan sah jika memenuhi kuorum minimum tertentu.
- **Matriks Tindakan Penyetuju (Approval Actions)**:
  - *Setujui (Approve)*: Meneruskan dokumen ke tingkat persetujuan berikutnya.
  - *Tolak (Reject)*: Menghentikan siklus dokumen secara permanen dan mencatat alasan penolakan secara tertulis.
  - *Revisi (Request Revision)*: Mengembalikan dokumen ke pemohon untuk diedit tanpa membatalkan proses dokumen secara keseluruhan.
- **Delegasi Wewenang (Workflow Delegation)**: Pelimpahan otoritas persetujuan sementara (misal: saat cuti) ke pengguna lain dengan validasi masa aktif otomatis dan proteksi *circular-loop guard* (mencegah rantai delegasi berputar balik ke orang yang sama).

### 3. 🚚 Modul Surat Jalan & Pelepasan Barang (Goods Release / SAT)
Modul logistik untuk mengontrol pengeluaran fisik barang dari area gudang:
- **Penerbitan Surat Jalan Resmi**: Pengeluaran berkas Surat Jalan (*Goods Release*) berdasarkan dokumen SPPB yang telah disetujui penuh (*Final Approved*).
- **Cetak PDF & Verifikasi QR**:
  - Dukungan tombol **Cetak PDF** untuk menerbitkan dokumen fisik Surat Jalan resmi dengan Kop Surat Perusahaan, rincian pengiriman, daftar barang, tanda tangan persetujuan lengkap, dan QR Code verifikasi dokumen.
  - **Dua Desain Penomoran Dinamis**:
    - **Surat Jalan Otomatis**: Nomor dokumen utama menggunakan nomor Surat Jalan otomatis dari sistem.
    - **Surat Jalan Manual**: Jika dikirim sebagai surat jalan manual, pengguna dapat menginput nomor Surat Jalan manual miliknya. Pada cetak PDF, nomor manual tersebut akan menjadi nomor utama dokumen, sedangkan nomor sistem otomatis akan dilabeli sebagai **No. Referensi (Reference No)**.
- **Konsolidasi Multi-SPPB**: Mendukung penggabungan beberapa dokumen SPPB aktif dari lokasi asal/tujuan yang sama ke dalam satu dokumen Surat Jalan untuk efisiensi armada pengiriman.
- **Over-Release Protection**: Validasi ketat kuantitas rilis fisik terhadap sisa kuantitas pengajuan SPPB untuk mencegah pengeluaran barang melebihi batas persetujuan.
- **Pelacakan Status Rilis**: Transisi status otomatis detail barang SPPB dari `APPROVED` menjadi `RELEASE_IN_PROGRESS` (jika baru dirilis sebagian) hingga `COMPLETED` (jika seluruh kuantitas telah dikeluarkan).

### 4. 🔔 Modul Pengaturan Notifikasi Terpadu (Notification Settings - 3 Tabs)
Gerbang notifikasi multi-saluran untuk memberikan pembaruan aktivitas dokumen secara asinkron (Terletak pada menu **Pengaturan > Pengaturan Notifikasi**):
- **Tab 1: Notifikasi Sistem (In-App Lonceng)**:
  - Master Switch pengaktifan notifikasi lonceng.
  - Durasi retensi penyimpanan log notifikasi (`30` / `60` / `90` hari) yang dibersihkan otomatis secara berkala melalui scheduler.
  - Checklist Matriks Event: *Pengajuan SPPB Baru*, *Permintaan Persetujuan*, *Update Tahap Approval*, *SPPB Disetujui*, *Revisi/Penolakan*, dan *Penerbitan Surat Jalan*.
- **Tab 2: Notifikasi Email (SMTP Mail Setup)**:
  - Master Switch Email, Driver, Host, Port, Username, Password, Email & Nama Pengirim.
  - Action Button **"Kirim Email Uji Coba"** untuk pengujian koneksi SMTP.
- **Tab 3: Notifikasi WhatsApp (OpenWA Gateway)**:
  - Master Switch WA, Server URL (`http://127.0.0.1:3000/send-message`), API Secret Token Header, dan Nomor Bot WA Pengirim.
  - **Live Status Viewer** (`CONNECTED` / `DISCONNECTED`) dan **QR Code Pairing Viewer**.
  - Action Button **"Kirim WA Uji Coba"** dan integrasi otomatis `WhatsAppService` & `WhatsAppChannel`.
- **WhatsApp Nomor Pengguna**: Validasi format nomor telepon WA pada menu profil pengguna (`MyProfile.php`) agar notifikasi terkirim tepat sasaran.

### 5. 🔒 Modul Otorisasi Keamanan & Kebijakan Hak Akses
Perlindungan keamanan data tingkat tinggi untuk mencegah kebocoran informasi dan akses tidak sah:
- **Role & Permission Granular**: Otorisasi menggunakan Spatie Laravel Permission tanpa menyimpan field `role_id` mentah di tabel `users`.
- **Matriks Hak Akses Dokumen (DocumentAccesses)**: Isolasi ketat untuk operasi *View, Create, Edit, Delete* yang disesuaikan secara dinamis per Pabrik (`plant_id`), Departemen (`department_id`), dan Modul.
- **Lockout Brute Force Protection**: Memblokir sementara akun pengguna selama 15 menit jika mendeteksi kegagalan login berturut-turut sebanyak 5 kali.
- **Verifikasi Ganti Sandi Mandiri**: Kewajiban verifikasi kata sandi saat ini (*current password*) sebelum diizinkan mengganti kata sandi baru pada halaman profil.
- **Reset Sandi Otomatis**: Generator kata sandi acak ramah pengguna bagi Super Admin untuk me-reset kata sandi pengguna menggunakan frasa kata benda/sifat Bahasa Indonesia (misal: `KopiBintang382!`) yang copyable.

### 6. 📊 Modul Pelaporan & Laporan Ekspor (Reports Engine)
Mesin pelaporan untuk analisis data operasional dan audit logistik (Terletak pada menu **Laporan > Laporan**):
- **Laporan Dinamis**: Filter rentang tanggal, departemen, dan pabrik untuk penarikan Laporan Rekapitulasi SPPB dan Laporan Detail Barang.
- **Export Engine Asinkron**: Penarikan ribuan baris data ke format Excel dan CSV diproses di latar belakang (*background job*) untuk menghindari kendala memori/timeout pada web server.
- **Pembersihan Berkas Unduhan**: Pembersih otomatis berkas ekspor lama yang kedaluwarsa secara terjadwal untuk menjamin kerahasiaan data perusahaan.

### 7. 📤 Modul Impor Data Master Massal
Infrastruktur pemrosesan data massal untuk mempermudah migrasi data operasional (Terletak pada menu **Master Data > Import Master Data**):
- **Dua Tahap Impor**: Proses validasi struktur dan data kotor di tahap pertama, dan penulisan transaksi database di tahap kedua setelah disetujui pengguna.
- **Progress Widget**: Widget pelacak kemajuan pemrosesan baris data secara real-time.
- **Dukungan Data Master**: Mendukung impor massal untuk data *Plants, Departments, Locations, Units, Items, dan Assets*.

### 8. 🎨 Modul Branding & Konfigurasi Global
Kustomisasi tampilan aplikasi agar sesuai dengan identitas korporat:
- **Logo Manager**: Pengaturan logo kustom terpisah untuk Kop Surat PDF, halaman login, header panel, dan favicon.
- **Branding Dictionary (Kamus Istilah)**: Pengaturan teks kustom secara global untuk istilah-istilah khusus perusahaan.
- **Preset Tema Hasnayeen Style**: Pengaturan visual skema warna panel per pengguna (*Default*, *Nord*, *Sunset*, *Forest*, *Dracula*, *Min*).

---ry*) untuk seluruh modul operasional.

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

## 🔄 Panduan Migrasi (Zero Error)

Bagi tim yang melakukan migrasi database atau deployment kode versi terbaru dari repositori agar berjalan lancar tanpa *error*:

1. **Jalankan Migrasi Database**:
   Lakukan perintah migrasi untuk memperbarui tabel `goods_releases` dengan kolom baru (`manual_release_number`):
   ```bash
   php artisan migrate --no-interaction
   ```
2. **Bersihkan Cache Aplikasi & Konfigurasi**:
   Agar rute cetak PDF Surat Jalan yang baru terdaftar dengan benar, jalankan:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   ```
3. **Build Ulang Aset Frontend**:
   Pastikan aset web dikompilasi ulang demi kelancaran antarmuka Filament:
   ```bash
   npm run build
   ```
4. **Jalankan Pengujian (Testing)**:
   Selalu jalankan pengujian fungsionalitas sebelum menaikkan perubahan ke production:
   ```bash
   php artisan test --compact
   ```

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

## 🔗 Informasi Repository & Layanan Pendukung

Untuk menjalankan ekosistem **E-SPPB Enterprise** secara penuh dengan seluruh fitur (termasuk gerbang notifikasi WhatsApp), diperlukan dua repositori/layanan terpisah berikut:

### 1. Repositori Aplikasi Utama (Laravel)
- **URL Repositori**: [`https://github.com/bayusasongko2407/e-sppb.git`](https://github.com/bayusasongko2407/e-sppb.git)
- **Branch Aktif**: `dev-beta`
- **Tujuan**: Menyimpan seluruh logic backend Laravel 12, antarmuka admin panel Filament v5, database migrations, workflow engine, PDF document generator, reporting, dan access control.

### 2. Layanan WhatsApp Open-WA Node.js Gateway
Aplikasi Laravel memerlukan WhatsApp server gateway Node.js yang berjalan terpisah agar pengiriman notifikasi dan pemindaian QR Code pairing WhatsApp pada panel Admin dapat bekerja dengan baik.
- **Repositori/Layanan Rujukan**: [`https://github.com/open-wa/wa-automate-nodejs`](https://github.com/open-wa/wa-automate-nodejs)
- **Langkah Menjalankan Server Gateway (Lokal/Development)**:
  ```bash
  # Menjalankan server gateway instan via npx
  npx @open-wa/wa-automate --port 3000 --api-key "API_SECRET_TOKEN_ANDA"
  ```
- **Konfigurasi Aplikasi**: Masukkan alamat server URL (`http://localhost:3000/send-message`) dan kunci rahasia API (`API_SECRET_TOKEN_ANDA`) pada menu **Pengaturan > Pengaturan Notifikasi** di Tab *WhatsApp (OpenWA Gateway)*.

---

- **Lisensi**: Hak Cipta Dilindungi — PT Santos Jaya Abadi / E-SPPB Enterprise.
