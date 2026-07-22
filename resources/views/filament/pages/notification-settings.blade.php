<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <!-- Form Submit Bar -->
        <div class="flex items-center justify-end gap-x-4 pt-4 border-t border-gray-200 dark:border-gray-800">
            <x-filament::button type="submit" size="lg" icon="heroicon-m-check">
                Simpan Pengaturan Notifikasi
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
