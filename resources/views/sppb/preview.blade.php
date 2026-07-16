<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Preview SPPB - {{ $header->sppb_no }}</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 100%;
        }
        .pdf-header {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header-date {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .info-layout {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-layout td {
            border: 1px solid #000;
            vertical-align: top;
            padding: 0;
        }
        .top-info {
            padding: 6px 8px;
            vertical-align: top;
        }
        .info-inner {
            width: 100%;
            border-collapse: collapse;
        }
        .info-inner td {
            border: none !important;
            padding: 3px 2px;
            vertical-align: top;
            font-size: 11px;
        }
        .label {
            width: 85px;
            font-weight: bold;
            white-space: nowrap;
        }
        .label-spb {
            width: 50px;
            font-weight: bold;
            white-space: nowrap;
        }
        .separator {
            width: 10px;
            text-align: center;
        }
        .value {
            text-align: left;
        }
        .date-box {
            text-align: center;
            vertical-align: top;
        }
        .date-title {
            font-weight: bold;
            padding: 5px;
            font-size: 11px;
        }
        .date-value {
            padding-top: 10px;
            font-size: 11px;
        }
        .address-box {
            height: 70px;
            padding: 6px 8px;
        }
        .address-title {
            text-align: left;
            font-weight: bold;
            padding: 8px;
            font-size: 11px;
        }
        .address-content {
            padding: 8px;
            font-size: 11px;
        }
        .location-name {
            font-weight: bold;
            font-size: 12px;
        }
        .project-box {
            height: 50px;
            padding: 6px 8px;
        }
        .necessity-box {
            height: 50px;
            padding: 6px 8px;
        }
        .items-section {
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .items-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            border: 1px solid #000;
            background: #d9d9d9;
            padding: 6px;
            text-align: center;
            font-weight: bold;
        }
        .items-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        .text-center {
            text-align: center;
            vertical-align: middle;
        }
        .empty-row td {
            height: 24px;
        }
        .approval-section {
            margin-top: 35px;
            margin-bottom: 20px;
        }
        .approval-table {
            width: 100%;
            border-collapse: collapse;
        }
        .approval-box {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .approval-title {
            font-size: 12px;
            margin-bottom: 5px;
        }
        .approval-space {
            height: 65px;
            position: relative;
        }
        .watermark-approved {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 20px;
            color: rgba(255, 0, 0, 0.25);
            font-weight: bold;
            border: 2px solid rgba(255, 0, 0, 0.25);
            border-radius: 4px;
            padding: 4px 8px;
            z-index: -1;
            pointer-events: none;
            white-space: nowrap;
        }
        .approval-name {
            font-weight: bold;
            font-size: 12px;
        }
        .qr-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .qr-table td {
            border: none !important;
        }
        .qr-left {
            width: 80%;
        }
        .qr-right {
            width: 20%;
            text-align: center;
            vertical-align: bottom;
        }
        .qr-image-container {
            display: inline-block;
            width: 80px;
            height: auto;
        }
        .qr-image-container img {
            width: 100%;
            height: auto;
            display: block;
        }
        .qr-text {
            font-size: 9px;
            margin-top: 4px;
            text-align: center;
        }
        .footer-section {
            margin-top: 10px;
        }
        .footer-notes {
            border-top: 1px solid #000;
            padding-top: 8px;
            font-size: 11px;
            line-height: 1.5;
        }
        tr {
            page-break-inside: avoid;
        }
        .print-actions {
            margin-bottom: 20px;
            text-align: right;
        }
        .print-btn {
            background-color: #4F46E5;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-btn:hover {
            background-color: #4338CA;
        }
        @media print {
            .print-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print()" class="print-btn">Cetak Dokumen</button>
    </div>
    <div class="page">
        <!-- HEADER -->
        <div class="pdf-header" style="position: relative;">
            <img src="{{ asset('images/logo-lanscape.png') }}" style="position: absolute; left: 0; top: 0; height: 100%; z-index: -1;" alt="Logo">
            <div class="header-title">{{ strtoupper($header->plant?->name ?? 'PT SANTOS JAYA ABADI') }}</div>
            <div class="header-title">SURAT PERMOHONAN PENGIRIMAN BARANG</div>
            <div class="header-date">
                @if($header->approved_at)
                    {{ strtoupper(\Illuminate\Support\Carbon::parse($header->approved_at)->translatedFormat('d F Y')) }}
                @endif
            </div>
        </div>

        <!-- INFO -->
        <table class="info-layout">
            <tr>
                <td width="50%" class="top-info">
                    <table class="info-inner">
                        <tr>
                            <td class="label">Departemen</td>
                            <td class="separator">:</td>
                            <td class="value">{{ $header->department?->name }}</td>
                        </tr>
                    </table>
                </td>
                <td width="32%" class="top-info">
                    <table class="info-inner">
                        <tr>
                            <td class="label-spb">No. SPPB</td>
                            <td class="separator">:</td>
                            <td class="value">{{ $header->sppb_no }}</td>
                        </tr>
                    </table>
                </td>
                <td width="18%" class="date-box">
                    <div class="date-title">Tanggal Kebutuhan</div>
                    <div class="date-value">
                        @if($header->date_needed)
                            {{ \Illuminate\Support\Carbon::parse($header->date_needed)->translatedFormat('d F Y') }}
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="project-box">
                    <table class="info-inner">
                        <tr>
                            <td class="label">Nama Project</td>
                            <td class="separator">:</td>
                            <td class="value">{!! nl2br(e($header->needed_name)) !!}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="address-box">
                    <div class="address-title">Alamat Asal</div>
                    <div class="address-content">
                        <div class="location-name">{{ $header->originLocation?->name }}</div>
                        {!! nl2br(e($header->originLocation?->address)) !!}
                    </div>
                </td>
                <td colspan="2" class="address-box">
                    <div class="address-title">Alamat Tujuan</div>
                    <div class="address-content">
                        <div class="location-name">{{ $header->destinationLocation?->name }}</div>
                        {!! nl2br(e($header->destinationLocation?->address)) !!}
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="necessity-box">
                    <table class="info-inner">
                        <tr>
                            <td class="label">Keterangan</td>
                            <td class="separator">:</td>
                            <td class="value">{!! nl2br(e($header->purpose)) !!}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- ITEMS -->
        <div class="items-section">
            <div class="items-title">Daftar Barang</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th width="5%">No.</th>
                        <th width="50%">Nama Barang</th>
                        <th width="10%">Jumlah</th>
                        <th width="10%">Satuan</th>
                        <th width="25%">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($header->sppbDetails as $index => $detail)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                {{ $detail->item_asset_name }}
                                @if($detail->barcode_confirmed && $detail->asset?->barcode)
                                    <br><strong>Barcode: {{ $detail->asset->barcode }}</strong>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format((float) $detail->quantity, 2) }}</td>
                            <td class="text-center">{{ $detail->unit?->name }}</td>
                            <td>{!! nl2br(e($detail->remarks)) !!}</td>
                        </tr>
                    @endforeach
                    <tr class="empty-row">
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- APPROVAL -->
        <div class="approval-section">
            <table class="approval-table">
                <tr>
                    @php
                        // Helper to get position of a user
                        $getPositionName = function ($user) {
                            if (!$user) {
                                return '';
                            }
                            $activePositions = $user->positions()->where('is_active', true)->with('position')->get();
                            $primaryPos = $activePositions->firstWhere('is_primary', true);
                            $anyPos = $primaryPos ?? $activePositions->first();
                            
                            if ($anyPos && $anyPos->position) {
                                return $anyPos->position->name;
                            }
                            
                            $roleName = $user->roles->pluck('name')->first();
                            if ($roleName) {
                                return str_replace('_', ' ', ucwords($roleName, '_'));
                            }
                            
                            return '';
                        };

                        // 1. Collect signers
                        $signers = [];
                        
                        // Pemohon
                        $signers[] = [
                            'title' => 'Dibuat Oleh',
                            'name' => strtoupper($header->requester?->name ?? ''),
                            'position' => $getPositionName($header->requester),
                            'status' => 'APPROVED', // Always approved since it's submitted
                        ];
                        
                        // Fetch workflow steps
                        $instance = $header->currentWorkflowInstance;
                        if ($instance) {
                            $steps = $instance->workflowInstanceSteps()->with(['workflowStep', 'actedBy'])->get()->sortBy(function($s) {
                                return $s->workflowStep?->sequence ?? 99;
                            });
                            
                            foreach ($steps as $step) {
                                $stepDef = $step->workflowStep;
                                if (!$stepDef) continue;
                                
                                $title = 'Diketahui Oleh';
                                if ($stepDef->is_final) {
                                    $title = 'Disetujui Oleh';
                                } elseif (stripos($stepDef->name, 'BAT') !== false || stripos($stepDef->code, 'BAT') !== false || stripos($stepDef->name, 'verifikasi') !== false) {
                                    $title = 'Diverifikasi Oleh';
                                }
                                
                                $signerUser = $step->status === 'APPROVED' && $step->actedBy ? $step->actedBy : null;
                                
                                $signers[] = [
                                    'title' => $title,
                                    'name' => $signerUser ? strtoupper($signerUser->name) : '&mdash;',
                                    'position' => $signerUser ? $getPositionName($signerUser) : '',
                                    'status' => $step->status,
                                ];
                            }
                        } else {
                            // Fallback if no workflow (legacy/testing)
                            $approverName = '&mdash;';
                            $status = 'PENDING';
                            $posName = '';
                            if ($header->status === 'APPROVED') {
                                $status = 'APPROVED';
                                $actLog = $header->sppbStatusLogs()
                                    ->where('action', 'APPROVED')
                                    ->latest()
                                    ->first();
                                $approverUser = $actLog ? $actLog->actor : null;
                                $approverName = $approverUser ? strtoupper($approverUser->name) : 'MANAGER';
                                $posName = $approverUser ? $getPositionName($approverUser) : 'MANAGER';
                            }
                            $signers[] = [
                                'title' => 'Disetujui Oleh',
                                'name' => $approverName,
                                'position' => $posName,
                                'status' => $status,
                            ];
                        }
                    @endphp

                    @foreach($signers as $signer)
                    <td class="approval-box" style="width: {{ 100 / count($signers) }}%">
                        <div class="approval-title">{{ $signer['title'] }}</div>
                        <div class="approval-space">
                            @if($signer['status'] === 'APPROVED')
                                <div class="watermark-approved">APPROVED</div>
                            @endif
                        </div>
                        <div class="approval-name">{!! $signer['name'] !!}</div>
                        @if(!empty($signer['position']))
                            <div class="approval-position" style="font-size: 10px; color: #555; margin-top: 2px;">{{ $signer['position'] }}</div>
                        @endif
                    </td>
                    @endforeach
                </tr>
            </table>
        </div>

        <!-- QR CODE & FOOTER -->
        <table class="qr-table">
            <tr>
                <td class="qr-left">
                    <div class="footer-section">
                        <div class="footer-notes">
                            Catatan:<br>
                            1. Dokumen ini sah dan valid secara hukum sebagai Surat Permohonan Pengiriman Barang yang disetujui secara digital.<br>
                            2. Silakan scan QR code di samping untuk memverifikasi keaslian dan status terkini dokumen ini langsung pada sistem E-SPPB.
                        </div>
                    </div>
                </td>
                <td class="qr-right">
                    @php
                        $page = \App\Models\DocumentPage::whereHas('documentGeneration', function ($q) use ($header) {
                            $q->where('sppb_header_id', $header->id)->where('status', 'READY');
                        })->orderByDesc('id')->first();

                        if ($page && $page->verification_token_hash) {
                            $verifyUrl = route('document.verify', ['sha256Token' => $page->verification_token_hash]);
                        } else {
                            // Fallback: derive token from header UUID if no generation exists yet
                            $verifyUrl = url('/verify/document/' . hash('sha256', $header->uuid . '-page-1'));
                        }


                        $qrOptions = new \chillerlan\QRCode\QROptions([
                            'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
                            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
                            'addQuietzone' => false,
                        ]);
                        $qrCodeSvg = (new \chillerlan\QRCode\QRCode($qrOptions))->render($verifyUrl);
                    @endphp
                    <div class="qr-image-container">
                        <img src="{!! $qrCodeSvg !!}" alt="QR Verifikasi">
                    </div>
                    <div class="qr-text">Scan untuk verifikasi</div>
                </td>
            </tr>
        </table>
    </div>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
