<div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-2">
    <!-- Card 1: Surat Jalan (SPPB) -->
    <a href="{{ \App\Filament\Resources\GoodsReleases\GoodsReleaseResource::getUrl('create', ['is_manual' => 0]) }}"
       class="flex flex-col justify-between p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm hover:border-primary-500 hover:ring-2 hover:ring-primary-500/20 transition-all duration-200 group cursor-pointer">
        <div>
            <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-950/30 text-primary-600 dark:text-primary-400 mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1 group-hover:text-primary-600 dark:group-hover:text-primary-400">
                📦 Surat Jalan (SPPB)
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Diterbitkan berdasarkan dokumen permohonan SPPB yang telah disetujui. Memiliki pelacakan kuantitas dan rilis parsial.
            </p>
        </div>
        <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center text-xs font-semibold text-primary-600 dark:text-primary-400">
            <span>Buat Surat Jalan SPPB</span>
            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </div>
    </a>

    <!-- Card 2: Surat Jalan Manual -->
    <a href="{{ \App\Filament\Resources\GoodsReleases\GoodsReleaseResource::getUrl('create', ['is_manual' => 1]) }}"
       class="flex flex-col justify-between p-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm hover:border-amber-500 hover:ring-2 hover:ring-amber-500/20 transition-all duration-200 group cursor-pointer">
        <div>
            <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1 group-hover:text-amber-600 dark:group-hover:text-amber-400">
                📝 Surat Jalan Manual
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Diterbitkan secara langsung tanpa referensi SPPB. Cocok untuk barang perbaikan/service, sampel vendor, retur, atau pengiriman darurat.
            </p>
        </div>
        <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center text-xs font-semibold text-amber-600 dark:text-amber-400">
            <span>Buat Surat Jalan Manual</span>
            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </div>
    </a>
</div>
