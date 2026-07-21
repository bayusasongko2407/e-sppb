<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use App\Models\AssetImportCompletion;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class ImportReview extends Page
{
    protected static ?string $title = 'Konfirmasi Data Import Aset';

    protected string $view = 'filament.pages.import-review';

    // Hide it from main navigation menu
    protected static bool $shouldRegisterNavigation = false;

    #[Url(as: 'id')]
    public ?string $completionUuid = null;

    public ?AssetImportCompletion $completion = null;

    public array $mismatches = [];

    public function mount(): void
    {
        if (empty($this->completionUuid)) {
            abort(404);
        }

        $this->completion = AssetImportCompletion::where('uuid', $this->completionUuid)
            ->where('status', 'PENDING')
            ->first();

        if (! $this->completion) {
            Notification::make()
                ->title('Proses Konfirmasi Selesai atau Tidak Ditemukan')
                ->warning()
                ->send();

            $this->redirect('/assets');

            return;
        }

        $barcodes = $this->completion->missing_barcodes ?? [];
        $this->mismatches = Asset::with(['plant', 'location', 'unit'])
            ->whereIn('barcode', $barcodes)
            ->get()
            ->toArray();

        if (empty($this->mismatches)) {
            $this->completion->update(['status' => 'PROCESSED']);
            Notification::make()
                ->title('Tidak ada barcode yang terlewat untuk dikonfirmasi')
                ->success()
                ->send();

            $this->redirect('/assets');
        }
    }

    public function confirmDelete(): void
    {
        $barcodes = $this->completion->missing_barcodes ?? [];

        // Delete them from database
        Asset::whereIn('barcode', $barcodes)->delete();

        $this->completion->update(['status' => 'PROCESSED']);

        Notification::make()
            ->title('Data aset berhasil dihapus dari database')
            ->success()
            ->send();

        $this->redirect('/assets');
    }

    public function confirmDeactivate(): void
    {
        $barcodes = $this->completion->missing_barcodes ?? [];

        // Deactivate them
        Asset::whereIn('barcode', $barcodes)->update(['is_active' => false]);

        $this->completion->update(['status' => 'PROCESSED']);

        Notification::make()
            ->title('Data aset berhasil dinonaktifkan')
            ->success()
            ->send();

        $this->redirect('/assets');
    }

    public function skip(): void
    {
        $this->completion->update(['status' => 'PROCESSED']);

        Notification::make()
            ->title('Konfirmasi dilewati tanpa mengubah data yang ada')
            ->info()
            ->send();

        $this->redirect('/assets');
    }
}
