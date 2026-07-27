<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Jalan - {{ $record->is_manual ? $record->manual_release_number : $record->release_number }}</title>

<style>
@page {
    size: A4 portrait;
    margin: 12mm;
}

*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family: Arial, Helvetica, sans-serif;
    font-size:11px;
    color:#222;
    background:#f3f3f3;
}

.page{
    width:210mm;
    min-height:297mm;
    background:#fff;
    margin:auto;
    padding:0;
}

table{
    width:100%;
    border-collapse:collapse;
}

.section{
    border:1px solid #444;
    margin-top:8px;
    page-break-inside: avoid;
}

.section-title{
    background:#ececec;
    padding:6px 8px;
    font-weight:bold;
    border-bottom:1px solid #444;
    text-transform:uppercase;
}

.p8{
    padding:8px;
}

.header{
    border:2px solid #444;
}

.header td{
    vertical-align:top;
}

.logo{
    width:90px;
    height:70px;
    border:1px solid #aaa;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:bold;
    overflow:hidden;
}

.logo img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

.title{
    text-align:center;
}

.title h1{
    margin:5px 0;
    font-size:24px;
    letter-spacing:1px;
}

.title h2{
    margin:0;
    font-size:16px;
    font-weight:bold;
}

.info td{
    padding:3px;
}

.label{
    width:140px;
    font-weight:bold;
}

.border-table th,
.border-table td{
    border:1px solid #444;
    padding:5px;
}

.border-table th{
    background:#efefef;
    text-align:center;
}

.center{
    text-align:center;
}

.right{
    text-align:right;
}

.signature td{
    border:1px solid #444;
    width:33.33%;
    height:120px;
    text-align:center;
    vertical-align:top;
}

.footer{
    margin-top:10px;
    border:1px solid #444;
    padding:8px;
    font-size:10px;
    page-break-inside: avoid;
}

.hash{
    font-family:monospace;
    word-break:break-all;
}

.print-actions {
    margin-bottom: 20px;
    text-align: right;
    width: 210mm;
    margin: 10px auto;
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
    body {
        background: #fff;
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

@php
    $logoPdf = \App\Models\AppSetting::get('logo_pdf');
    $companyName = \App\Models\AppSetting::get('company_name', 'PT SANTOS JAYA ABADI');
    $companyAddress = \App\Models\AppSetting::get('company_address', 'Jl. Raya Gilang No. 159, Taman, Sidoarjo, Jawa Timur, Indonesia');
    $companyPhone = \App\Models\AppSetting::get('company_phone', '+62-31-8971000');

    // Resolve QR Code
    $page = \App\Models\DocumentPage::whereHas('documentGeneration', function ($q) use ($record) {
        $q->where('goods_release_id', $record->id)->where('status', 'READY');
    })->orderByDesc('id')->first();

    if ($page && $page->verification_token_hash) {
        $verifyUrl = route('document.verify', ['sha256Token' => $page->verification_token_hash]);
        $verificationTokenHash = $page->verification_token_hash;
    } else {
        $verificationTokenHash = hash('sha256', $record->uuid . '-page-1');
        $verifyUrl = url('/verify/document/' . $verificationTokenHash);
    }

    $qrOptions = new \chillerlan\QRCode\QROptions([
        'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
        'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
        'addQuietzone' => false,
    ]);
    $qrCodeSvg = (new \chillerlan\QRCode\QRCode($qrOptions))->render($verifyUrl);

    // Resolve Mengetahui Signatory (Final Approver of first SPPB workflow)
    $approverName = '';
    $sppb = $record->sppbHeader;
    if ($sppb) {
        $instance = $sppb->currentWorkflowInstance;
        if ($instance) {
            $lastApprovedStep = $instance->workflowInstanceSteps()
                ->where('status', 'APPROVED')
                ->orderByDesc('sequence')
                ->first();
            if ($lastApprovedStep && $lastApprovedStep->actedBy) {
                $approverName = strtoupper($lastApprovedStep->actedBy->name);
            }
        }
    }
@endphp

<div class="print-actions">
    <button onclick="window.print()" class="print-btn">Cetak Surat Jalan</button>
</div>

<div class="page">

<!-- ====================================================== -->
<!-- HEADER -->
<!-- ====================================================== -->

<table class="header">
    <tr>
        <td width="40%" class="p8">
            <table>
                <tr>
                    <td width="90" style="padding-right: 8px;">
                        <div class="logo">
                            @if($logoPdf)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logoPdf) }}" alt="Logo">
                            @else
                                LOGO
                            @endif
                        </div>
                    </td>
                    <td>
                        <b style="font-size: 12px;">{{ strtoupper($companyName) }}</b><br>
                        <span style="font-size: 9px; color: #333; line-height: 1.25; display: inline-block; margin-top: 4px;">
                            {{ $record->sppbHeader?->plant?->name ?? ($record->sppbHeaders->first()?->plant?->name ?? 'Plant') }}<br>
                            {!! nl2br(e($companyAddress)) !!}<br>
                            Telp: {{ $companyPhone }}
                        </span>
                    </td>
                </tr>
            </table>
        </td>

        <td width="45%" class="center" style="vertical-align: middle; padding: 10px 5px;">
            <div class="title">
                <h1>SURAT JALAN</h1>
                @if($record->is_manual)
                    <h2>No. {{ $record->manual_release_number }}</h2>
                    <div style="font-size: 10px; margin-top: 4px; color: #555; font-weight: normal;">
                        Ref. No: {{ $record->release_number }}
                    </div>
                @else
                    <h2>No. {{ $record->release_number }}</h2>
                @endif
            </div>
        </td>

        <td width="15%" class="center" style="vertical-align: middle; padding: 8px;">
            <div style="display: inline-block; width: 75px; height: 75px; margin-bottom: 4px;">
                <img src="{!! $qrCodeSvg !!}" alt="QR Verifikasi" style="width: 100%; height: auto; display: block;">
            </div>
            <div style="font-size: 8px; text-align: center; color: #555;">Verifikasi Dokumen</div>
        </td>
    </tr>
