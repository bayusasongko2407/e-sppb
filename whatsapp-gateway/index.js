const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode');
const cors = require('cors');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;
const API_KEY = process.env.API_KEY || '';
const EXECUTABLE_PATH = process.env.PUPPETEER_EXECUTABLE_PATH || null;

app.use(cors());
app.use(express.json());

let qrCodeData = null;
let clientStatus = 'INITIALIZING'; // INITIALIZING, QR_RECEIVED, AUTHENTICATING, READY, DISCONNECTED
let clientInfo = null;

// Puppeteer launch options
const puppeteerOptions = {
    headless: true,
    args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-accelerated-2d-canvas',
        '--no-first-run',
        '--no-zygote',
        '--disable-gpu',
        '--single-process'
    ]
};

if (EXECUTABLE_PATH) {
    puppeteerOptions.executablePath = EXECUTABLE_PATH;
}

// Initialize WhatsApp Web Client
const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: './.wwebjs_auth'
    }),
    puppeteer: puppeteerOptions
});

client.on('qr', (qr) => {
    clientStatus = 'QR_RECEIVED';
    qrcode.toDataURL(qr, (err, url) => {
        if (!err) {
            qrCodeData = url;
        }
    });
    console.log(' [wwebjs] QR Code baru diterima. Silakan scan di Dashboard E-SPPB.');
});

client.on('authenticated', () => {
    clientStatus = 'AUTHENTICATING';
    qrCodeData = null;
    console.log(' [wwebjs] Autentikasi berhasil, memuat sesi...');
});

client.on('ready', () => {
    clientStatus = 'READY';
    qrCodeData = null;
    clientInfo = client.info;
    console.log(' [wwebjs] Client WhatsApp Web SIAP terhubung!');
});

client.on('disconnected', (reason) => {
    clientStatus = 'DISCONNECTED';
    qrCodeData = null;
    clientInfo = null;
    console.log(` [wwebjs] Client terputus: ${reason}`);
});

// Middleware API Key Verification
const verifyApiKey = (req, res, next) => {
    if (!API_KEY) return next();
    const requestKey = req.headers['x-api-key'] || req.query.api_key;
    if (requestKey !== API_KEY) {
        return res.status(401).json({ success: false, message: 'Unauthorized: Invalid API Key' });
    }
    next();
};

// Endpoint Check Status & QR
app.get('/status', verifyApiKey, (req, res) => {
    const isConnected = clientStatus === 'READY';
    return res.json({
        success: true,
        connected: isConnected,
        status: clientStatus,
        qr_code: qrCodeData,
        message: isConnected 
            ? `Terhubung sebagai ${clientInfo?.pushname || 'Bot'} (${clientInfo?.wid?.user || ''})`
            : clientStatus === 'QR_RECEIVED' 
                ? 'Scan QR Code untuk menghubungkan WhatsApp.' 
                : `Status Gateway: ${clientStatus}`
    });
});

// Endpoint Send Message
app.post('/send-message', verifyApiKey, async (req, res) => {
    try {
        const { number, text, chatId } = req.body;
        let target = chatId;

        if (!target && number) {
            let cleaned = number.replace(/[^0-9]/g, '');
            if (cleaned.startsWith('0')) {
                cleaned = '62' + cleaned.slice(1);
            }
            target = cleaned.includes('@c.us') ? cleaned : `${cleaned}@c.us`;
        }

        if (!target) {
            return res.status(400).json({ success: false, message: 'Nomor penerima (number/chatId) wajib diisi.' });
        }

        if (clientStatus !== 'READY') {
            return res.status(503).json({ success: false, message: `WhatsApp Gateway belum siap. Status saat ini: ${clientStatus}` });
        }

        const response = await client.sendMessage(target, text);
        return res.json({
            success: true,
            messageId: response.id._serialized,
            message: 'Pesan WhatsApp berhasil dikirim.'
        });
    } catch (error) {
        console.error(' Error sending WA message:', error);
        return res.status(500).json({ success: false, message: `Gagal mengirim pesan: ${error.message}` });
    }
});

// Endpoint Restart Client
app.post('/restart', verifyApiKey, async (req, res) => {
    try {
        clientStatus = 'INITIALIZING';
        qrCodeData = null;
        await client.destroy();
        client.initialize();
        return res.json({ success: true, message: 'Client whatsapp-web.js sedang di-restart.' });
    } catch (error) {
        return res.status(500).json({ success: false, message: error.message });
    }
});

client.initialize().catch((err) => {
    console.error('❌ Gagal menginisialisasi Puppeteer/WhatsApp Client:', err.message);
    console.error('💡 Solusi: Silakan install dependensi Chromium di server Ubuntu dengan perintah:');
    console.error('   sudo apt-get update && sudo apt-get install -y libxkbcommon0 libglib2.0-0 libnss3 libatk1.0-0 libatk-bridge2.0-0 libcups2 libdrm2 libxcomposite1 libxdamage1 libxrandr2 libgbm1 libasound2');
});

app.listen(PORT, () => {
    console.log(`🚀 E-SPPB whatsapp-web.js Gateway berjalan pada http://localhost:${PORT}`);
});
