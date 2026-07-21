<x-filament-widgets::widget>
    @php
        $imports = $this->getActiveImports();
    @endphp

    @if ($imports->isNotEmpty())
        <div wire:poll.2s class="space-y-4 mb-6">
            @foreach ($imports as $import)
                @php
                    $isCompleted = ! is_null($import->completed_at);
                    $totalRows = max(1, $import->total_rows);
                    $processedRows = min($import->processed_rows, $totalRows);
                    $failedRows = $import->getFailedRowsCount();
                    $successfulRows = $import->successful_rows;
                    $percentage = $totalRows > 0 ? min(100, (int) round(($processedRows / $totalRows) * 100)) : 0;
                    $importerLabel = match(true) {
                        str_contains($import->importer, 'Asset') => 'Impor Data Aset',
                        str_contains($import->importer, 'Item') => 'Impor Data Barang',
                        str_contains($import->importer, 'Department') => 'Impor Data Departemen',
                        str_contains($import->importer, 'Location') => 'Impor Data Lokasi',
                        str_contains($import->importer, 'Plant') => 'Impor Data Plant',
                        str_contains($import->importer, 'Unit') => 'Impor Data Satuan',
                        default => 'Impor Data',
                    };
                @endphp

                <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-5 shadow-sm transition-all duration-200">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $isCompleted ? 'bg-green-100 text-green-600 dark:bg-green-950/50 dark:text-green-400' : 'bg-primary-100 text-primary-600 dark:bg-primary-950/50 dark:text-primary-400' }}">
                                @if ($isCompleted)
                                    <x-heroicon-o-check-circle class="h-6 w-6" />
                                @else
                                    <x-heroicon-o-arrow-path class="h-6 w-6 animate-spin" />
                                @endif
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $importerLabel }}: {{ $import->file_name }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Status: {{ $isCompleted ? 'Selesai' : 'Sedang Berjalan di Background' }} &bull; {{ $import->created_at?->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($isCompleted)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 dark:bg-green-950/50 px-3 py-1 text-xs font-semibold text-green-700 dark:text-green-300 ring-1 ring-inset ring-green-600/20">
                                    <x-heroicon-s-check-circle class="h-4 w-4" /> Selesai (100%)
                                </span>
                                <button type="button" wire:click="dismissImport({{ $import->id }})" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg transition-colors" title="Tutup">
                                    <x-heroicon-m-x-mark class="h-5 w-5" />
                                </button>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 dark:bg-primary-950/50 px-3 py-1 text-xs font-semibold text-primary-700 dark:text-primary-300 ring-1 ring-inset ring-primary-600/20">
                                    <span class="relative flex h-2 w-2">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                                    </span>
                                    Memproses Background ({{ $percentage }}%)
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <span>Kemajuan Impor Data</span>
                            <span>{{ number_format($processedRows) }} / {{ number_format($totalRows) }} baris ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full h-3 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden p-0.5 border border-gray-200/50 dark:border-white/5">
                            <div class="h-full rounded-full transition-all duration-500 ease-out {{ $isCompleted ? 'bg-green-500' : 'bg-gradient-to-r from-primary-500 to-indigo-500' }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    <!-- Stats Badges -->
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-2.5 border-t border-gray-100 dark:border-white/5">
                        <div class="flex gap-4">
                            <span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400 font-semibold">
                                <x-heroicon-m-check-circle class="h-4 w-4" /> {{ number_format($successfulRows) }} baris berhasil
                            </span>
                            @if ($failedRows > 0)
                                <span class="inline-flex items-center gap-1 text-red-600 dark:text-red-400 font-semibold">
                                    <x-heroicon-m-exclamation-triangle class="h-4 w-4" /> {{ number_format($failedRows) }} baris gagal
                                </span>
                            @endif
                        </div>
                        <span class="text-gray-400">Waktu Mulai: {{ $import->created_at?->format('H:i:s') }} WIB</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-widgets::widget>
