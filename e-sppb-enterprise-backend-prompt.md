# [PROMPT BACKEND] Perbaikan Endpoint Status Goods Release & Respon Verifikasi Dokumen SPPB

Dokumentasi dan Spesifikasi Perbaikan Backend API Server (`https://e-sppb.engiboard.web.id/api/v1`) untuk aplikasi E-SPPB Enterprise.

---

### 1. Endpoint Konfirmasi Penerimaan Barang / Surat Jalan (`DELIVERED`)
**Permasalahan:**
Saat pengguna menekan tombol "Diterima / Konfirmasi Penerimaan" pada Surat Jalan (Goods Release) di aplikasi frontend/mobile, status barang harus berubah di database backend secara permanen dan mencatat log audit penerimaan.

**Spesifikasi Endpoint:**
* **Method & Route:** `POST /api/v1/goods-releases/{id_or_uuid}/receive`  
  *(Alias: `PATCH /api/v1/goods-releases/{id_or_uuid}/status`)*
* **Headers:** `Authorization: Bearer <token>`, `Content-Type: application/json`
* **Request Body:**
```json
{
  "status": "DELIVERED",
  "notes": "Barang telah diterima dalam kondisi baik",
  "receiving_notes": "Barang telah diterima dalam kondisi baik",
  "received_by_id": 12,
  "received_at": "2026-07-29T23:00:00Z"
}
```

**Logika Backend:**
1. Cari record di tabel `goods_releases` berdasarkan `id` (integer), `uuid`, `release_number`, `manual_release_number`, atau `verification_hash`.
2. Ubah kolom `status` menjadi `'DELIVERED'`.
3. Simpan catatan penerimaan ke kolom `notes` / `receiving_notes`, catat `received_at` dengan timestamp, dan `received_by_id`.
4. Buat entri di `sppb_status_logs` untuk audit trail.
5. Jika seluruh Surat Jalan pada SPPB terkait sudah diserahterimakan (`DELIVERED`), perbarui status `sppb_headers` terkait menjadi `'COMPLETED'`.
6. Return HTTP status 200 OK dengan payload:
```json
{
  "status": "success",
  "success": true,
  "message": "Surat Jalan berhasil dikonfirmasi diterima",
  "data": {
    "id": 1,
    "uuid": "e9b23f80-...",
    "release_number": "SJ-ENG-2026-0001",
    "status": "DELIVERED",
    "notes": "Barang telah diterima dalam kondisi baik",
    "updated_at": "2026-07-29T23:00:00Z"
  }
}
```

---

### 2. Kelengkapan Data Verifikasi Dokumen (`GET /v1/documents/verify/{hash}` & `GET /v1/sppb/{uuid}`)
**Permasalahan:**
Hasil pemindaian QR Code SPPB mengembalikan data yang tidak lengkap pada bagian Pemohon dan Lokasi Tujuan.

**Spesifikasi Perbaikan:**
Pastikan handler endpoint `GET /api/v1/documents/verify/{hash}` dan `GET /api/v1/sppb/{id_or_uuid}` melakukan relational join/eager loading untuk field berikut:
* **Pemohon (Requester):**
  * Objek `requester`: `{ "id": 1, "name": "Budi Santoso", "nik": "12345678" }`
  * Fallback key root: `"requester_name": "Budi Santoso"`, `"needed_name": "Budi Santoso"`
* **Lokasi Tujuan (Destination Location):**
  * Objek `destination_location`: `{ "id": 2, "code": "PLT1", "name": "Pabrik Sentul (Plant 1)" }`
  * Fallback key root: `"destination_location_name": "Pabrik Sentul (Plant 1)"`, `"destination_name": "Pabrik Sentul (Plant 1)"`
* **Departemen (Department):**
  * Objek `department`: `{ "id": 5, "code": "LOG", "name": "Departemen Logistik" }`
  * Fallback key root: `"department_name": "Departemen Logistik"`

---

### 3. Pencarian SPPB & Goods Release Berdasarkan UUID / Document Number / Integer ID
**Spesifikasi:**
Pastikan `GET /api/v1/sppb/{id_or_uuid}` dan `GET /api/v1/goods-releases/{id_or_uuid}` dapat menerima parameter berupa `id` (integer), `uuid` (string GUID), maupun `document_number` / `release_number` agar pemindaian QR Code dan navigasi detail selalu berhasil.
