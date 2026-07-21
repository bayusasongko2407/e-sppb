# Frozen Rules: Filament v5 Conventions

## 1. Resource Structure
*   **Separated Schemas:** Pindahkan Form Schema ke folder `Schemas/Form/` dan Table Schema ke folder `Tables/` menggunakan kelas terpisah untuk menjaga kebersihan file Resource utama.
*   **Bahasa Indonesia:** Semua label form, kolom tabel, aksi, notifikasi, dan widget wajib menggunakan Bahasa Indonesia.
*   **Authorization:** Gunakan Policy untuk melindungi halaman resource dan aksi. Dilarang melakukan hardcode pengecekan role di dalam menu atau button.

## 2. Forms & Tables UI
*   **Dropdown Dependency:** Gunakan parameter dropdown dependen yang responsif terhadap input sebelumnya (misal: memfilter Departemen setelah memilih Plant).
*   **Table Grouping:** Kelompokkan baris tabel menggunakan `groupBy` jika memuat data berulang untuk satu user/entitas, sediakan halaman detail (View) untuk melihat rincian lengkapnya.
*   **Delete Actions:** Aksi hapus tunggal dan massal (*Bulk Delete*) pada data terkelompok wajib disesuaikan agar membersihkan seluruh baris terkait dari database.
