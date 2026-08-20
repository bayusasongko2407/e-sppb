<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Preview SPPB - {{ $header->sppb_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, 'Liberation Sans', sans-serif;
            font-size: 9.5pt;
            line-height: 1.3;
            color: #000000;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 0;
        }
        .pdf-header {
            width: 100%;
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #000000;
        }
        .header-title {
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            color: #000000;
        }
        .header-subtitle {
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            color: #000000;
        }
        .header-date {
            font-size: 9.5pt;
            font-weight: bold;
            color: #000000;
            margin-top: 2px;
        }

        /* Unified Info Layout Table */
        .info-layout {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #000000;
        }
        .info-layout td {
            border: 1px solid #000000;
            vertical-align: top;
            padding: 0;
        }
        .top-info {
            padding: 6px 8px;
            vertical-align: top;
            background-color: #eeeeee;
        }
        .info-inner {
            width: 100%;
            border-collapse: collapse;
        }
        .info-inner td {
            border: none !important;
            padding: 2px;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .label {
            width: 85px;
            font-weight: bold;
            white-space: nowrap;
            color: #000000;
        }
        .label-spb {
            width: 60px;
            font-weight: bold;
            white-space: nowrap;
            color: #000000;
        }
        .separator {
            width: 10px;
            text-align: center;
            font-weight: bold;
        }
        .value {
            text-align: left;
            color: #000000;
        }
        .date-box {
            text-align: center;
            vertical-align: middle;
            background-color: #eeeeee;
            padding: 6px 8px !important;
        }
        .date-title {
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000000;
            margin-bottom: 2px;
        }
        .date-value {
            font-size: 9.5pt;
            font-weight: normal;
            color: #000000;
        }
        .address-box {
            min-height: 50px;
            padding: 6px 8px !important;
            background-color: #ffffff;
        }
        .address-title {
            text-align: left;
            font-weight: bold;
            font-size: 9.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000000;
            margin-bottom: 4px;
            border-bottom: 1px solid #000000;
            padding-bottom: 2px;
        }
        .address-content {
            font-size: 9.5pt;
            color: #000000;
            line-height: 1.25;
        }
        .location-name {
            font-weight: bold;
            font-size: 9.5pt;
            color: #000000;
            margin-bottom: 2px;
        }
        .project-box {
            padding: 6px 8px !important;
            background-color: #ffffff;
        }
        .necessity-box {
            padding: 6px 8px !important;
            background-color: #ffffff;
        }

        /* Items Section */
        .items-section {
            margin-top: 10px;
            margin-bottom: 14px;
        }
        .items-title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #000000;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
        }
        .items-table th {
            border: 1px solid #000000;
            background: #eeeeee;
            padding: 5px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 9.5pt;
            color: #000000;
            text-transform: uppercase;
        }
        .items-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            vertical-align: middle;
            font-size: 9.5pt;
            color: #000000;
            line-height: 1.25;
        }
        .barcode-bold {
            font-weight: bold;
            color: #000000;
            font-size: 9.5pt;
        }
        .item-name-normal {
            font-weight: normal;
            color: #000000;
        }
        .text-center {
            text-align: center;
            vertical-align: middle;
        }

        /* Approval Section */
        .approval-section {
            margin-top: 14px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000000;
        }
        .approval-box {
            text-align: center;
            vertical-align: top;
            border: 1px solid #000000;
            padding: 0;
        }
        .approval-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            background-color: #eeeeee;
            border-bottom: 1px solid #000000;
            padding: 5px 4px;
        }
        .approval-space {
            height: 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4px;
        }
        .exec-sign {
            text-align: center;
            line-height: 1.3;
        }
        .exec-text {
            font-size: 8.5pt;
            font-style: italic;
            color: #333333;
        }
        .exec-date {
            font-size: 8pt;
            color: #555555;
            margin-top: 2px;
        }
        .exec-pending {
            color: #888888;
            font-size: 10pt;
        }
        .approval-info {
            padding: 4px 6px 6px 6px;
        }
        .approval-name {
            font-weight: bold;
            font-size: 9.5pt;
            color: #000000;
            border-top: 1px dashed #000000;
            padding-top: 3px;
            margin-top: 2px;
        }
        .approval-position {
            font-size: 8.5pt;
            color: #444444;
            margin-top: 1px;
        }

        /* Footer & QR Code */
        .qr-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .qr-table td {
            border: none !important;
            vertical-align: top;
        }
        .qr-left {
            width: 82%;
            padding-right: 10px;
        }
        .qr-right {
            width: 18%;
            text-align: center;
            vertical-align: top;
        }
        .footer-notes {
            border: 1px solid #000000;
            background-color: #eeeeee;
            padding: 6px 8px;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #000000;
        }
        .footer-notes strong {
            color: #000000;
        }
        .qr-image-container {
            display: inline-block;
            width: 75px;
            height: 75px;
            border: 1px solid #000000;
            padding: 3px;
            background: #ffffff;
        }
        .qr-image-container img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .qr-text {
            font-size: 8pt;
            margin-top: 3px;
            text-align: center;
            color: #000000;
            font-weight: bold;
        }
        tr {
            page-break-inside: avoid;
        }
        .print-actions {
            margin: 10px auto;
            width: 210mm;
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
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .print-btn:hover {
            background-color: #4338CA;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .print-actions {
                display: none;
            }
            .page {
                width: 100%;
                min-height: auto;
                margin: 0;
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
        <div class="pdf-header">
            <div class="header-title">{{ strtoupper($header->plant?->name ?? 'PT SANTOS JAYA ABADI') }}</div>
            <div class="header-subtitle">SURAT PERMOHONAN PENGIRIMAN BARANG</div>
            <div class="header-date">
                @if($header->approved_at)
                    {{ strtoupper(\Illuminate\Support\Carbon::parse($header->approved_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y')) }}
                @elseif($header->request_date)
                    {{ strtoupper(\Illuminate\Support\Carbon::parse($header->request_date)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y')) }}
                @else
                    {{ strtoupper(now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y')) }}
                @endif
            </div>
        </div>

        <!-- INFO LAYOUT -->
        <table class="info-layout">
            <tr>
                <td width="48%" class="top-info">
                    <table class="info-inner">
                        <tr>
                            <td class="label">Departemen</td>
                            <td class="separator">:</td>
                            <td class="value">{{ $header->department?->name ?? '-' }}</td>
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
                <td width="20%" class="date-box">
                    <div class="date-title">Tanggal Kebutuhan</div>
                    <div class="date-value">
                        @if($header->date_needed)
                            {{ \Illuminate\Support\Carbon::parse($header->date_needed)->translatedFormat('d F Y') }}
                        @else
                            -
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
                <td class="address-box" width="50%">
                    <div class="address-title">Alamat Asal</div>
                    <div class="address-content">
                        <div class="location-name">{{ $header->originLocation?->name ? ucwords(strtolower($header->originLocation->name)) : '-' }}</div>
                        {!! nl2br(e($header->originLocation?->address ? ucwords(strtolower($header->originLocation->address)) : '')) !!}
                    </div>
                </td>
                <td colspan="2" class="address-box" width="50%">
                    <div class="address-title">Alamat Tujuan</div>
                    <div class="address-content">
                        <div class="location-name">{{ $header->destinationLocation?->name ? ucwords(strtolower($header->destinationLocation->name)) : '-' }}</div>
                        {!! nl2br(e($header->destinationLocation?->address ? ucwords(strtolower($header->destinationLocation->address)) : '')) !!}
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" class="necessity-box">
                    <table class="info-inner">
                        <tr>
                            <td class="label">Keterangan</td>
                            <td class="separator">:</td>
                            <td class="value">{!! nl2br(e($header->purpose ?? '-')) !!}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- ITEMS SECTION -->
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
                        @php
                            $isAsset = !empty($detail->asset_id) || !empty($detail->asset);
                            $codeValue = $isAsset
                                ? ($detail->asset?->barcode ?? $detail->reference_code)
                                : ($detail->item?->code ?? $detail->reference_code);
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <span class="item-name-normal">{{ $detail->item_asset_name }}</span>
                                @if(!empty($codeValue) && $codeValue !== '-')
                                    <br><span class="barcode-bold">{{ $isAsset ? 'Barcode' : 'Kode' }}: {{ $codeValue }}</span>
                                @endif
                            </td>
                            <td class="text-center"><b>{{ number_format((float) $detail->quantity, 2) }}</b></td>
                            <td class="text-center">{{ $detail->unit?->name ?? '-' }}</td>
                            <td>{!! nl2br(e($detail->remarks ?? '-')) !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- APPROVAL SECTION -->
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

                        // Collect signers
                        $signers = [];
                        
                        // Pemohon
                        $signers[] = [
                            'title' => 'Dibuat Oleh',
                            'name' => strtoupper($header->requester?->name ?? '-'),
                            'position' => $getPositionName($header->requester),
                            'status' => 'APPROVED',
                            'date' => $header->submitted_at ? \Illuminate\Support\Carbon::parse($header->submitted_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : null,
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
                                    'date' => ($step->status === 'APPROVED' && $step->acted_at) ? \Illuminate\Support\Carbon::parse($step->acted_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : null,
                                ];
                            }
                        } else {
                            // Fallback if no workflow (legacy/testing)
                            $approverName = '&mdash;';
                            $status = 'PENDING';
                            $posName = '';
                            $approvedDate = null;
                            if (in_array($header->status, ['APPROVED', 'RELEASE_IN_PROGRESS', 'COMPLETED'])) {
                                $status = 'APPROVED';
                                $actLog = $header->sppbStatusLogs()
                                    ->where('action', 'APPROVED')
                                    ->latest()
                                    ->first();
                                $approverUser = $actLog ? $actLog->actor : null;
                                $approverName = $approverUser ? strtoupper($approverUser->name) : 'MANAGER';
                                $posName = $approverUser ? $getPositionName($approverUser) : 'MANAGER';
                                $approvedDate = $actLog ? \Illuminate\Support\Carbon::parse($actLog->logged_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB' : null;
                            }
                            $signers[] = [
                                'title' => 'Disetujui Oleh',
                                'name' => $approverName,
                                'position' => $posName,
                                'status' => $status,
                                'date' => $approvedDate,
                            ];
                        }
                    @endphp

                    @foreach($signers as $signer)
                    <td class="approval-box" style="width: {{ 100 / count($signers) }}%">
                        <div class="approval-title">{{ $signer['title'] }}</div>
                        <div class="approval-space">
                            @if($signer['status'] === 'APPROVED')
                                <div class="exec-sign">
                                    <div class="exec-text">( Disetujui Secara Sistem )</div>
                                    @if(!empty($signer['date']))
                                        <div class="exec-date">{{ $signer['date'] }}</div>
                                    @endif
                                </div>
                            @else
                                <div class="exec-pending">&mdash;</div>
                            @endif
                        </div>
                        <div class="approval-info">
                            <div class="approval-name">{!! $signer['name'] !!}</div>
                            @if(!empty($signer['position']))
                                <div class="approval-position">{{ $signer['position'] }}</div>
                            @endif
                        </div>
                    </td>
                    @endforeach
                </tr>
            </table>
        </div>

        <!-- FOOTER & QR CODE -->
        <table class="qr-table">
            <tr>
                <td class="qr-left">
                    <div class="footer-section">
                        <div class="footer-notes">
                            <strong>Catatan Ketentuan Dokumen:</strong><br>
                            1. Dokumen ini merupakan SPPB sah yang telah diajukan dan disetujui secara digital.<br>
                            2. SPPB hanya berfungsi sebagai instruksi penyiapan barang, bukan Surat Jalan.<br>
                            3. Bukti pengiriman atau penyerahan fisik barang wajib menggunakan Surat Jalan resmi.<br>
                            4. Scan QR Code untuk memverifikasi keaslian dan status terkini dokumen di sistem E-SPPB.<br>
                            <span style="display:inline-block; margin-top:4px; font-style:italic; color:#555;">Dicetak pada: {{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }} WIB oleh {{ auth()->user()?->name ?? 'Sistem' }}</span>
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
