<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DataExport;
use App\Models\DocumentGeneration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredDocumentExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $this->purgeDataExports();
        $this->purgeDocumentGenerations();
    }

    private function purgeDataExports(): void
    {
        $exports = DataExport::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->whereNotNull('path')
            ->get();

        foreach ($exports as $export) {
            if ($export->disk && $export->path) {
                Storage::disk($export->disk)->delete($export->path);
            }

            $export->update([
                'status' => 'EXPIRED',
                'path' => null, // Path removed to indicate file deletion
                'disk' => null,
            ]);
        }
    }

    private function purgeDocumentGenerations(): void
    {
        $generations = DocumentGeneration::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->where('status', '!=', 'EXPIRED')
            ->get();

        foreach ($generations as $generation) {
            if ($generation->disk && $generation->path) {
                Storage::disk($generation->disk)->delete($generation->path);
            }

            $generation->update([
                'status' => 'EXPIRED',
                'path' => null,
                'disk' => null,
            ]);
        }
    }
}
