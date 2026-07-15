<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DataExport;
use App\Services\DataExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateDataExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 1200; // 20 minutes

    public function __construct(
        public int $dataExportId
    ) {
        $this->onQueue('reports');
    }

    public function handle(DataExportService $service): void
    {
        $export = DataExport::find($this->dataExportId);

        if (! $export || $export->status !== 'QUEUED') {
            return;
        }

        $export->update(['status' => 'PROCESSING', 'processing_started_at' => now()]);

        try {
            // Simulated generation logic. In real world, this would use League\Csv or PhpSpreadsheet
            // to chunk query the dataset and write to stream.

            $content = "id,name,value\n1,Test,100\n";
            $checksum = hash('sha256', $content);
            $fileSize = strlen($content);

            $disk = 'private';
            $directory = 'exports/'.now()->format('Y/m');
            $storedName = $export->uuid.'.'.$export->format;
            $path = $directory.'/'.$storedName;

            Storage::disk($disk)->put($path, $content);

            $service->completeExport(
                $export,
                $disk,
                $path,
                $storedName,
                $fileSize,
                $checksum,
                1 // Simulated rows
            );

        } catch (\Throwable $e) {
            $service->failExport($export, 'EXPORT_FAILED', $e->getMessage());
            throw $e;
        }
    }
}
