<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex gap-4">
            <x-filament::button type="submit">
                Simpan Pengaturan
            </x-filament::button>

            <x-filament::button href="/docs/api" tag="a" target="_blank" color="gray" icon="heroicon-o-document-text">
                Lihat Dokumentasi API (Swagger)
            </x-filament::button>
        </div>
    </form>
    
    <div class="mt-8 bg-white dark:bg-gray-900 rounded-lg shadow ring-1 ring-gray-950/5 p-6">
        <h2 class="text-lg font-bold mb-2">Panduan Integrasi API Ujicoba</h2>
        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2 prose max-w-none">
            <p>Mode Sandbox berguna ketika Anda ingin memberikan akses ke pihak ketiga atau pengembang internal untuk melakukan uji coba endpoint SPPB tanpa mempengaruhi data transaksi asli.</p>
            <ul>
                <li><strong>Endpoint Dasar:</strong> <code>{{ url('/api/v1') }}</code></li>
                <li><strong>Autentikasi:</strong> Bearer Token. Anda dapat membuat token di menu profil pengguna.</li>
                <li><strong>Dokumentasi:</strong> Standar OpenAPI v3.1 tersedia pada tautan "Lihat Dokumentasi API" di atas. Dokumentasi akan menyesuaikan dengan endpoint yang tersedia pada saat itu.</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
