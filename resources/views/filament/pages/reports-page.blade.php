<x-filament-panels::page>
    <form wire:submit="filter">
        {{ $this->form }}
        
        <div class="flex flex-wrap gap-4 mt-4">
            <x-filament::button type="submit" color="primary">
                Tampilkan Preview
            </x-filament::button>
            
            <x-filament::button wire:click="exportExcel" color="success">
                Export Excel
            </x-filament::button>

            <x-filament::button wire:click="exportPdf" color="danger">
                Export PDF
            </x-filament::button>
        </div>
    </form>

    <div class="mt-8">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
