<x-filament-panels::page>
    <div class="space-y-6">
        <!-- System Health Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/40 text-blue-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Base API Endpoint</div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white font-mono break-all">{{ $apiUrl }}</div>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Real-Time Latency</div>
                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $apiLatencyMs ?? '—' }} ms</div>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Database Status</div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $dbStatus }}</div>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/40 text-purple-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">QR Decoder Status</div>
                        <div class="text-sm font-semibold text-purple-600 dark:text-purple-400">{{ $qrDecoderStatus }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button wire:click="runDiagnostics" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Uji Latency & Diagnostik Ulang</span>
            </button>
        </div>

        <!-- Diagnostic Tester Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- QR Decoder Tester -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Tester Dekoder QR Terenkripsi</h3>
                </div>
                <p class="text-xs text-gray-500">Masukkan payload QR (Base64 encrypted, JSON string <code>{"iv":"...","value":"..."}</code>, nomor surat jalan, atau hash token) untuk menguji dekoder secara otomatis.</p>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Payload / String QR Code:</label>
                    <textarea wire:model="qrTestInput" rows="3" class="w-full text-xs font-mono p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" placeholder='Tempel string encrypted Base64 atau JSON {"iv": "...", "value": "...", "mac": "..."}'></textarea>
                </div>

                <button wire:click="testDecodeQr" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                    Dekode & Verifikasi Dokument
                </button>

                @if($qrTestResult)
                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 space-y-2 text-xs font-mono">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Metode Dekode:</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $qrTestResult['decoded_method'] }}</span>
                    </div>
                    <flex class="flex justify-between">
                        <span class="text-gray-500">Terenkripsi:</span>
                        <span class="font-semibold">{{ $qrTestResult['is_encrypted'] ? 'YA (Crypt::encryptString)' : 'TIDAK (Plain Text / Token)' }}</span>
                    </flex>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Hasil Dekripsi Target:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $qrTestResult['decrypted_target'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status Verifikasi:</span>
                        <span class="px-2 py-0.5 rounded text-white font-bold {{ $qrTestResult['verification_status'] === 'VALID' ? 'bg-green-600' : 'bg-red-600' }}">
                            {{ $qrTestResult['verification_status'] }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Waktu Eksekusi:</span>
                        <span>{{ $qrTestResult['execution_time_ms'] }} ms</span>
                    </div>

                    @if($qrTestResult['verification_data'])
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-gray-500 font-sans font-medium block mb-1">Preview Detail Dokumen:</span>
                        <pre class="bg-gray-800 text-green-400 p-2 rounded max-h-40 overflow-auto text-[10px]">{{ json_encode($qrTestResult['verification_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- QR Encryptor Helper Tool -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Generator Encrypt String QR</h3>
                </div>
                <p class="text-xs text-gray-500">Buat string QR terenkripsi berbasis Laravel Crypt untuk simulasi scanning PDF Surat Jalan.</p>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor Surat Jalan / Teks Polos:</label>
                    <input type="text" wire:model="qrEncryptInput" class="w-full text-xs font-mono p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" placeholder="misal: SJ-20260730-0001">
                </div>

                <button wire:click="testEncryptString" class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition">
                    Generate Encrypted String
                </button>

                @if($qrEncryptResult)
                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 space-y-2">
                    <span class="text-xs text-gray-500 font-medium">Hasil Encrypted Base64 Payload:</span>
                    <div class="p-2 bg-gray-800 text-purple-300 text-[10px] font-mono rounded break-all select-all">
                        {{ $qrEncryptResult }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent QR Code Validation Logs -->
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Log Pengujian & Verifikasi QR Code Terbaru</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold uppercase">
                        <tr>
                            <th class="px-4 py-2">Waktu</th>
                            <th class="px-4 py-2">Status Result</th>
                            <th class="px-4 py-2">Saluran</th>
                            <th class="px-4 py-2">UUID Verifikasi</th>
                            <th class="px-4 py-2">Lookup Fingerprint</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->recentValidations as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-4 py-2 font-mono whitespace-nowrap">{{ $log->verified_at ? ($log->verified_at instanceof \DateTimeInterface ? $log->verified_at->format('Y-m-d H:i:s') : \Illuminate\Support\Carbon::parse($log->verified_at)->format('Y-m-d H:i:s')) : '—' }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold text-white {{ $log->validation_result === 'VALID' ? 'bg-green-600' : 'bg-amber-600' }}">
                                    {{ $log->validation_result }}
                                </span>
                            </td>
                            <td class="px-4 py-2 font-medium">{{ $log->verification_channel }}</td>
                            <td class="px-4 py-2 font-mono text-[10px]">{{ $log->uuid }}</td>
                            <td class="px-4 py-2 font-mono text-[10px]">{{ substr($log->lookup_fingerprint_sha256 ?? '—', 0, 16) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada log verifikasi QR Code terdeteksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
