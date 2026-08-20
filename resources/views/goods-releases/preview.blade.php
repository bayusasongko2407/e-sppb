<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Surat Jalan - {{ $record->is_manual ? $record->manual_release_number : $record->release_number }}</title>

<style>
@page {
    size: A4 portrait;
    margin: 8mm 10mm 8mm 10mm;
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
    margin:20px auto;
    padding:10mm 10mm 12mm 10mm;
    border:1px solid #d1d5db;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    position: relative;
    box-sizing: border-box;
}

table{
    width:100%;
    border-collapse:collapse;
}

.section{
    border:1px solid #444;
    margin-top:5px;
    page-break-inside: avoid;
    break-inside: avoid;
}

.section-persetujuan {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
}

.section-title{
    background:#ececec;
    padding:4px 8px;
    font-weight:bold;
    border-bottom:1px solid #444;
    text-transform:uppercase;
    font-size: 10.5px;
}

.p8{
    padding:5px 8px;
}

.header{
    border:2px solid #444;
}

.header td{
    vertical-align:top;
}

.logo{
    width:85px;
    height:65px;
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
    margin:2px 0;
    font-size:20px;
    letter-spacing:1px;
}

.title h2{
    margin:0;
    font-size:14px;
    font-weight:bold;
}

.info td{
    padding:2px 4px;
}

.label{
    width:130px;
    font-weight:bold;
}

