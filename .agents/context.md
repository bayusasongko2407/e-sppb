# Project Context: E-SPPB Enterprise

## 1. Project Overview
E-SPPB Enterprise adalah sistem berbasis web untuk mengelola Surat Permohonan Pengiriman Barang (SPPB), workflow persetujuan bertingkat (approvals), pelepasan barang (Goods Release / Surat Jalan), verifikasi keaslian dokumen via QR-code, dan sinkronisasi data master antar departemen/plant.

## 2. Tech Stack
*   **Backend:** PHP 8.3 & Laravel 12
*   **Frontend UI:** Filament v5 (TailwindCSS v4, AlpineJS, Livewire v4)
*   **Database:** MariaDB 10.11
*   **Cache & Queue:** Redis
*   **Server:** Nginx di Ubuntu Server

## 3. Business Domains
*   **Plant & Department Scope:** Pengguna terasosiasi dengan Plant dan Departemen tertentu. Akses dokumen disaring berdasarkan kewenangan lokasi kerja.
*   **Workflow Engine:** Setiap dokumen SPPB melewati alur persetujuan bertingkat (BAT -> KADEP -> KADIV -> dll.).
*   **Security (ID Masking):** Semua route parameter ID primer menggunakan enkripsi simetris (AES-256) untuk mencegah IDOR.

## 4. Architecture Standards
*   **Service Layer:** Logika bisnis ditaruh di `App\Services\` (contoh: `SppbService`, `WorkflowService`).
*   **Action Pattern:** Logika tunggal mutasi status ditaruh di `App\Actions\`.
*   **Policy & Permission:** Keamanan otorisasi menggunakan Laravel Policies dan Spatie Laravel Permission.
