# E-SPPB WhatsApp Web.js Microservice Gateway

Gateway WhatsApp ini dibangun menggunakan pustaka [`whatsapp-web.js`](https://github.com/wwebjs/whatsapp-web.js) dan Express Node.js REST API.

## Cara Menggunakan

1. **Masuk ke folder gateway:**
   ```bash
   cd whatsapp-gateway
   ```

2. **Install Dependensi:**
   ```bash
   npm install
   ```

3. **Konfigurasi Environment (`.env`):**
   ```env
   PORT=3000
   API_KEY=
   ```

4. **Jalankan Service:**
   ```bash
   npm start
   ```
   Atau untuk pengujian/development:
   ```bash
   npm run dev
   ```

5. **Integrasi dengan E-SPPB Enterprise:**
   - Buka panel Filament E-SPPB (`/admin/notification-settings`).
   - Di tab **Pengaturan Notifikasi**, masukkan URL: `http://127.0.0.1:3000/send-message` (atau host tempat service dijalankan).
   - Bila `API_KEY` diisi pada `.env`, masukkan token tersebut pada bidang **API Secret Token Header** di Filament.
   - Pindai (scan) QR Code yang muncul di Filament Admin menggunakan aplikasi WhatsApp pada ponsel Anda.
