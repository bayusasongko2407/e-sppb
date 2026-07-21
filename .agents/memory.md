# AI Memory: Rules to Always Remember

Berikut adalah poin-poin memori jangka panjang yang wajib diingat sepanjang proyek **E-SPPB Enterprise**:

1.  **Arsitektur Bersih (Clean Architecture):**
    *   Selalu gunakan Service Layer (`app/Services/`) untuk logika kompleks.
    *   Controller dan Filament Resource Page hanya bertindak sebagai fasilitator input/output.
2.  **ID Masking & Security:**
    *   Ingat bahwa URL **dilarang mengekspos ID integer database** secara mentah.
    *   Selalu pasang `SecureRouteBinding` pada model Eloquent baru.
3.  **UI Bahasa Indonesia:**
    *   Seluruh teks UI, notifikasi sukses/gagal, label form, dan tabel wajib disajikan dalam **Bahasa Indonesia**.
4.  **Query Performa:**
    *   Waspada terhadap query N+1. Selalu eager-load relasi yang digunakan di tabel list Filament.
5.  **Authorization First:**
    *   Jangan membuat route atau modul baru tanpa melindunginya dengan Laravel Policy.
