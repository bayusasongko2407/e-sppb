<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Form Upload -->
        <div class="lg:col-span-1 space-y-6">
            <div class="p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Form Import Master Data</h3>
                
                <form wire:submit.prevent="startImport" class="space-y-4">
                    {{ $this->form }}

                    <div class="pt-2">
                        <x-filament::button
                            type="submit"
                            size="md"
                            icon="heroicon-o-arrow-up-tray"
                            class="w-full"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>Mulai Proses Import</span>
                            <span wire:loading>Memproses File...</span>
                        </x-filament::button>
                    </div>
                </form>
            </div>

            <!-- Section Unduh Template Excel -->
            <div class="p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-800 pb-3">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        Download Template Excel
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Klik modul di bawah ini untuk mengunduh file template Excel (.xlsx) resmi:
                    </p>
                </div>

                <ul class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                    <li class="py-2.5 flex items-center justify-between">
                        <span class="font-medium text-gray-800 dark:text-gray-200">Template Master Plant</span>
                        <button type="button" wire:click="downloadSelectedTemplate('plants')" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-semibold hover:underline">
                            Download XLSX
                        </button>
                    </li>
                    <li class="py-2.5 flex items-center justify-between">
                        <span class="font-medium text-gray-800 dark:text-gray-200">Template Master Departemen</span>
                        <button type="button" wire:click="downloadSelectedTemplate('departments')" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-semibold hover:underline">
                            Download XLSX
                        </button>
                    </li>
                    <li class="py-2.5 flex items-center justify-between">
                        <span class="font-medium text-gray-800 dark:text-gray-200">Template Master Lokasi</span>
                        <button type="button" wire:click="downloadSelectedTemplate('locations')" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-semibold hover:underline">
                            Download XLSX
                        </button>
                    </li>
                    <li class="py-2.5 flex items-center justify-between">
                        <span class="font-medium text-gray-800 dark:text-gray-200">Template Master Satuan (Unit)</span>
                        <button type="button" wire:click="downloadSelectedTemplate('units')" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-semibold hover:underline">
                            Download XLSX
                        </button>
                    </li>
                    <li class="py-2.5 flex items-center justify-between">
                        <span class="font-medium text-gray-800 dark:text-gray-200">Template Master Barang (Item)</span>
                        <button type="button" wire:click="downloadSelectedTemplate('items')" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-semibold hover:underline">
                            Download XLSX
                        </button>
                    </li>
                    <li class="py-2.5 flex items-center justify-between">
                        <span class="font-medium text-gray-800 dark:text-gray-200">Template Master Aset</span>
                        <button type="button" wire:click="downloadSelectedTemplate('assets')" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-semibold hover:underline">
                            Download XLSX
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Petunjuk Penggunaan -->
            <div class="p-6 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 rounded-xl">
                <h4 class="text-sm font-bold text-amber-900 dark:text-amber-300 mb-2">Petunjuk & Ketentuan:</h4>
                <ul class="text-xs text-amber-800 dark:text-amber-400 space-y-2 list-disc list-inside">
                    <li>Gunakan file template <strong>XLSX</strong> resmi di atas.</li>
                    <li>Sistem hanya membaca <strong>Sheet 1</strong> pertama.</li>
                    <li>Baris pertama didefinisikan sebagai Header Kolom &amp; otomatis dilewati.</li>
                    <li>Data divalidasi dan dicocokkan berdasarkan kode unik (Code / Barcode).</li>
                    <li>Khusus modul <strong>Aset</strong>, jika barcode di DB tidak ada di file import, Anda akan diarahkan ke halaman tinjauan/konfirmasi tindakan.</li>
                </ul>
            </div>
        </div>

        <!-- Detail Progress & Real-time Logs -->
        <div class="lg:col-span-2 space-y-6">
            @if ($isProcessing || !empty($importLogs) || $errorMessage)
                <div class="p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Status &amp; Laporan Import</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Rincian pemrosesan data baris demi baris.</p>
                        </div>
                        @if ($isProcessing)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 animate-pulse">
                                <span class="h-2 w-2 rounded-full bg-primary-600 dark:bg-primary-400"></span>
                                Sedang Memproses
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                Selesai
                            </span>
                        @endif
                    </div>

                    <!-- Ringkasan Angka -->
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg text-center">
                            <span class="block text-xs font-medium text-gray-500 dark:text-gray-400">Total Baris</span>
                            <span class="block text-lg font-bold text-gray-950 dark:text-white mt-1">{{ $totalRows }}</span>
                        </div>
                        <div class="p-4 bg-primary-50/50 dark:bg-primary-950/20 rounded-lg text-center border border-primary-100/50 dark:border-primary-900/30">
                            <span class="block text-xs font-medium text-primary-600 dark:text-primary-400">Diproses</span>
                            <span class="block text-lg font-bold text-primary-950 dark:text-primary-300 mt-1">{{ $processedRows }}</span>
                        </div>
                        <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 rounded-lg text-center border border-emerald-100/50 dark:border-emerald-900/30">
                            <span class="block text-xs font-medium text-emerald-600 dark:text-emerald-400">Berhasil</span>
                            <span class="block text-lg font-bold text-emerald-950 dark:text-emerald-300 mt-1">{{ $successfulRows }}</span>
                        </div>
                        <div class="p-4 bg-danger-50/50 dark:bg-danger-950/20 rounded-lg text-center border border-danger-100/50 dark:border-danger-900/30">
                            <span class="block text-xs font-medium text-danger-600 dark:text-danger-400">Gagal</span>
                            <span class="block text-lg font-bold text-danger-950 dark:text-danger-300 mt-1">{{ $failedRows }}</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    @if ($totalRows > 0)
                        @php
                            $percentage = round(($processedRows / $totalRows) * 100);
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs font-medium">
                                <span class="text-gray-600 dark:text-gray-400">Kemajuan</span>
                                <span class="text-primary-600 dark:text-primary-400">{{ $percentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-primary-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endif

                    <!-- Global Error -->
                    @if ($errorMessage)
                        <div class="p-4 bg-danger-50 text-danger-800 dark:bg-danger-950/30 dark:text-danger-400 rounded-lg border border-danger-200/50 dark:border-danger-900/50 text-xs">
                            <h4 class="font-bold mb-1">Fatal Error pada File:</h4>
                            <p>{{ $errorMessage }}</p>
                        </div>
                    @endif

                    <!-- Logs Table -->
                    @if (!empty($importLogs))
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Log Detail Baris</h4>
                            <div class="max-h-80 overflow-y-auto rounded-lg border border-gray-100 dark:border-gray-800 divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                @foreach (array_reverse($importLogs) as $log)
                                    <div class="p-3 flex items-start justify-between gap-4 hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                        <div class="space-y-0.5">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">Baris #{{ $log['row'] }}</span>
                                            <p class="text-gray-600 dark:text-gray-400 font-mono">{{ $log['message'] }}</p>
                                        </div>
                                        @if ($log['status'] === 'SUCCESS')
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">OK</span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-danger-50 text-danger-700 dark:bg-danger-950 dark:text-danger-400">FAIL</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="p-12 text-center border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                    <x-filament::icon
                        icon="heroicon-o-document-arrow-up"
                        class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600"
                    />
                    <h3 class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">Belum Ada Import Berjalan</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Isi formulir dan upload berkas untuk melihat laporan detail kemajuan import.</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
