<?php

declare(strict_types=1);

$targetDir = __DIR__.'/../docs/reference-images/menu-modules';

if (! is_dir($targetDir)) {
    mkdir($targetDir, 0775, true);
}

$modules = [
    [
        'file' => '00-dashboard.svg',
        'group' => 'Dashboard',
        'title' => 'Dashboard Operasional',
        'subtitle' => 'Ringkasan kondisi SPPB, approval, SLA, dan pelepasan barang.',
        'accent' => '#d97706',
        'icon' => 'dashboard',
        'stats' => ['Draft', 'Waiting Approval', 'Approval Saya', 'Overdue'],
        'flow' => ['Pantau volume dokumen', 'Prioritaskan approval', 'Lihat tren SLA'],
        'notes' => ['Widget berbasis scope Plant/Department', 'Chart status dan pelepasan bulanan', 'Tidak menampilkan data lintas kewenangan'],
    ],
    [
        'file' => '01-master-data.svg',
        'group' => 'Master Data',
        'title' => 'Peta Master Data',
        'subtitle' => 'Fondasi organisasi, katalog barang/aset, pengguna, dan struktur approval.',
        'accent' => '#0f766e',
        'icon' => 'grid',
        'stats' => ['Plant', 'Department', 'Location', 'User'],
        'flow' => ['Kelola referensi aktif', 'Validasi relasi Plant', 'Audit perubahan master'],
        'notes' => ['Plant adalah level organisasi tertinggi', 'Tidak ada Company/company_id', 'Master historis tidak boleh dihapus sembarang'],
    ],
    [
        'file' => '02-plant.svg',
        'group' => 'Master Data',
        'title' => 'Plant',
        'subtitle' => 'Unit organisasi tertinggi untuk scope data dan operasional SPPB.',
        'accent' => '#166534',
        'icon' => 'plant',
        'stats' => ['Kode unik', 'Nama Plant', 'Status aktif', 'Deskripsi'],
        'flow' => ['Buat Plant', 'Hubungkan Department', 'Gunakan sebagai scope'],
        'notes' => ['Semua Department dan Location berada di bawah Plant', 'Scope akses harus berbasis Plant', 'Plant nonaktif tidak dipakai transaksi baru'],
    ],
    [
        'file' => '03-department.svg',
        'group' => 'Master Data',
        'title' => 'Department',
        'subtitle' => 'Pemilik permintaan dan dasar penomoran/dokumen operasional.',
        'accent' => '#0369a1',
        'icon' => 'org',
        'stats' => ['Plant', 'Kode Dept', 'Nama Dept', 'Status'],
        'flow' => ['Pilih Plant', 'Isi kode unik per Plant', 'Aktifkan untuk requester'],
        'notes' => ['Kode unik per Plant', 'Dipakai filter inbox dan laporan', 'Jangan hapus bila sudah dipakai transaksi'],
    ],
    [
        'file' => '04-location.svg',
        'group' => 'Master Data',
        'title' => 'Location',
        'subtitle' => 'Lokasi asal dan tujuan pengiriman pada dokumen SPPB.',
        'accent' => '#0e7490',
        'icon' => 'pin',
        'stats' => ['Plant', 'Kode Lokasi', 'Alamat', 'Status'],
        'flow' => ['Daftarkan lokasi', 'Pakai sebagai origin/destination', 'Snapshot ke Surat Jalan'],
        'notes' => ['Origin dan destination harus valid', 'Alamat perlu lengkap untuk pelepasan barang', 'Lokasi nonaktif tidak dipilih pada dokumen baru'],
    ],
    [
        'file' => '05-unit.svg',
        'group' => 'Master Data',
        'title' => 'Unit',
        'subtitle' => 'Satuan kuantitas untuk detail barang dan pelepasan.',
        'accent' => '#7c2d12',
        'icon' => 'unit',
        'stats' => ['Kode Unit', 'Nama Unit', 'Status', 'Catatan'],
        'flow' => ['Tetapkan satuan', 'Pakai di item/detail', 'Konsisten pada release'],
        'notes' => ['Kuantitas memakai decimal', 'Satuan harus konsisten untuk agregasi', 'Nonaktifkan, jangan hapus bila historis'],
    ],
    [
        'file' => '06-position.svg',
        'group' => 'Master Data',
        'title' => 'Position',
        'subtitle' => 'Jabatan untuk pemetaan approver dan struktur organisasi.',
        'accent' => '#4338ca',
        'icon' => 'badge',
        'stats' => ['Plant', 'Department', 'Nama Jabatan', 'Status'],
        'flow' => ['Buat jabatan', 'Tetapkan user position', 'Resolve approver workflow'],
        'notes' => ['Dipakai strategy POSITION/manager/head', 'Pastikan user aktif', 'Perubahan tidak mengubah snapshot workflow berjalan'],
    ],
    [
        'file' => '07-user.svg',
        'group' => 'Master Data',
        'title' => 'User',
        'subtitle' => 'Akun pengguna, NIK/email login, status aktif, dan role permission.',
        'accent' => '#be123c',
        'icon' => 'user',
        'stats' => ['NIK', 'Email', 'Role', 'Plant Scope'],
        'flow' => ['Buat akun', 'Atur posisi dan role', 'Audit login/perubahan akses'],
        'notes' => ['Login via email atau NIK', 'User nonaktif harus ditolak login', 'Jangan simpan password default produksi'],
    ],
    [
        'file' => '08-item.svg',
        'group' => 'Master Data',
        'title' => 'Item',
        'subtitle' => 'Katalog barang non-aset untuk detail permintaan SPPB.',
        'accent' => '#4d7c0f',
        'icon' => 'box',
        'stats' => ['Kode Item', 'Nama', 'Unit', 'Spesifikasi'],
        'flow' => ['Pilih item aktif', 'Auto-fill spesifikasi', 'Masuk detail SPPB'],
        'notes' => ['Dipilih saat barcode_confirmed=false', 'Spesifikasi boleh disalin ke remarks', 'Status aktif membatasi input baru'],
    ],
    [
        'file' => '09-asset.svg',
        'group' => 'Master Data',
        'title' => 'Asset',
        'subtitle' => 'Katalog aset/barcode untuk permintaan berbasis aset.',
        'accent' => '#0f172a',
        'icon' => 'asset',
        'stats' => ['Barcode', 'Nama Aset', 'Lokasi', 'Status'],
        'flow' => ['Validasi barcode', 'Pilih aset aktif', 'Masuk detail SPPB'],
        'notes' => ['Dipilih saat barcode_confirmed=true', 'Item_id dan asset_id tidak boleh terisi bersamaan', 'Aset perlu terkait lokasi/Plant yang benar'],
    ],
    [
        'file' => '10-sppb.svg',
        'group' => 'SPPB',
        'title' => 'Dokumen SPPB',
        'subtitle' => 'Permintaan pemindahan/pengeluaran barang dari draft sampai selesai.',
        'accent' => '#b45309',
        'icon' => 'doc',
        'stats' => ['Draft', 'Submitted', 'Approved', 'Completed'],
        'flow' => ['Buat draft', 'Tambah detail dan lampiran', 'Submit ke workflow', 'Buat Surat Jalan'],
        'notes' => ['Mutasi harus lewat service dan queue', 'Nomor dokumen harus aman dari race condition', 'Timeline status wajib append-only'],
    ],
    [
        'file' => '11-my-approvals.svg',
        'group' => 'Persetujuan',
        'title' => 'Kotak Masuk Saya',
        'subtitle' => 'Daftar approval yang ditugaskan kepada approver aktif atau delegasi.',
        'accent' => '#2563eb',
        'icon' => 'inbox',
        'stats' => ['Pending', 'Overdue', 'Delegasi', 'Riwayat'],
        'flow' => ['Buka assignment', 'Review dokumen', 'Approve/Reject/Minta Revisi', 'Command diproses worker'],
        'notes' => ['Self-approval harus dicegah kecuali diizinkan template', 'Alasan wajib untuk reject/revision', 'Aksi ganda harus idempoten'],
    ],
    [
        'file' => '12-workflow-template.svg',
        'group' => 'Workflow',
        'title' => 'Workflow Template',
        'subtitle' => 'Konfigurasi step approval, approver strategy, SLA, dan versioning.',
        'accent' => '#6d28d9',
        'icon' => 'workflow',
        'stats' => ['Version', 'Step', 'Approver', 'SLA'],
        'flow' => ['Buat template', 'Atur step serial/quorum', 'Aktifkan versi', 'Snapshot saat submit'],
        'notes' => ['Perubahan template tidak mengubah instance berjalan', 'Template ambigu harus ditolak', 'Default legacy: BAT lalu Manager'],
    ],
    [
        'file' => '13-workflow-delegation.svg',
        'group' => 'Persetujuan',
        'title' => 'Workflow Delegation',
        'subtitle' => 'Delegasi approval sementara dengan batas waktu dan scope.',
        'accent' => '#7e22ce',
        'icon' => 'delegate',
        'stats' => ['Delegator', 'Delegate', 'Periode', 'Status'],
        'flow' => ['Ajukan delegasi', 'Validasi periode', 'Resolver memakai delegate', 'Audit keputusan'],
        'notes' => ['Delegasi harus aktif saat action', 'Tidak boleh melewati policy approval', 'Jejak delegated_from wajib tersimpan'],
    ],
    [
        'file' => '14-workflow-instance.svg',
        'group' => 'Monitoring',
        'title' => 'Lacak Dokumen',
        'subtitle' => 'Monitoring read-only workflow instance, step, approver, dan status command.',
        'accent' => '#0891b2',
        'icon' => 'monitor',
        'stats' => ['Instance', 'Step Aktif', 'Approver', 'Command'],
        'flow' => ['Cari SPPB', 'Lihat timeline', 'Deteksi stuck/failed', 'Tindak lanjut admin'],
        'notes' => ['Resource monitoring sebaiknya read-only', 'Tampilkan correlation_id untuk troubleshooting', 'Jangan mengubah status manual tanpa service'],
    ],
    [
        'file' => '15-goods-release.svg',
        'group' => 'Logistik & Gudang',
        'title' => 'Surat Jalan',
        'subtitle' => 'Pelepasan barang berdasarkan SPPB approved sampai penerimaan.',
        'accent' => '#c2410c',
        'icon' => 'truck',
        'stats' => ['Release No', 'Partial', 'Received', 'Completed'],
        'flow' => ['Pilih SPPB approved', 'Alokasi nomor SJ', 'Catat qty release', 'Konfirmasi penerimaan'],
        'notes' => ['Kuantitas release tidak boleh melebihi sisa', 'Snapshot pengirim/penerima wajib jelas', 'Pelepasan berulang harus idempoten'],
    ],
    [
        'file' => '16-attachment.svg',
        'group' => 'Lampiran',
        'title' => 'Attachment',
        'subtitle' => 'Lampiran privat pada level header SPPB dengan checksum dan scan status.',
        'accent' => '#475569',
        'icon' => 'clip',
        'stats' => ['Private Disk', 'Checksum', 'MIME', 'Scan'],
        'flow' => ['Upload aman', 'Validasi MIME/ukuran', 'Simpan private', 'Audit uploader'],
        'notes' => ['Jangan simpan lampiran di public disk', 'Nama file tersimpan harus acak', 'Original name hanya metadata tampilan'],
    ],
    [
        'file' => '17-activity-log.svg',
        'group' => 'Audit',
        'title' => 'Activity Log',
        'subtitle' => 'Jejak append-only untuk aktivitas penting, perubahan akses, dan transaksi.',
        'accent' => '#334155',
        'icon' => 'audit',
        'stats' => ['Actor', 'Module', 'Subject', 'Correlation'],
        'flow' => ['Tangkap event', 'Simpan old/new values aman', 'Filter audit', 'Ekspor laporan'],
        'notes' => ['Tidak boleh diedit/dihapus dari aplikasi', 'Log tidak boleh berisi password/token', 'Correlation ID memudahkan tracing'],
    ],
    [
        'file' => '18-role-permission.svg',
        'group' => 'Pengaturan Sistem',
        'title' => 'Role & Permission',
        'subtitle' => 'Manajemen RBAC berbasis Spatie Permission dan policy objek.',
        'accent' => '#991b1b',
        'icon' => 'shield',
        'stats' => ['Role', 'Permission', 'Policy', 'Audit'],
        'flow' => ['Kelola role non-sistem', 'Sinkronkan permission', 'Tetapkan ke user', 'Reset cache permission'],
        'notes' => ['Permission kanonik dikelola kode/seeder', 'Lindungi super admin terakhir', 'Cegah privilege escalation'],
    ],
    [
        'file' => '19-failed-command.svg',
        'group' => 'Monitoring',
        'title' => 'Failed Workflow Command',
        'subtitle' => 'Panel admin untuk melihat command gagal dan retry terkontrol.',
        'accent' => '#b91c1c',
        'icon' => 'warning',
        'stats' => ['Command UUID', 'Status', 'Attempts', 'Error'],
        'flow' => ['Review error aman', 'Perbaiki penyebab', 'Retry idempoten', 'Pantau worker'],
        'notes' => ['Jangan tampilkan payload sensitif', 'Retry harus lewat service/job', 'Failed command perlu correlation_id'],
    ],
];

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function wrapText(string $text, int $limit = 54): array
{
    $words = explode(' ', $text);
    $lines = [];
    $line = '';

    foreach ($words as $word) {
        $candidate = trim($line.' '.$word);
        if (mb_strlen($candidate) > $limit && $line !== '') {
            $lines[] = $line;
            $line = $word;
        } else {
            $line = $candidate;
        }
    }

    if ($line !== '') {
        $lines[] = $line;
    }

    return $lines;
}

