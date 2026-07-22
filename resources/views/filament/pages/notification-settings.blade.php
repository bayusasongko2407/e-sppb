<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <!-- OpenWA Gateway Live Status & QR Viewer Section -->
        <div class="mt-6 p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-4">
                <div class="flex items-center gap-x-3">
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg">
                        <x-heroicon-o-chat-bubble-left-right class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            Status Koneksi & QR Viewer OpenWA Gateway
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Monitor status terhubung dan pairing WhatsApp Bot Pengirim.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-x-3">
                    <button type="button" wire:click="checkWaStatus" class="inline-flex items-center gap-x-2 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition">
                        <x-heroicon-m-arrow-path class="w-4 h-4" />
                        Periksa Status Server
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                <div class="space-y-3">
                    <div class="flex items-center gap-x-3">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Gateway:</span>
                        @if ($waStatusData['connected'])
                            <span class="inline-flex items-center gap-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                CONNECTED (Terhubung)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-400">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                DISCONNECTED (Terputus)
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $waStatusData['message'] ?? 'Tidak ada pesan status.' }}
                    </p>

                    <div class="pt-4 space-y-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="space-y-1">
                            <label for="test_wa_recipient" class="text-xs font-semibold text-gray-750 dark:text-gray-350">
                                WhatsApp Penerima Uji Coba:
                            </label>
                            <div class="flex gap-x-2">
                                <input 
                                    type="text" 
                                    id="test_wa_recipient" 
                                    wire:model="test_wa_recipient" 
                                    placeholder="Contoh: 628123456789" 
                                    class="block w-full max-w-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                />
                                <x-filament::button
                                    type="button"
                                    color="info"
                                    size="sm"
                                    icon="heroicon-m-paper-airplane"
                                    wire:click="sendTestWa"
                                >
                                    Kirim WA Uji Coba
                                </x-filament::button>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label for="test_email_recipient" class="text-xs font-semibold text-gray-750 dark:text-gray-350">
                                Email Penerima Uji Coba:
                            </label>
                            <div class="flex gap-x-2">
                                <input 
                                    type="email" 
                                    id="test_email_recipient" 
                                    wire:model="test_email_recipient" 
                                    placeholder="Contoh: admin@perusahaan.com" 
                                    class="block w-full max-w-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                />
                                <x-filament::button
                                    type="button"
                                    color="gray"
                                    size="sm"
                                    icon="heroicon-m-envelope"
                                    wire:click="sendTestEmail"
                                >
                                    Kirim Email Uji Coba
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QR Viewer Box -->
                <div class="flex flex-col items-center justify-center p-4 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-950 min-h-[160px]">
                    @if (!empty($waStatusData['qr_code']))
                        <div class="text-center space-y-2">
                            <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Scan QR Code dengan WhatsApp Bot:</p>
                            <img src="{{ $waStatusData['qr_code'] }}" alt="OpenWA Pairing QR Code" class="w-40 h-40 object-contain rounded border p-1 bg-white" />
                        </div>
                    @elseif ($waStatusData['connected'])
                        <div class="text-center space-y-2 text-emerald-600 dark:text-emerald-400">
                            <x-heroicon-o-check-circle class="w-12 h-12 mx-auto" />
                            <p class="text-sm font-medium">Perangkat WhatsApp Terhubung</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Bot siap mengirimkan pesan notifikasi secara otomatis.</p>
                        </div>
                    @else
                        <div class="text-center space-y-2 text-gray-400 dark:text-gray-500">
                            <x-heroicon-o-qr-code class="w-12 h-12 mx-auto" />
                            <p class="text-xs font-medium">QR Code tidak tersedia / Server gateway offline.</p>
                            <p class="text-xs text-gray-400">Klik "Periksa Status Server" untuk me-refresh QR Code.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Form Submit Bar -->
        <div class="flex items-center justify-end gap-x-4 pt-4 border-t border-gray-200 dark:border-gray-800">
            <x-filament::button type="submit" size="lg" icon="heroicon-m-check">
                Simpan Pengaturan Notifikasi
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
