<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DataImport;
use App\Services\DataImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDataImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 3600; // 1 hour

    public function __construct(
        public int $dataImportId,
        public string $action = 'validate' // 'validate' or 'commit'
    ) {
        $this->onQueue('documents'); // default queue
    }

    public function handle(DataImportService $service): void
    {
        $import = DataImport::find($this->dataImportId);

        if (! $import) {
            return;
        }

        try {
            if ($this->action === 'validate' && $import->status === 'UPLOADED') {
                $this->validateImport($import);
            } elseif ($this->action === 'commit' && $import->status === 'PROCESSING') {
                $this->commitImport($import);
            }
        } catch (\Throwable $e) {
            $service->failImport($import, 'IMPORT_FAILED', $e->getMessage());
            throw $e;
        }
    }

    private function validateImport(DataImport $import): void
    {
        $import->update([
            'status' => 'VALIDATING',
            'scan_status' => 'CLEAN',
            'validation_started_at' => now(),
        ]);

        // Simulated validation logic
        $import->update([
            'status' => 'VALIDATED',
            'validated_at' => now(),
            'total_rows' => 100,
            'valid_rows' => 100,
            'invalid_rows' => 0,
        ]);
    }

    private function commitImport(DataImport $import): void
    {
        // Simulated commit logic
        $import->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
            'processed_rows' => 100,
            'successful_rows' => 100,
            'failed_rows' => 0,
        ]);
    }
}