function iconSvg(string $type, string $accent): string
{
    $stroke = esc($accent);

    return match ($type) {
        'dashboard' => "<rect x=\"92\" y=\"88\" width=\"56\" height=\"56\" rx=\"16\" fill=\"$stroke\" opacity=\"0.14\"/><path d=\"M76 168h168M96 144l32-38 34 24 48-62\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"10\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>",
        'truck' => "<path d=\"M70 132h108v54H70zM178 150h42l24 26v10h-66z\" fill=\"$stroke\" opacity=\"0.14\"/><path d=\"M86 194a16 16 0 1 0 0-32 16 16 0 0 0 0 32Zm122 0a16 16 0 1 0 0-32 16 16 0 0 0 0 32Z\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"8\"/>",
        'shield' => "<path d=\"M160 70l76 28v54c0 56-35 94-76 112-41-18-76-56-76-112V98z\" fill=\"$stroke\" opacity=\"0.12\"/><path d=\"M160 70l76 28v54c0 56-35 94-76 112-41-18-76-56-76-112V98z\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"8\"/><path d=\"M128 158l22 22 48-58\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"10\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>",
        'workflow' => "<circle cx=\"94\" cy=\"112\" r=\"28\" fill=\"$stroke\" opacity=\"0.14\"/><circle cx=\"218\" cy=\"112\" r=\"28\" fill=\"$stroke\" opacity=\"0.14\"/><circle cx=\"156\" cy=\"214\" r=\"28\" fill=\"$stroke\" opacity=\"0.14\"/><path d=\"M122 112h68M204 138l-34 52M108 138l34 52\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"8\" stroke-linecap=\"round\"/>",
        'inbox' => "<path d=\"M76 96h168l26 84v58H50v-58z\" fill=\"$stroke\" opacity=\"0.12\"/><path d=\"M50 180h70l18 28h44l18-28h70\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"8\" stroke-linejoin=\"round\"/><path d=\"M160 70v82m0 0l-30-30m30 30l30-30\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"9\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>",
        'doc' => "<path d=\"M94 62h100l48 48v150H94z\" fill=\"$stroke\" opacity=\"0.12\"/><path d=\"M194 62v54h48M120 150h84M120 184h84M120 218h52\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"8\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>",
        'user' => "<circle cx=\"160\" cy=\"112\" r=\"42\" fill=\"$stroke\" opacity=\"0.14\"/><path d=\"M82 244c12-48 44-72 78-72s66 24 78 72\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"10\" stroke-linecap=\"round\"/>",
        'plant' => "<path d=\"M160 248V126\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"9\" stroke-linecap=\"round\"/><path d=\"M160 134c-52-8-76-38-72-82 48 4 76 32 72 82Zm0 28c52-8 76-38 72-82-48 4-76 32-72 82Z\" fill=\"$stroke\" opacity=\"0.15\" stroke=\"$stroke\" stroke-width=\"6\"/>",
        'pin' => "<path d=\"M160 264s72-74 72-130a72 72 0 0 0-144 0c0 56 72 130 72 130Z\" fill=\"$stroke\" opacity=\"0.13\" stroke=\"$stroke\" stroke-width=\"8\"/><circle cx=\"160\" cy=\"134\" r=\"24\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"8\"/>",
        'clip' => "<path d=\"M108 170l76-76a42 42 0 0 1 60 60l-94 94a58 58 0 0 1-82-82l90-90\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"10\" stroke-linecap=\"round\"/>",
        'warning' => "<path d=\"M160 64l110 190H50z\" fill=\"$stroke\" opacity=\"0.13\" stroke=\"$stroke\" stroke-width=\"8\" stroke-linejoin=\"round\"/><path d=\"M160 128v58m0 36v2\" stroke=\"$stroke\" stroke-width=\"12\" stroke-linecap=\"round\"/>",
        default => "<rect x=\"76\" y=\"76\" width=\"168\" height=\"168\" rx=\"42\" fill=\"$stroke\" opacity=\"0.12\"/><path d=\"M112 128h96M112 164h96M112 200h64\" fill=\"none\" stroke=\"$stroke\" stroke-width=\"9\" stroke-linecap=\"round\"/>",
    };
}

