<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Resmi - E-SPPB Enterprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.15);
            --warning: #f59e0b;
            --warning-glow: rgba(245, 158, 11, 0.15);
            --danger: #ef4444;
            --danger-glow: rgba(239, 68, 68, 0.15);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            background-image: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.1) 0%, transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.08) 0%, transparent 40%);
        }

        .container {
            width: 100%;
            max-width: 480px;
            perspective: 1000px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 32px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            margin-bottom: 24px;
        }

        .company-name {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .system-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        /* Status Styles */
        .status-badge-container {
            margin: 24px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .status-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            position: relative;
        }

        .status-icon svg {
            width: 36px;
            height: 36px;
        }

        .status-title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .status-desc {
            font-size: 13px;
            color: var(--text-secondary);
            max-width: 280px;
            line-height: 1.5;
        }

        /* Valid */
        .status-VALID .status-icon {
            background-color: var(--success-glow);
            border: 2px solid var(--success);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
        }
        .status-VALID .status-icon svg { color: var(--success); }
        .status-VALID .status-title { color: var(--success); }

        /* Warning/Superseded/Expired */
        .status-SUPERSEDED .status-icon,
        .status-EXPIRED .status-icon {
            background-color: var(--warning-glow);
            border: 2px solid var(--warning);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.3);
        }
        .status-SUPERSEDED .status-icon svg,
        .status-EXPIRED .status-icon svg { color: var(--warning); }
        .status-SUPERSEDED .status-title,
        .status-EXPIRED .status-title { color: var(--warning); }

        /* Danger/Revoked/NotFound */
        .status-REVOKED .status-icon,
        .status-NOT_FOUND .status-icon {
            background-color: var(--danger-glow);
            border: 2px solid var(--danger);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
        }
        .status-REVOKED .status-icon svg,
        .status-NOT_FOUND .status-icon svg { color: var(--danger); }
        .status-REVOKED .status-title,
        .status-NOT_FOUND .status-title { color: var(--danger); }

        /* Details */
        .details-list {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            text-align: left;
            margin-bottom: 24px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            font-size: 13px;
        }

        .detail-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .detail-item:first-child {
            padding-top: 0;
        }

        .detail-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .detail-value {
            color: var(--text-primary);
            font-weight: 600;
            text-align: right;
            padding-left: 15px;
        }

        .footer {
            font-size: 11px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .footer a {
            color: #6366f1;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card status-{{ $status }}">
            <div class="header">
                <div class="company-name">PT Santos Jaya Abadi</div>
                <h1 class="system-title">Verifikasi Dokumen Resmi</h1>
            </div>

            <div class="status-badge-container">
                <div class="status-icon">
                    @if($status === 'VALID')
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    @elseif($status === 'SUPERSEDED')
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    @elseif($status === 'EXPIRED')
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @elseif($status === 'REVOKED')
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    @endif
                </div>

                <div class="status-title">
                    @if($status === 'VALID')
                        DOKUMEN ASLI & VALID
                    @elseif($status === 'SUPERSEDED')
                        DOKUMEN TELAH DIPERBARUI
                    @elseif($status === 'EXPIRED')
                        DOKUMEN KADALUARSA
                    @elseif($status === 'REVOKED')
                        DOKUMEN TELAH DICABUT
                    @else
                        DOKUMEN TIDAK DITEMUKAN
                    @endif
                </div>

                <div class="status-desc">
                    @if($status === 'VALID')
                        Dokumen ini sah, terdaftar resmi di sistem E-SPPB, dan tidak mengalami perubahan isi.
                    @elseif($status === 'SUPERSEDED')
                        Versi dokumen baru telah dirilis untuk SPPB ini. Dokumen versi lama ini sudah tidak berlaku.
                    @elseif($status === 'EXPIRED')
                        Masa berlaku dokumen ini telah habis dan tidak dapat digunakan kembali.
                    @elseif($status === 'REVOKED')
                        Dokumen ini telah dicabut secara manual oleh administrator dan tidak berlaku lagi.
                    @else
                        Kode QR tidak valid atau dokumen tidak terdaftar dalam basis data sistem resmi kami.
                    @endif
                </div>
            </div>

            @if($data)
                <div class="details-list">
                    <div class="detail-item">
                        <span class="detail-label">Jenis Dokumen</span>
                        <span class="detail-value">{{ strtoupper($data['document_type']) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Nomor Dokumen</span>
                        <span class="detail-value">{{ $data['document_number'] }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Pabrik (Plant)</span>
                        <span class="detail-value">{{ $data['plant_name'] }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Halaman</span>
                        <span class="detail-value">{{ $data['page_number'] }} dari {{ $data['total_pages'] }}</span>
                    </div>
                    @if(isset($data['generated_at']))
                        <div class="detail-item">
                            <span class="detail-label">Tanggal Dibuat</span>
                            <span class="detail-value">{{ \Carbon\Carbon::parse($data['generated_at'])->translatedFormat('d F Y H:i') }} WIB</span>
                        </div>
                    @endif
                    <div class="detail-item">
                        <span class="detail-label">Digital Fingerprint</span>
                        <span class="detail-value" style="font-family: monospace; letter-spacing: 0.5px;">SHA256: {{ $data['fingerprint'] }}</span>
                    </div>
                </div>
            @endif

            <div class="footer">
                ID Validasi: <span style="font-family: monospace; color: var(--text-primary);">{{ $validation_id }}</span><br>
                @if(isset($sha256_token))
                <span style="display: block; margin-top: 8px; font-size: 10px; word-break: break-all; font-family: monospace; color: var(--text-secondary);">
                    Token: {{ $sha256_token }}
                </span>
                @endif
                <span style="display: block; margin-top: 12px; color: var(--text-secondary);">
                    &copy; 2026 E-SPPB Enterprise. Hak Cipta Dilindungi.
                </span>
            </div>
        </div>
    </div>
</body>
</html>
