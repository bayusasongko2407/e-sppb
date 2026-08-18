<div class="p-4 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-xl space-y-3">
    <div class="flex items-center gap-x-2 text-sky-700 dark:text-sky-300 font-semibold text-xs uppercase tracking-wider">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
        </svg>
        Panduan Variable Placeholder Template
    </div>
    <p class="text-xs text-sky-900 dark:text-sky-200 leading-relaxed">
        Gunakan tag berikut pada **Subjek Email**, **Isi Email**, maupun **Isi WhatsApp**. Sistem akan mengganti tag tersebut secara otomatis dengan data aktual transaksi:
    </p>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
        <div class="bg-white dark:bg-gray-900 p-2 rounded border border-sky-100 dark:border-sky-900">
            <code class="font-mono font-bold text-sky-600 dark:text-sky-400">{document_number}</code>
            <span class="block text-[11px] text-gray-500">Nomor SPPB / Surat Jalan</span>
        </div>
        <div class="bg-white dark:bg-gray-900 p-2 rounded border border-sky-100 dark:border-sky-900">
            <code class="font-mono font-bold text-sky-600 dark:text-sky-400">{requester_name}</code>
            <span class="block text-[11px] text-gray-500">Nama Pemohon SPPB</span>
        </div>
        <div class="bg-white dark:bg-gray-900 p-2 rounded border border-sky-100 dark:border-sky-900">
            <code class="font-mono font-bold text-sky-600 dark:text-sky-400">{approver_name}</code>
            <span class="block text-[11px] text-gray-500">Nama Approver / Verifikator</span>
        </div>
        <div class="bg-white dark:bg-gray-900 p-2 rounded border border-sky-100 dark:border-sky-900">
            <code class="font-mono font-bold text-sky-600 dark:text-sky-400">{status}</code>
            <span class="block text-[11px] text-gray-500">Status Dokumen Saat Ini</span>
        </div>
        <div class="bg-white dark:bg-gray-900 p-2 rounded border border-sky-100 dark:border-sky-900">
            <code class="font-mono font-bold text-sky-600 dark:text-sky-400">{notes}</code>
            <span class="block text-[11px] text-gray-500">Catatan Revisi / Penolakan</span>
        </div>
        <div class="bg-white dark:bg-gray-900 p-2 rounded border border-sky-100 dark:border-sky-900">
            <code class="font-mono font-bold text-sky-600 dark:text-sky-400">{url}</code>
            <span class="block text-[11px] text-gray-500">Link Tautan Akses Dokumen</span>
        </div>
    </div>
</div>