function renderModule(array $module): string
{
    $accent = $module['accent'];
    $title = esc($module['title']);
    $group = esc($module['group']);
    $subtitle = esc($module['subtitle']);
    $icon = iconSvg($module['icon'], $accent);
    $subtitleLines = wrapText($module['subtitle'], 66);
    $subtitleSvg = '';
    foreach ($subtitleLines as $index => $line) {
        $subtitleSvg .= '<text x="72" y="'.(184 + ($index * 30)).'" class="subtitle">'.esc($line).'</text>';
    }

    $statsSvg = '';
    foreach ($module['stats'] as $index => $stat) {
        $x = 72 + ($index * 240);
        $statsSvg .= "<rect x=\"$x\" y=\"278\" width=\"204\" height=\"94\" rx=\"24\" class=\"stat\"/>";
        $statsSvg .= '<circle cx="'.($x + 38).'" cy="324" r="14" fill="'.esc($accent).'" opacity="0.18"/>';
        $statsSvg .= '<text x="'.($x + 64).'" y="316" class="stat-label">Area</text>';
        $statsSvg .= '<text x="'.($x + 64).'" y="346" class="stat-value">'.esc($stat).'</text>';
    }

    $flowSvg = '';
    foreach ($module['flow'] as $index => $step) {
        $x = 78 + ($index * 238);
        $flowSvg .= "<circle cx=\"$x\" cy=\"508\" r=\"30\" fill=\"".esc($accent).'"/>';
        $flowSvg .= '<text x="'.$x.'" y="518" text-anchor="middle" class="flow-no">'.($index + 1).'</text>';
        $flowSvg .= '<text x="'.($x + 46).'" y="500" class="flow-text">'.esc($step).'</text>';
        if ($index < count($module['flow']) - 1) {
            $flowSvg .= '<path d="M'.($x + 148).' 508h62" class="flow-line"/>';
        }
    }

    $notesSvg = '';
    foreach ($module['notes'] as $index => $note) {
        $y = 648 + ($index * 42);
        $notesSvg .= "<circle cx=\"96\" cy=\"$y\" r=\"7\" fill=\"".esc($accent).'"/>';
        $notesSvg .= '<text x="118" y="'.($y + 7).'" class="note">'.esc($note).'</text>';
    }

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="780" viewBox="0 0 1200 780" role="img" aria-labelledby="title desc">
  <title id="title">$title</title>
  <desc id="desc">$subtitle</desc>
  <defs>
    <linearGradient id="bg" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="#fff7ed"/>
      <stop offset="47%" stop-color="#f8fafc"/>
      <stop offset="100%" stop-color="#ecfeff"/>
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="20" stdDeviation="18" flood-color="#0f172a" flood-opacity="0.14"/>
    </filter>
    <style>
      .panel { fill: rgba(255,255,255,0.88); stroke: rgba(15,23,42,0.10); }
      .eyebrow { fill: #64748b; font: 700 20px Georgia, serif; letter-spacing: 0.18em; text-transform: uppercase; }
      .title { fill: #0f172a; font: 800 58px Georgia, serif; }
      .subtitle { fill: #475569; font: 400 24px "Trebuchet MS", sans-serif; }
      .section { fill: #0f172a; font: 800 28px Georgia, serif; }
      .stat { fill: rgba(255,255,255,0.78); stroke: rgba(15,23,42,0.10); }
      .stat-label { fill: #64748b; font: 700 14px "Trebuchet MS", sans-serif; letter-spacing: 0.12em; text-transform: uppercase; }
      .stat-value { fill: #0f172a; font: 800 22px "Trebuchet MS", sans-serif; }
      .flow-no { fill: #ffffff; font: 800 26px "Trebuchet MS", sans-serif; }
      .flow-text { fill: #0f172a; font: 700 20px "Trebuchet MS", sans-serif; }
      .flow-line { stroke: #cbd5e1; stroke-width: 6; stroke-linecap: round; }
      .note { fill: #334155; font: 500 22px "Trebuchet MS", sans-serif; }
      .watermark { fill: #0f172a; font: 700 14px "Trebuchet MS", sans-serif; letter-spacing: 0.14em; opacity: 0.38; }
    </style>
  </defs>
  <rect width="1200" height="780" fill="url(#bg)"/>
  <circle cx="1030" cy="90" r="190" fill="$accent" opacity="0.10"/>
  <circle cx="130" cy="730" r="220" fill="$accent" opacity="0.08"/>
  <rect x="36" y="34" width="1128" height="712" rx="42" class="panel" filter="url(#shadow)"/>
  <g transform="translate(832 72) scale(1.04)">$icon</g>
  <text x="72" y="100" class="eyebrow">$group</text>
  <text x="72" y="154" class="title">$title</text>
  $subtitleSvg
  $statsSvg
  <text x="72" y="450" class="section">Alur Utama</text>
  $flowSvg
  <text x="72" y="604" class="section">Catatan Desain</text>
  $notesSvg
  <text x="72" y="718" class="watermark">E-SPPB ENTERPRISE • REFERENSI MENU/MODUL • 2026-07-14</text>
</svg>
SVG;
}

foreach ($modules as $module) {
    file_put_contents($targetDir.'/'.$module['file'], renderModule($module));
}

$readme = "# Referensi Gambar Menu dan Modul E-SPPB\n\n";
$readme .= "Dibuat pada 2026-07-14. File SVG ini adalah referensi visual/dokumentasi untuk menu dan modul aplikasi, bukan implementasi UI produksi.\n\n";
$readme .= "| File | Group | Modul |\n";
$readme .= "| --- | --- | --- |\n";

foreach ($modules as $module) {
    $readme .= '| [`'.$module['file'].'`]('.$module['file'].') | '.$module['group'].' | '.$module['title']." |\n";
}

file_put_contents($targetDir.'/README.md', $readme);
