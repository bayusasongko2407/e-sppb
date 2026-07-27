# 📦 E-SPPB Enterprise

> **Elektronik Surat Permintaan & Pelepasan Barang (E-SPPB)** — Sistem manajemen pengajuan, otorisasi berjenjang (*multi-stage approval*), pelepasan barang (surat jalan), serta gateway notifikasi multi-saluran berbasis **Laravel 12** dan **Filament v5**.

---

## 📋 Daftar Isi
1. [Gambaran Umum](#-gambaran-umum)
2. [Teknologi Utama (Tech Stack)](#-teknologi-utama-tech-stack)
3. [Fitur-Fitur Utama](#-fitur-fitur-utama)
4. [Persyaratan & Layanan Tambahan (Zero Error)](#-persyaratan--layanan-tambahan-zero-error)
5. [Panduan Instalasi & Deployment Produksi](#-panduan-instalasi--deployment-produksi)
6. [Daftar Akun Simulasi & Seeder Bawaan](#-daftar-akun-simulasi--seeder-bawaan)
7. [Pengujian & Gate Kualitas](#-pengujian--gate-kualitas)
8. [Informasi Repository & Branching](#-informasi-repository--branching)

---

## 🌟 Gambaran Umum

**E-SPPB Enterprise** dirancang untuk mengotomatiskan dan memperketat alur kerja pengajuan barang, otorisasi verifikasi berjenjang, hingga pelepasan barang di lingkungan manufaktur multi-plant. Aplikasi ini menerapkan pola hirarki **Plant-based scoping** di mana `Plant` adalah entitas organisasi tertinggi tanpa ketergantungan scope Company.

Seluruh transaksi dilindungi dengan penguncian data (*pessimistic locking* `lockForUpdate()`), log audit status (*SppbStatusLog*), token verifikasi SHA256, serta notifikasi terpadu melalui **Lonceng In-App**, **Email SMTP**, dan **WhatsApp OpenWA Gateway**.

---

## 🛠️ Teknologi Utama (Tech Stack)

| Komponen | Teknologi / Library | Versi |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP | `^8.3` |
| **Framework Utama** | Laravel Framework | `v12.x` |
| **Admin Panel UI** | Filament Admin | `v5.x` |
| **Frontend Reactive** | Livewire | `v4.x` |
| **Styling & CSS** | Tailwind CSS | `v4.x` |
| **Database Engine** | MariaDB / MySQL | `10.11+` / `8.0+` |
| **Role & Permission** | Spatie Laravel Permission | `v6.x` |
| **WA Gateway Engine** | OpenWA Node.js Gateway | Integration REST API |

---

## 🚀 Fitur-Fitur Utama

### 1. 📄 Modul Pengajuan SPPB (Surat Permintaan Pengeluaran Barang)
- **Formulir Input Adaptif**: Antarmuka dinamis di mana masukan *Barcode/Kode* otomatis disembunyikan jika memilih jenis barang **Non-Aset**, secara responsif melebarkan masukan *Nama Barang*.
- **Dropdown Search & Autocomplete**: Pilihan nama barang terhubung ke Master Data untuk standarisasi penulisan, tetapi tetap membolehkan pengetikan teks kustom secara bebas (non-master).
- **Auto-Reselect Master Data**: Memetakan kode barang dan satuan secara otomatis jika input teks bebas cocok dengan data master.
- **Pencegahan Redundansi Alur**: Logika bisnis melarang pengajuan SPPB jika Lokasi Asal dan Lokasi Tujuan bernilai sama.
- **Auto Running Number Generator**: Format penomoran dokumen otomatis terisolasi secara unik per Pabrik (Plant) dan periode waktu berjalan.
- **Pessimistic Locking & Safety Transaction**: Setiap perubahan status dokumen diproses dalam blok `DB::transaction()` dengan penguncian `lockForUpdate()`.
- **Ekspor Resmi PDF & Public Verification QR Code**:
  - Cetak dokumen PDF resmi dengan Kop Surat, rincian barang, dan riwayat *Approval Timeline*.
  - Dilengkapi **QR Code Validasi** berbasis token kriptografi SHA256 (`verification_sha256_token`).
  - Halaman verifikasi publik tanpa login (`verify.blade.php`) dilindungi *Rate-limiting* ketat serta mencatat metadata audit (IP address, browser fingerprint, timestamp).

### 2. ⚡ Modul Multi-Stage Workflow Approval Engine
- **Template Approval Dinamis**: Konfigurasi urutan persetujuan berjenjang (misal: Step 1 `Manager` $\rightarrow$ Step 2 `BAT`) yang disesuaikan per departemen dan jenis dokumen.
- **Mode Persetujuan Fleksibel**:
  - `ANY`: Cukup salah satu dari pemegang jabatan yang menyetujui.
  - `ALL`: Seluruh pejabat yang terdaftar wajib menyetujui secara kolektif.
  - `Quorum`: Persetujuan sah jika memenuhi kuorum minimum tertentu.
- **Matriks Tindakan Penyetuju (Approval Actions)**:
  - *Setujui (Approve)*: Meneruskan dokumen ke tingkat berikutnya.
  - *Tolak (Reject)*: Menghentikan dokumen secara permanen dan mencatat alasan penolakan.
  - *Revisi (Request Revision)*: Mengembalikan dokumen ke pemohon untuk diedit tanpa membatalkan proses dokumen secara keseluruhan.
- **Delegasi Wewenang (Workflow Delegation)**: Pelimpahan otoritas persetujuan sementara (misal: saat cuti) dengan validasi masa aktif otomatis dan proteksi *circular-loop guard*.

### 3. 🚚 Modul Surat Jalan & Pelepasan Barang (Goods Release / SAT)
- **Penerbitan Surat Jalan Resmi**: Pengeluaran berkas Surat Jalan (*Goods Release*) berdasarkan dokumen SPPB yang telah disetujui penuh (*Final Approved*).
- **Cetak PDF & Verifikasi QR**: Penerbitan dokumen cetak fisik Surat Jalan resmi dengan Kop Surat, rincian pengiriman, tanda tangan persetujuan, dan QR Code verifikasi.
- **Dua Desain Penomoran Dinamis**:
  - *Surat Jalan Otomatis*: Penomoran sistem utama.
  - *Surat Jalan Manual*: Input nomor manual fisik yang dijadikan nomor utama dokumen, sementara nomor otomatis menjadi *No. Referensi*.
- **Konsolidasi Multi-SPPB**: Penggabungan beberapa dokumen SPPB aktif dari lokasi yang sama ke dalam 1 Surat Jalan.
- **Over-Release Protection**: Validasi ketat kuantitas rilis fisik terhadap sisa kuantitas pengajuan SPPB.
- **Pelacakan Status Rilis**: Transisi status otomatis dari `APPROVED` $\rightarrow$ `RELEASE_IN_PROGRESS` $\rightarrow$ `COMPLETED`.

### 4. 🔔 Modul Pengaturan Notifikasi Terpadu (3 Tabs)
- **Tab 1: Notifikasi Sistem (In-App Lonceng)**: Master Switch, durasi retensi penyimpanan log (`30` / `60` / `90` hari) yang dibersihkan otomatis via scheduler, dan checklist matriks event.
- **Tab 2: Notifikasi Email (SMTP Mail Setup)**: Master Switch Email, Driver, Host, Port, Username, Password, dan Action Button *"Kirim Email Uji Coba"*.
- **Tab 3: Notifikasi WhatsApp (OpenWA Gateway)**: Master Switch WA, Server URL, API Secret Token Header, Live Status Viewer (`CONNECTED`/`DISCONNECTED`), QR Code Pairing Viewer, dan Action Button *"Kirim WA Uji Coba"*.

### 5. 🔒 Modul Otorisasi Keamanan & Kebijakan Hak Akses
- **Role & Permission Granular**: Otorisasi Spatie Permission (`super_admin`, `admin`, `Pemohon`, `approver`, `manager`).
- **Matriks Hak Akses Dokumen (DocumentAccesses)**: Isolasi ketat operasi *View, Create, Edit, Delete* per Plant, Department, dan Modul.
- **Lockout Brute Force Protection**: Memblokir akun selama 15 menit jika gagal login 5 kali berturut-turut.
- **Reset Sandi Otomatis**: Generator kata sandi acak ramah pengguna dalam Bahasa Indonesia bagi Super Admin.

### 6. 📊 Modul Pelaporan & Impor Massal
- **Reports Engine**: Penarikan laporan SPPB dan Surat Jalan ke format Excel dan CSV diproses di latar belakang (*background job*) dengan pembersihan berkas kedaluwarsa otomatis.
- **Mass Data Import**: Impor massal dua tahap (Upload $\rightarrow$ Validate $\rightarrow$ Commit) dengan Progress Widget pelacak real-time untuk data Plants, Departments, Locations, Units, Items, dan Assets.

---

## ⚡ Persyaratan & Layanan Tambahan (Zero Error)

Agar aplikasi dapat beroperasi sempurna tanpa kendala (*Zero Error*) pada lingkungan produksi, pastikan komponen berikut telah terpasang dan terkonfigurasi:

### 1. Ekstensi PHP Wajib
Pastikan ekstensi PHP berikut aktif di server:
- `pdo_mysql` / `pdo` (Koneksi database)
- `mbstring` & `openssl` (Enkripsi & token SHA256)
- `bcmath` (Kalkulasi decimal presisi tinggi)
- `curl` (Koneksi HTTP REST API ke WA Gateway)
- `gd` / `imagick` (Pemrosesan gambar logo & QR code)
- `zip` (Kompresi file ekspor & impor massal)
- `fileinfo` (Validasi MIME type lampiran)

### 2. Process Manager (Supervisor - Queue Worker)
Gunakan **Supervisor** di Linux untuk menjalankan antrean pekerjaan latar belakang (*queue worker*) secara terus-menerus:

Buat file `/etc/supervisor/conf.d/esppb-worker.conf`:
```ini
[program:esppb-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /www/wwwroot/e-sppb-enterprise/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/www/wwwroot/e-sppb-enterprise/storage/logs/worker.log
```
Jalankan Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start esppb-worker:*
```

### 3. Task Scheduler (Cron Job)
Tambahkan entry berikut pada crontab server untuk pembersihan log & file ekspor otomanis:
```bash
* * * * * cd /www/wwwroot/e-sppb-enterprise && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Layanan WhatsApp OpenWA Node.js Gateway
Notifikasi WhatsApp membutuhkan server gateway Node.js yang berjalan di port `3000`:
- **Menjalankan via PM2**:
  ```bash
  npm install -g pm2
  pm2 start "npx @open-wa/wa-automate --port 3000 --api-key API_SECRET_TOKEN_ANDA" --name "wa-gateway"
  ```
- Masukkan URL `http://127.0.0.1:3000/send-message` dan API Secret Token pada menu **Pengaturan > Pengaturan Notifikasi** di Admin Panel.

---

## ⚙️ Panduan Instalasi & Deployment Produksi

### 1. Clone & Setup Dependensi
```bash
# 1. Clone repositori
git clone https://github.com/bayusasongko2407/e-sppb.git
cd e-sppb

# 2. Switch ke branch main
git checkout main

# 3. Install dependensi PHP
composer install --no-dev --optimize-autoloader

# 4. Salin & sesuaikan lingkungan .env
cp .env.example .env

# 5. Generate Application Key
php artisan key:generate
```

### 2. Konfigurasi Database & Seeders
Sesuaikan kredensial database di `.env`, lalu jalankan migrasi dan seeder lengkap:
```bash
# Jalankan migrasi & seeder master data
php artisan migrate --seed --force
```

### 3. Kompilasi Aset Frontend
```bash
npm install
npm run build
```

### 4. Optimasi Performa Produksi
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 🔑 Daftar Akun Simulasi & Seeder Bawaan

Setelah eksekusi `php artisan db:seed`, sistem secara otomatis membuat akun simulasi per plant dengan kata sandi default `password`:

| Email | Nama Akun | Role | Posisi | Plant |
| :--- | :--- | :--- | :--- | :--- |
| `superadmin@esppb.local` | Super Admin | `super_admin` | - | Global |
| `requester.sjaspj@esppb.local` | Staff ENG Sepanjang | `Pemohon` | `STAFF` | Sepanjang Plant |
| `manager.sjaspj@esppb.local` | Manager ENG Sepanjang | `manager` | `MGR` | Sepanjang Plant |
| `bat.sjaspj@esppb.local` | BAT Sepanjang | `approver` | `BAT` | Sepanjang Plant |
| `gudang.sjaspj@esppb.local` | Gudang Sepanjang | `approver` | `STAFF` | Sepanjang Plant |
| `requester.sjakrw@esppb.local` | Staff ENG Karawang | `Pemohon` | `STAFF` | Karawang Plant |
| `manager.sjakrw@esppb.local` | Manager ENG Karawang | `manager` | `MGR` | Karawang Plant |
| `bat.sjakrw@esppb.local` | BAT Karawang | `approver` | `BAT` | Karawang Plant |
| `gudang.sjakrw@esppb.local` | Gudang Karawang | `approver` | `STAFF` | Karawang Plant |

---

## 🧪 Pengujian & Gate Kualitas

Jalankan pengujian ketersediaan fungsionalitas dan kualitas kode dengan perintah berikut:

```bash
# 1. Format kode Pint
vendor/bin/pint --format agent

# 2. Analisis Statis PHPStan
vendor/bin/phpstan analyse app/ --memory-limit=2G

# 3. Jalankan 106 Unit & Feature Test Suites
php artisan test --compact
```

---

## 🔗 Informasi Repository & Branching

*   **Repository URL**: [`https://github.com/bayusasongko2407/e-sppb.git`](https://github.com/bayusasongko2407/e-sppb.git)
*   **Branch Utama (Produksi)**: `main`
*   **Branch Pengembangan**: `dev-beta`
*   **Lisensi**: Hak Cipta Dilindungi — PT Santos Jaya Abadi / E-SPPB Enterprise.
