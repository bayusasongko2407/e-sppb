<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Resmi - E-SPPB Enterprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --success: #059669;
            --success-bg: #ecfdf5;
            --success-border: #a7f3d0;
            --warning: #d97706;
            --warning-bg: #fffbeb;
            --warning-border: #fde68a;
            --danger: #dc2626;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --indigo-accent: #4f46e5;
            --indigo-bg: #eef2ff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(5, 150, 105, 0.04) 0px, transparent 50%);
        }

        .container {
            width: 100%;
            max-width: 520px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 32px 28px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px dashed var(--border-color);
        }

        .company-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--indigo-accent);
            background: var(--indigo-bg);
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 8px;
        }

        .system-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.3px;
        }

        /* Status Section */
        .status-box {
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .status-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .status-icon svg {
            width: 28px;
            height: 28px;
        }

        .status-title {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.2px;
            margin-bottom: 4px;
        }

        .status-desc {
            font-size: 13px;
            line-height: 1.5;
            max-width: 380px;
        }

        /* Status Variants */
        .status-VALID .status-box {
            background-color: var(--success-bg);
            border: 1px solid var(--success-border);
        }
        .status-VALID .status-icon {
            background-color: #d1fae5;
            color: var(--success);
        }
        .status-VALID .status-title { color: var(--success); }
        .status-VALID .status-desc { color: #047857; }

        .status-SUPERSEDED .status-box,
        .status-EXPIRED .status-box {
            background-color: var(--warning-bg);
            border: 1px solid var(--warning-border);
        }
        .status-SUPERSEDED .status-icon,
        .status-EXPIRED .status-icon {
            background-color: #fef3c7;
            color: var(--warning);
        }
        .status-SUPERSEDED .status-title,
        .status-EXPIRED .status-title { color: var(--warning); }
        .status-SUPERSEDED .status-desc,
        .status-EXPIRED .status-desc { color: #b45309; }

        .status-REVOKED .status-box,
        .status-NOT_FOUND .status-box {
            background-color: var(--danger-bg);
            border: 1px solid var(--danger-border);
        }
        .status-REVOKED .status-icon,
        .status-NOT_FOUND .status-icon {
            background-color: #fee2e2;
            color: var(--danger);
        }
        .status-REVOKED .status-title,
        .status-NOT_FOUND .status-title { color: var(--danger); }
        .status-REVOKED .status-desc,
        .status-NOT_FOUND .status-desc { color: #b91c1c; }

        /* Section Layout */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-grid {
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-row:first-child {
            padding-top: 0;
        }

        .label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .value {
            color: var(--text-primary);
            font-weight: 600;
            text-align: right;
        }

        .value-highlight {
            color: var(--indigo-accent);
            font-weight: 700;
        }

        /* Approval Timeline */
        .approval-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .approval-item {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }

        .approval-role {
            font-weight: 700;
            color: var(--text-primary);
        }

        .approval-status {
            font-size: 12px;
            font-weight: 700;
            color: var(--success);
            background: var(--success-bg);
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid var(--success-border);
        }

        /* Security Badges */
        .security-badge {
            background: #f8fafc;
            border: 1px dashed var(--border-color);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        .security-badge code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            color: var(--text-primary);
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card status-{{ $status }}">
            <!-- Header -->
            <div class="header">
                <div class="company-tag">PT Santos Jaya Abadi</div>
                <h1 class="system-title">Verifikasi Dokumen SPPB</h1>
            </div>

            <!-- Status Banner -->
            <div class="status-box">
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
                        DOKUMEN ASLI &amp; VALID
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
                        Dokumen ini terdaftar resmi dalam sistem E-SPPB Enterprise, terverifikasi sah, dan tidak mengalami modifikasi.
                    @elseif($status === 'SUPERSEDED')
                        Telah terbit versi baru untuk dokumen SPPB ini. Dokumen versi lama ini tidak dapat dipergunakan kembali.
                    @elseif($status === 'EXPIRED')
                        Masa berlaku verifikasi dokumen ini telah habis. Silakan hubungi bagian administrasi.
                    @elseif($status === 'REVOKED')
                        Dokumen ini telah dicabut secara resmi dan dinyatakan tidak berlaku.
                    @else
                        Kode QR tidak terdaftar dalam basis data resmi E-SPPB.
                    @endif
                </div>
            </div>

            @if($data)
                <!-- Ringkasan SPPB -->
                <div class="section-title">📄 Informasi SPPB</div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="label">Jenis Dokumen</span>
                        <span class="value">{{ strtoupper($data['document_type']) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Nomor SPPB</span>
                        <span class="value value-highlight">{{ $data['document_number'] }}</span>
                    </div>
                    @if(isset($data['status_sppb']))
                        <div class="info-row">
                            <span class="label">Status Pengajuan</span>
                            <span class="value" style="color: var(--success);">{{ strtoupper($data['status_sppb']) }}</span>
                        </div>
                    @endif
                    <div class="info-row">
                        <span class="label">Pabrik (Plant)</span>
                        <span class="value">{{ $data['plant_name'] }}</span>
                    </div>
                    @if(isset($data['department_name']))
                        <div class="info-row">
                            <span class="label">Departemen</span>
                            <span class="value">{{ $data['department_name'] }}</span>
                        </div>
                    @endif
                    @if(isset($data['requester_name']))
                        <div class="info-row">
                            <span class="label">Pemohon</span>
                            <span class="value">{{ $data['requester_name'] }}</span>
                        </div>
                    @endif
                </div>

                <!-- Jadwal & Rute Pengiriman -->
                <div class="section-title">📍 Jadwal &amp; Rute</div>
                <div class="info-grid">
                    @if(isset($data['date_needed']))
                        <div class="info-row">
                            <span class="label">Tanggal Kebutuhan</span>
                            <span class="value">{{ $data['date_needed'] }}</span>
                        </div>
                    @endif

                    @if(isset($data['locations']))
                        <div class="info-row">
                            <span class="label">Lokasi Asal &rarr; Tujuan</span>
                            <span class="value">
                                {{ $data['locations']['origin'] ?? '—' }} &rarr; {{ $data['locations']['destination'] ?? '—' }}
                            </span>
                        </div>
                    @elseif(isset($data['origin_location']) || isset($data['destination_location']))
                        <div class="info-row">
                            <span class="label">Lokasi Asal &rarr; Tujuan</span>
                            <span class="value">
                                {{ $data['origin_location'] ?? '—' }} &rarr; {{ $data['destination_location'] ?? '—' }}
                            </span>
                        </div>
                    @endif

                    @if(isset($data['purpose']))
                        <div class="info-row">
                            <span class="label">Keperluan</span>
                            <span class="value">{{ $data['purpose'] }}</span>
                        </div>
                    @endif

                    @if(isset($data['items_summary']))
                        <div class="info-row">
                            <span class="label">Ringkasan Muatan</span>
                            <span class="value">
                                {{ $data['items_summary']['total_item_types'] ?? 0 }} jenis barang
                                ({{ $data['items_summary']['total_quantity_approved'] ?? 0 }} unit)
                            </span>
                        </div>
                    @elseif(isset($data['total_items']))
                        <div class="info-row">
                            <span class="label">Ringkasan Muatan</span>
                            <span class="value">
                                {{ $data['total_items'] }} jenis barang
                                @if(isset($data['total_quantity']))
                                    ({{ (float)$data['total_quantity'] }} unit)
                                @endif
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Riwayat Otorisasi -->
                @if(!empty($data['approval_summary']))
                    <div class="section-title">🛡️ Riwayat Persetujuan</div>
                    <div class="info-grid" style="padding: 12px;">
                        <div class="approval-list">
                            @foreach($data['approval_summary'] as $appr)
                                <div class="approval-item">
                                    <div>
                                        <div class="approval-role">{{ $appr['role'] ?? 'Approver' }}</div>
                                        @if(!empty($appr['approver_name']))
                                            <div style="font-size: 11.5px; color: var(--text-secondary); margin-top: 2px;">
                                                👤 {{ $appr['approver_name'] }}
                                                @if(!empty($appr['approver_nik']))
                                                    <span style="font-size: 10.5px; opacity: 0.85;">({{ $appr['approver_nik'] }})</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="approval-status">
                                        {{ $appr['status'] ?? 'DISETUJUI' }}
                                        @if(!empty($appr['approved_at']))
                                            <span style="font-size: 11px; font-weight: normal; opacity: 0.8; margin-left: 4px;">({{ $appr['approved_at'] }})</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Security Fingerprint -->
                <div class="security-badge">
                    @if(!empty($data['fingerprint']))
                        Digital Fingerprint SHA256: <code>{{ $data['fingerprint'] }}</code><br>
                    @endif
                    @if(isset($data['page_number'], $data['total_pages']))
                        Halaman {{ $data['page_number'] }} dari {{ $data['total_pages'] }} &bull;
                    @endif
                    Status: <b>{{ $data['status_sppb'] ?? ($data['status_display'] ?? $status) }}</b>
                </div>
            @endif

            <!-- Footer -->
            <div class="footer">
                ID Validasi: <code style="font-size: 11px;">{{ $validation_id }}</code><br>
                &copy; 2026 E-SPPB Enterprise — PT Santos Jaya Abadi.
            </div>
        </div>
    </div>
</body>
</html>