</table>

<!-- ====================================================== -->
<!-- INFORMASI SURAT JALAN -->
<!-- ====================================================== -->

<div class="section">
    <div class="section-title">
        1. Informasi Surat Jalan
    </div>
    <div class="p8">
        <table class="info">
            <tr>
                <td class="label">Tanggal</td>
                <td>:</td>
                <td>{{ $record->delivery_date ? \Illuminate\Support\Carbon::parse($record->delivery_date)->translatedFormat('d F Y') : '-' }}</td>
                
                <td class="label">Status</td>
                <td>:</td>
                <td>
                    @php
                        $statusLabels = [
                            'DRAFT' => 'DRAFT',
                            'RELEASED' => 'DALAM PENGIRIMAN',
                            'RECEIVED' => 'TERKIRIM',
                            'CANCELLED' => 'DIBATALKAN',
                        ];
                        $displayStatus = $statusLabels[$record->status] ?? $record->status;
                    @endphp
                    {{ $displayStatus }}
                </td>
            </tr>
            <tr>
                <td class="label">Plant</td>
                <td>:</td>
                <td>{{ $record->sppbHeader?->plant?->name ?? ($record->sppbHeaders->first()?->plant?->name ?? '-') }}</td>
                
                <td class="label">Total SPPB</td>
                <td>:</td>
                <td>{{ $record->sppbHeaders->count() ?: 1 }} Dokumen</td>
            </tr>
            <tr>
                <td class="label">Departemen</td>
                <td>:</td>
                <td>{{ $record->sppbHeader?->department?->name ?? ($record->sppbHeaders->first()?->department?->name ?? '-') }}</td>
                
                <td class="label">Total Item</td>
                <td>:</td>
                <td>{{ $record->goodsReleaseItems->count() }} Item</td>
            </tr>
            <tr>
                <td class="label">Pembuat</td>
                <td>:</td>
                <td>{{ $record->createdBy?->name ?? '-' }}</td>
                
                <td class="label">Total Qty Kirim</td>
                <td>:</td>
                <td>{{ number_format((float)$record->goodsReleaseItems->sum('quantity_released'), 2) }}</td>
            </tr>
        </table>
    </div>
</div>

<!-- ====================================================== -->
<!-- SPPB -->
<!-- ====================================================== -->

