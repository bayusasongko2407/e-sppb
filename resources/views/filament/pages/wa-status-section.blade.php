@php
    $currentProvider = $this->data['wa_provider'] ?? $this->waStatusData['provider'] ?? 'meta_cloud';
    $providerLabel = $currentProvider === 'meta_cloud' 
        ? 'Official Meta WhatsApp Business Cloud API' 
        : 'Custom REST Gateway (wwebjs)';
@endphp

<div class="mt-4 p-4 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-xl space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-x-3">
            <div class="p-1.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.083.29.12.597.12.905 0 3.903-4.477 7.067-10 7.067a11.324 11.324 0 0 1-2.263-.223c-.702.996-1.895 2.03-3.678 2.518a.75.75 0 0 1-.79-.956c.645-1.953.298-3.419-.172-4.337C2.977 12.445 2.25 11.013 2.25 9.416c0-3.903 4.477-7.067 10-7.067 5.02 0 9.172 2.628 9.9 6.162Z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Status Koneksi: {{ $providerLabel }}</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pantau status koneksi engine WhatsApp terpilih dan lakukan uji coba pengiriman pesan.</p>
            </div>
        </div>
        <button type="button" wire:click="checkWaStatus" class="inline-flex items-center gap-x-1 px-2.5 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Refresh Status
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
        <!-- Status & Test Send -->
        <div class="space-y-4">
            <div class="flex items-center gap-x-2">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Engine Terpilih:</span>
                <span class="inline-flex items-center gap-x-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-sky-100 dark:bg-sky-950 text-sky-700 dark:text-sky-400 border border-sky-200 dark:border-sky-800">
                    {{ $providerLabel }}
                </span>
            </div>

            <div class="flex items-center gap-x-2">
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Status Koneksi:</span>
                @if ($this->waStatusData['connected'])
                    <span class="inline-flex items-center gap-x-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        CONNECTED (Terhubung)
                    </span>
                @elseif (($this->waStatusData['status_label'] ?? '') === 'PAIRING_REQUIRED')
                    <span class="inline-flex items-center gap-x-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        PERLU SCAN QR
                    </span>
                @else
                    <span class="inline-flex items-center gap-x-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        DISCONNECTED (Terputus)
                    </span>
                @endif
            </div>

            <p class="text-xs text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-900 p-2.5 rounded-lg border border-gray-200 dark:border-gray-800">
                {{ $this->waStatusData['message'] ?? 'Tidak ada pesan status.' }}
            </p>

            <div class="space-y-2 pt-2 border-t border-gray-200 dark:border-gray-800">
                <label for="test_wa_recipient" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                    Nomor WhatsApp Penerima Uji Coba:
                </label>
                <div class="flex gap-x-2">
                    <input 
                        type="text" 
                        id="test_wa_recipient" 
                        wire:model="test_wa_recipient" 
                        placeholder="Contoh: 628123456789" 
                        class="block w-full max-w-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500" 
                    />
                    <button 
                        type="button" 
                        wire:click="sendTestWa" 
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-semibold text-white bg-sky-600 dark:bg-sky-500 hover:bg-sky-500 dark:hover:bg-sky-400 rounded-lg shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-600 transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                        Kirim Uji Coba
                    </button>
                </div>
            </div>
        </div>

        <!-- Provider Specific Card / QR Code -->
        <div class="flex flex-col items-center justify-center p-4 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 min-h-[160px]">
            @if ($currentProvider === 'meta_cloud')
                @if ($this->waStatusData['connected'])
                    <div class="text-center space-y-2 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-10 h-10 mx-auto text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <p class="text-xs font-semibold">Official Meta Cloud API Terverifikasi</p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Autentikasi Token Meta berhasil. Siap mengirim pesan notifikasi.</p>
                    </div>
                @else
                    <div class="text-center space-y-2 text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Belum Terhubung ke Meta Cloud API</p>
                        <p class="text-[11px] text-gray-400">Masukkan <strong>Phone Number ID</strong> & <strong>Permanent Access Token</strong> dari Meta Developer Portal, lalu klik Refresh Status.</p>
                    </div>
                @endif
            @else
                @if (!empty($this->waStatusData['qr_code']))
                    <div class="text-center space-y-2">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300">Scan QR Code dengan WhatsApp Bot:</p>
                        <img src="{{ $this->waStatusData['qr_code'] }}" alt="WhatsApp Pairing QR Code" class="w-36 h-36 object-contain rounded border p-1 bg-white mx-auto" />
                    </div>
                @elseif ($this->waStatusData['connected'])
                    <div class="text-center space-y-2 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-10 h-10 mx-auto text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <p class="text-xs font-semibold">WhatsApp Custom Gateway Terhubung</p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">REST Gateway Node.js siap mengirimkan notifikasi secara otomatis.</p>
                    </div>
                @else
                    <div class="text-center space-y-2 text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                        </svg>
                        <p class="text-xs font-medium">QR Code belum tersedia / Server gateway offline.</p>
                        <p class="text-[11px] text-gray-400">Pastikan service Node.js berjalan, lalu klik "Refresh Status".</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
