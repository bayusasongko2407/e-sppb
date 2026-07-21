<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Konfirmasi Tindakan Aset</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Proses import data dari file <strong>{{ $completion->original_name }}</strong> telah selesai diproses.
                Namun, terdapat <strong>{{ count($mismatches) }}</strong> aset yang ada di database saat ini tetapi <strong>tidak ditemukan</strong> dalam data upload excel yang baru.
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                Silakan tinjau daftar aset di bawah ini dan tentukan apakah data tersebut akan dihapus atau dinonaktifkan di database.
            </p>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
            <table class="w-full text-left border-collapse bg-white dark:bg-gray-900">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        <th class="px-6 py-3">No. Barcode</th>
                        <th class="px-6 py-3">Nama Aset</th>
                        <th class="px-6 py-3">Plant</th>
                        <th class="px-6 py-3">Lokasi</th>
                        <th class="px-6 py-3">Satuan</th>
                        <th class="px-6 py-3">Kondisi</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-sm text-gray-900 dark:text-gray-100">
                    @foreach ($mismatches as $asset)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-4 font-mono font-medium text-primary-600 dark:text-primary-400">{{ $asset['barcode'] }}</td>
                            <td class="px-6 py-4 font-semibold">{{ $asset['asset_name'] }}</td>
                            <td class="px-6 py-4">{{ $asset['plant']['name'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $asset['location']['name'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $asset['unit']['name'] ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    {{ $asset['condition'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    {{ $asset['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
            <x-filament::button
                wire:click="skip"
                color="gray"
                icon="heroicon-o-x-mark"
            >
                Biarkan Saja (Lewati)
            </x-filament::button>

            <x-filament::button
                wire:click="confirmDeactivate"
                color="warning"
                icon="heroicon-o-eye-slash"
            >
                Non-aktifkan di Database
            </x-filament::button>

            <x-filament::button
                wire:click="confirmDelete"
                color="danger"
                icon="heroicon-o-trash"
            >
                Hapus Permanen dari Database
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