<div class="section">
    <div class="section-title">
        2. Informasi SPPB
    </div>
    <table class="border-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">No. SPPB</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Pemohon</th>
                <th width="12%">Status</th>
                <th width="18%">Keperluan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->sppbHeaders as $index => $sppbItem)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $sppbItem->document_number }}</td>
                <td>{{ $sppbItem->request_date ? \Illuminate\Support\Carbon::parse($sppbItem->request_date)->translatedFormat('d/m/Y') : '-' }}</td>
                <td>{{ $sppbItem->requester?->name ?? '-' }}</td>
                <td class="center">{{ $sppbItem->status }}</td>
                <td>{{ $sppbItem->needed_name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- ====================================================== -->
<!-- PENGIRIMAN -->
<!-- ====================================================== -->

<div class="section">
    <div class="section-title">
        3. Informasi Pengiriman
    </div>
    <div class="p8">
        <table>
            <tr>
                <td width="45%" valign="top">
                    <table class="info">
                        <tr>
                            <td class="label">Nama Driver</td>
                            <td>:</td>
                            <td>{{ $record->driver_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">No Kendaraan</td>
                            <td>:</td>
                            <td>{{ $record->vehicle_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Ekspedisi</td>
                            <td>:</td>
                            <td>{{ $record->expedition_name ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td width="55%" valign="top">
                    <table class="info">
                        <tr>
                            <td class="label">Lokasi Asal</td>
                            <td>:</td>
                            <td>{{ $record->sender_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Alamat Asal</td>
                            <td>:</td>
                            <td>{!! nl2br(e($record->sender_address ?? '-')) !!}</td>
                        </tr>
                        <tr>
                            <td class="label">Tujuan</td>
                            <td>:</td>
                            <td>{{ $record->receiver_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Alamat Tujuan</td>
                            <td>:</td>
                            <td>{!! nl2br(e($record->receiver_address ?? '-')) !!}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 10px;">
                    <b>Catatan Pengiriman:</b><br>
                    {!! nl2br(e($record->notes ?? 'Tidak ada catatan.')) !!}
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- ====================================================== -->
<!-- BARANG -->
<!-- ====================================================== -->

<div class="section">
    <div class="section-title">
        4. Daftar Barang
    </div>
    <table class="border-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Jenis</th>
                <th width="18%">Barcode/Kode</th>
                <th width="30%">Nama Barang</th>
                <th width="10%">Qty SPPB</th>
                <th width="10%">Qty Kirim</th>
                <th width="7%">Satuan</th>
                <th width="8%">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalRequested = 0;
                $totalReleased = 0;
            @endphp
            @foreach($record->goodsReleaseItems as $index => $item)
            @php
                $itemType = $item->sppbDetail?->asset_id ? 'Asset' : 'Non Asset';
                $barcode = $item->sppbDetail?->asset?->barcode ?? $item->sppbDetail?->item?->code ?? $item->sppbDetail?->reference_code ?? '-';
                $totalRequested += $item->quantity_requested;
                $totalReleased += $item->quantity_released;
            @endphp
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $itemType }}</td>
                <td>{{ $barcode }}</td>
                <td>{{ $item->sppbDetail?->item_asset_name }}</td>
                <td class="right">{{ number_format((float)$item->quantity_requested, 2) }}</td>
                <td class="right">{{ number_format((float)$item->quantity_released, 2) }}</td>
                <td class="center">{{ $item->sppbDetail?->unit?->name }}</td>
                <td class="center">{{ $item->condition_on_release ?? '-' }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="4" class="right">
                    <b>TOTAL</b>
                </td>
                <td class="right">
                    <b>{{ number_format((float)$totalRequested, 2) }}</b>
                </td>
                <td class="right">
                    <b>{{ number_format((float)$totalReleased, 2) }}</b>
                </td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- ====================================================== -->
<!-- TANDA TANGAN -->
<!-- ====================================================== -->

<div class="section">
    <div class="section-title">
        5. Persetujuan
    </div>
    <table class="signature">
        <tr>
            <td>
                <br><b>Dibuat Oleh</b>
                <br><br><br><br><br>
                ({{ strtoupper($record->createdBy?->name ?? '.............................') }})
            </td>
            <td>
                <br><b>Dikirim Oleh (Driver)</b>
                <br><br><br><br><br>
                ({{ strtoupper($record->driver_name ?? '.............................') }})
            </td>
            <td>
                <br><b>Mengetahui</b>
                <br><br><br><br><br>
                ({{ !empty($approverName) ? $approverName : '.............................' }})
            </td>
        </tr>
    </table>
</div>

<!-- ====================================================== -->
<!-- FOOTER -->
<!-- ====================================================== -->

<div class="footer">
    <table>
        <tr>
            <td width="80%">
                <b>Hash Verifikasi</b>
                <div class="hash">
                    {{ $verificationTokenHash }}
                </div>
                Scan QR Code untuk melakukan verifikasi keaslian Surat Jalan.
            </td>
            <td align="right" valign="bottom">
                Halaman 1 / 1
            </td>
        </tr>
    </table>
</div>

</div>

<script>
    window.onload = function() {
        window.print();
    };
</script>

</body>
</html>