.border-table th,
.border-table td{
    border:1px solid #444;
    padding:4px 5px;
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

.barcode-bold {
    font-weight: bold;
    color: #000000;
    font-size: 9pt;
}

.item-name-normal {
    font-weight: normal;
    color: #222222;
}

.signature td{
    border:1px solid #444;
    width:33.33%;
    height:85px;
    text-align:center;
    vertical-align:top;
}

.footer{
    margin-top:8px;
    border:1px solid #444;
    padding:6px 8px;
    font-size:9.5px;
    page-break-inside: avoid;
    break-inside: avoid;
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
    @page {
        size: A4 portrait;
        margin: 8mm 10mm 8mm 10mm;
    }
    html, body {
        background: #fff;
        margin: 0;
        padding: 0;
    }
    .print-actions {
        display: none;
    }
    .page {
        width: 100%;
        min-height: auto;
        height: auto;
        margin: 0;
        padding: 0;
        border: none;
        box-shadow: none;
        position: static;
        display: block;
    }
    .footer {
        border: none;
        border-top: 1px solid #444;
        padding-top: 4px;
        margin-top: 8px;
        page-break-inside: avoid;
        break-inside: avoid;
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

    // Resolve QR Code (QR nomor surat jalan otomatis yang terenkripsi)
    $releaseNumberToEncrypt = $record->release_number ?? 'SJ-'.date('Ymd').'-0000';
    $encryptedReleaseNumber = \Illuminate\Support\Facades\Crypt::encryptString($releaseNumberToEncrypt);

    $qrOptions = new \chillerlan\QRCode\QROptions([
        'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
        'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
        'addQuietzone' => false,
    ]);
    $qrCodeSvg = (new \chillerlan\QRCode\QRCode($qrOptions))->render($encryptedReleaseNumber);

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

<div class="section section-info">
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
<!-- ====================================================== -->
<!-- SPPB (HANYA DITAMPILKAN JIKA MEMILIKI SPPB) -->
<!-- ====================================================== -->

@php
    $hasSppb = ($record->sppbHeaders && $record->sppbHeaders->isNotEmpty()) || !empty($record->sppb_header_id);
@endphp

@if($hasSppb)
<div class="section section-sppb">
    <div class="section-title">
        2. Informasi SPPB
    </div>
    <table class="border-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="1%" style="white-space: nowrap;">No. SPPB</th>
                <th width="1%" style="white-space: nowrap;">Tanggal</th>
                <th width="20%">Pemohon</th>
                <th>Keperluan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sppbList = ($record->sppbHeaders && $record->sppbHeaders->isNotEmpty()) ? $record->sppbHeaders : collect([$record->sppbHeader])->filter();
            @endphp
            @foreach($sppbList as $index => $sppbItem)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td style="white-space: nowrap;">{{ $sppbItem->document_number ?? $sppbItem->sppb_no }}</td>
                <td class="center" style="white-space: nowrap;">{{ $sppbItem->request_date ? \Illuminate\Support\Carbon::parse($sppbItem->request_date)->translatedFormat('d/m/Y') : '-' }}</td>
                <td>{{ $sppbItem->requester?->name ?? '-' }}</td>
                <td>{{ $sppbItem->needed_name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- ====================================================== -->
<!-- PENGIRIMAN -->
<!-- ====================================================== -->

<div class="section section-pengiriman">
    <div class="section-title">
        {{ $hasSppb ? '3' : '2' }}. Informasi Pengiriman
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
                            <td><b>{{ $record->sender_name ?? '-' }}</b></td>
                        </tr>
                        <tr>
                            <td class="label">Alamat Asal</td>
                            <td>:</td>
                            <td>{!! nl2br(e($record->sender_address ?? '-')) !!}</td>
                        </tr>
                        <tr>
                            <td class="label">Tujuan</td>
                            <td>:</td>
                            <td><b>{{ $record->receiver_name ?? '-' }}</b></td>
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

<div class="section section-barang">
    <div class="section-title">
        {{ $hasSppb ? '4' : '3' }}. Daftar Barang
    </div>
    <table class="border-table">
        @if(! $hasSppb)
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="10%">Jenis</th>
                    <th width="45%">Nama Barang / Aset</th>
                    <th width="12%">Qty Kirim</th>
                    <th width="8%">Satuan</th>
                    <th width="20%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php $totalKirimManual = 0; @endphp
                @foreach($record->goodsReleaseItems as $index => $item)
                @php
                    $itemTitle = $item->item_name ?? $item->sppbDetail?->item_asset_name ?? '-';
                    $codeVal = $item->barcode_code ?? ($item->sppbDetail?->asset?->barcode ?? $item->sppbDetail?->item?->code ?? '-');
                    $qtyKirim = (float) $item->quantity_released;
                    $totalKirimManual += $qtyKirim;
                    $unitName = $item->unit_name ?? $item->sppbDetail?->unit?->name ?? '-';
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $item->item_type ?? 'Non Asset' }}</td>
                    <td>
                        <span class="item-name-normal">{{ $itemTitle }}</span>
                        @if(!empty($codeVal) && $codeVal !== '-')
                            <br><span class="barcode-bold">Kode/Barcode: {{ $codeVal }}</span>
                        @endif
                    </td>
                    <td class="right"><b>{{ number_format($qtyKirim, 2) }}</b></td>
                    <td class="center">{{ $unitName }}</td>
                    <td class="center">{{ $item->condition_on_release ?? '-' }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="right"><b>TOTAL</b></td>
                    <td class="right"><b>{{ number_format($totalKirimManual, 2) }}</b></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        @else
            <thead>
                <tr>
                    <th rowspan="2" width="4%">No</th>
                    <th rowspan="2" width="7%">Jenis</th>
                    <th rowspan="2" width="33%">Nama Barang / Aset</th>
                    <th colspan="2" width="18%">Qty SPPB</th>
                    <th rowspan="2" width="10%">Qty Kirim Ini</th>
                    <th rowspan="2" width="9%">Sisa SPPB</th>
                    <th rowspan="2" width="6%">Satuan</th>
                    <th rowspan="2" width="13%">Keterangan</th>
                </tr>
                <tr>
                    <th width="9%">Awal</th>
                    <th width="9%">Terkirim</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalAwal = 0;
                    $totalTerkirimBefore = 0;
                    $totalKirimIni = 0;
                    $totalSisa = 0;
                @endphp
                @foreach($record->goodsReleaseItems as $index => $item)
                @php
                    $sppbDetail = $item->sppbDetail;
                    $isAsset = !empty($sppbDetail?->asset_id) || !empty($sppbDetail?->asset);
                    $itemType = $isAsset ? 'Asset' : 'Non Asset';
                    $codeValue = $isAsset
                        ? ($sppbDetail?->asset?->barcode ?? $sppbDetail?->reference_code)
                        : ($sppbDetail?->item?->code ?? $sppbDetail?->reference_code);

                    $qtyAwal = (float) ($sppbDetail?->quantity ?? $item->quantity_requested);

                    $previouslyReleased = $sppbDetail ? (float) \App\Models\GoodsReleaseItem::where('sppb_detail_id', $sppbDetail->id)
                        ->where('id', '!=', $item->id)
                        ->whereHas('goodsRelease', function($q) use ($record) {
                            $q->where('status', '!=', 'CANCELLED');
                            if ($record->id) {
                                $q->where('id', '<', $record->id);
                            }
                        })
                        ->sum('quantity_released') : 0.0;

                    $qtyKirimIni = (float) $item->quantity_released;
                    $sisaSppb = max(0.0, $qtyAwal - ($previouslyReleased + $qtyKirimIni));

                    $totalAwal += $qtyAwal;
                    $totalTerkirimBefore += $previouslyReleased;
                    $totalKirimIni += $qtyKirimIni;
                    $totalSisa += $sisaSppb;
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $itemType }}</td>
                    <td>
                        <span class="item-name-normal">{{ $sppbDetail?->item_asset_name }}</span>
                        @if(!empty($codeValue) && $codeValue !== '-')
                            <br><span class="barcode-bold">{{ $isAsset ? 'Barcode' : 'Kode' }}: {{ $codeValue }}</span>
                        @endif
                    </td>
                    <td class="right">{{ number_format($qtyAwal, 2) }}</td>
                    <td class="right">{{ number_format($previouslyReleased, 2) }}</td>
                    <td class="right"><b>{{ number_format($qtyKirimIni, 2) }}</b></td>
                    <td class="right">{{ number_format($sisaSppb, 2) }}</td>
                    <td class="center">{{ $sppbDetail?->unit?->name }}</td>
                    <td class="center">{{ $item->condition_on_release ?? '-' }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="right">
                        <b>TOTAL</b>
                    </td>
                    <td class="right"><b>{{ number_format($totalAwal, 2) }}</b></td>
                    <td class="right"><b>{{ number_format($totalTerkirimBefore, 2) }}</b></td>
                    <td class="right"><b>{{ number_format($totalKirimIni, 2) }}</b></td>
                    <td class="right"><b>{{ number_format($totalSisa, 2) }}</b></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        @endif
    </table>
</div>

<!-- ====================================================== -->
<!-- INFORMASI PENERIMAAN BARANG -->
<!-- ====================================================== -->

<div class="section section-penerimaan">
    <div class="section-title">
        {{ $hasSppb ? '5' : '4' }}. Informasi Penerimaan Barang
    </div>
    <div class="p8">
        <table>
            <tr>
                <td width="55%" valign="top">
                    <table class="info">
                        <tr>
                            <td class="label">Nama Penerima</td>
                            <td>:</td>
                            <td>{{ $record->recipient_name ?? $record->receiver_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tgl. Diterima</td>
                            <td>:</td>
                            <td>{{ $record->received_at ? \Illuminate\Support\Carbon::parse($record->received_at)->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s').' WIB' : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Catatan Penerimaan</td>
                            <td>:</td>
                            <td>{!! nl2br(e($record->receiving_notes ?? $record->notes ?? '-')) !!}</td>
                        </tr>
                    </table>
                </td>
                <td width="45%" valign="top" align="center">
                    <b>Tanda Tangan Penerima</b><br><br>
                    @if(!empty($record->recipient_signature))
                        @php
                            $sigSrc = str_starts_with($record->recipient_signature, 'data:image') || str_starts_with($record->recipient_signature, 'http')
                                ? $record->recipient_signature
                                : asset('storage/'.$record->recipient_signature);
                        @endphp
                        <img src="{{ $sigSrc }}" alt="Tanda Tangan Penerima" style="max-height: 65px; max-width: 180px; object-fit: contain;" /><br>
                    @else
                        <br><br><br>
                    @endif
                    ({{ strtoupper($record->recipient_name ?? $record->receiver_name ?? '.............................') }})
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- ====================================================== -->
<!-- TANDA TANGAN -->
<!-- ====================================================== -->

<div class="section section-persetujuan">
    <div class="section-title">
        {{ $hasSppb ? '6' : '5' }}. Persetujuan
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
                (........................................)
            </td>
        </tr>
    </table>
</div>

<!-- ====================================================== -->
<!-- FOOTER -->
<!-- ====================================================== -->

<div class="footer">
    <table style="width: 100%;">
        <tr>
            <td style="font-size: 9px; color: #333; line-height: 1.35;">
                Dicetak pada: {{ now()->setTimezone('Asia/Jakarta')->translatedFormat('d F Y H:i:s') }} WIB oleh {{ auth()->user()?->name ?? 'Sistem' }}<br>
                Scan QR Code untuk memverifikasi keaslian & status resmi Surat Jalan.<br>
                <span class="multi-page-info">Dokumen ini merupakan satu kesatuan dokumen resmi yang sah.</span>
            </td>
            <td align="right" valign="bottom" style="font-size: 9.5px; font-weight: bold; white-space: nowrap;">
                Halaman <span class="page-number-current">1</span> dari <span class="page-number-total">1</span>
            </td>
        </tr>
    </table>
</div>

</div>

<script>
    function updatePageNumbers() {
        var pageElem = document.querySelector('.page');
        if (pageElem) {
            var header = pageElem.querySelector('.header');
            var sections = pageElem.querySelectorAll('.section');
            var footer = pageElem.querySelector('.footer');
            
            var totalContentHeight = (header ? header.offsetHeight : 0);
            sections.forEach(function(s) { totalContentHeight += s.offsetHeight + 5; });
            if (footer) totalContentHeight += footer.offsetHeight + 10;
            
            var a4Capacity = 1040;
            var totalPages = Math.max(1, Math.ceil(totalContentHeight / a4Capacity));
            
            var totalElems = document.querySelectorAll('.page-number-total');
            totalElems.forEach(function(el) { el.textContent = totalPages; });

            var currentElems = document.querySelectorAll('.page-number-current');
            currentElems.forEach(function(el) { el.textContent = '1'; });

            var multiNotice = document.querySelectorAll('.multi-page-info');
            if (totalPages > 1) {
                multiNotice.forEach(function(el) {
                    el.innerHTML = '<b>PERHATIAN:</b> Dokumen ini terdiri dari <b>' + totalPages + ' halaman</b>. Seluruh lembar merupakan satu kesatuan sah.';
                });
            } else {
                multiNotice.forEach(function(el) {
                    el.innerHTML = 'Dokumen ini merupakan satu kesatuan dokumen resmi yang sah.';
                });
            }
        }
    }

    window.addEventListener('DOMContentLoaded', updatePageNumbers);
    window.addEventListener('load', function() {
        updatePageNumbers();
        window.print();
    });
    window.addEventListener('beforeprint', updatePageNumbers);
</script>

</body>
</html>
