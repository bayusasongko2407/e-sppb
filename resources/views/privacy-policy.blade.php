<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi - E-SPPB Enterprise</title>
    <meta name="description" content="Kebijakan Privasi Penggunaan Aplikasi E-SPPB Enterprise dan Layanan Notifikasi WhatsApp & Email.">
    <meta name="robots" content="index, follow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #475569;
            --border: #e2e8f0;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0f172a;
                --card-bg: #1e293b;
                --text-main: #f8fafc;
                --text-muted: #94a3b8;
                --border: #334155;
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            line-height: 1.7;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 840px;
            margin: 0 auto;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .header {
            border-bottom: 2px solid var(--border);
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .section {
            margin-bottom: 2rem;
        }

        .section h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section p, .section ul {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
        }

        .section ul {
            list-style-type: disc;
            padding-left: 1.5rem;
        }

        .section ul li {
            margin-bottom: 0.35rem;
        }

        .footer {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: rgba(2, 132, 199, 0.1);
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="badge">E-SPPB ENTERPRISE</span>
            <h1>Kebijakan Privasi (Privacy Policy)</h1>
            <p>Terakhir diperbarui: {{ date('d F Y') }}</p>
        </div>

        <div class="section">
            <h2>1. Pendahuluan</h2>
            <p>Selamat datang di sistem **E-SPPB Enterprise** (Surat Permintaan Pengeluaran Barang Enterprise). Kami berkomitmen untuk melindungi privasi dan keamanan data pribadi pengguna kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan melindungi informasi Anda saat menggunakan aplikasi E-SPPB Enterprise beserta integrasi layanannya (termasuk notifikasi Email dan WhatsApp Business API).</p>
        </div>

        <div class="section">
            <h2>2. Informasi yang Kami Kumpulkan</h2>
            <p>Dalam pengoperasian aplikasi E-SPPB Enterprise, kami dapat mengumpulkan jenis informasi berikut:</p>
            <ul>
                <li><strong>Informasi Identitas & Kontak:</strong> Nama lengkap, alamat email operasional, dan nomor telepon/WhatsApp aktif pengguna.</li>
                <li><strong>Data Transaksi & Dokumen:</strong> Nomor dokumen SPPB, rincian barang/material, catatan persetujuan (approval), dan status verifikasi Surat Jalan.</li>
                <li><strong>Data Perangkat & Log Akses:</strong> Alamat IP, jenis peramban (browser), waktu akses, dan log aktivitas persetujuan sistem untuk kepentingan jejak audit (*audit trail*).</li>
            </ul>
        </div>

        <div class="section">
            <h2>3. Penggunaan Informasi</h2>
            <p>Informasi yang dikumpulkan digunakan semata-mata untuk mendukung operasional sistem E-SPPB Enterprise, antara lain:</p>
            <ul>
                <li>Mengirimkan notifikasi transaksional real-time mengenai pengajuan SPPB, permintaan persetujuan, revisi, dan penerbitan Surat Jalan melalui Email dan WhatsApp.</li>
                <li>Mengotentikasi identitas pengguna dan mengontrol hak akses berbasis peran (*Role-Based Access Control*).</li>
                <li>Memfasilitasi proses verifikasi keaslian dokumen melalui token keamanan unik SHA-256.</li>
                <li>Menjaga integritas data transaksi dan memenuhi standar audit keamanan perusahaan.</li>
            </ul>
        </div>

        <div class="section">
            <h2>4. Integrasi Pihak Ketiga & WhatsApp Business API</h2>
            <p>Aplikasi E-SPPB Enterprise terintegrasi dengan layanan resmi pihak ketiga untuk pengiriman notifikasi:</p>
            <ul>
                <li><strong>Meta WhatsApp Business Cloud API:</strong> Nomor WhatsApp pengguna digunakan khusus untuk mengirimkan notifikasi status transaksi. Kami tidak menjual, menyewakan, atau membagikan data nomor telepon ke pihak ketiga manapun untuk tujuan pemasaran/komersial.</li>
                <li><strong>Layanan Email SMTP / Resend API:</strong> Digunakan untuk pengiriman surel notifikasi resmi sistem.</li>
            </ul>
        </div>

        <div class="section">
            <h2>5. Perlindungan & Keamanan Data</h2>
            <p>Kami menerapkan tindakan teknis dan organisasional yang ketat untuk melindungi data Anda dari akses, perubahan, pengungkapan, atau penghancuran yang tidak sah, termasuk:</p>
            <ul>
                <li>Enkripsi data saat transit menggunakan protokol SSL/TLS HTTPS.</li>
                <li>Penyimpanan kata sandi menggunakan algoritma Hashing standar industri (*Bcrypt*).</li>
                <li>Penguncian data transaksi (*Pessimistic Data Locking*) untuk mencegah manipulasi data bersamaan.</li>
                <li>Akses terbatas hanya kepada personil terotorisasi sesuai dengan hak akses peran masing-masing.</li>
            </ul>
        </div>

        <div class="section">
            <h2>6. Retensi Data & Hak Pengguna</h2>
            <p>Kami menyimpan data pribadi Anda selama akun Anda aktif atau selama diperlukan untuk memenuhi kewajiban operasional dan hukum. Pengguna memiliki hak untuk:</p>
            <ul>
                <li>Meminta pembaruan atau koreksi data profil dan nomor telepon/WhatsApp.</li>
                <li>Meminta informasi mengenai riwayat pengolahan data pribadi mereka.</li>
                <li>Mengajukan permohonan penonaktifan akun atau penghapusan data kontak apabila sudah tidak lagi terikat dengan operasional perusahaan.</li>
            </ul>
        </div>

        <div class="section">
            <h2>7. Hubungi Kami</h2>
            <p>Jika Anda memiliki pertanyaan, saran, atau permintaan terkait Kebijakan Privasi ini, Anda dapat menghubungi tim administrator sistem kami melalui:</p>
            <ul>
                <li><strong>Email:</strong> admin@esppb.perusahaan.com</li>
                <li><strong>Aplikasi:</strong> E-SPPB Enterprise Platform Admin Panel</li>
            </ul>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} E-SPPB Enterprise. Seluruh hak cipta dilindungi undang-undang.</p>
        </div>
    </div>
</body>
</html>
