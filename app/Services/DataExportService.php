<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DataExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DataExportService
{
    /**
     * Request an asynchronous data export.
     */
    public function requestExport(
        string $exportType,
        string $dataset,
        array $columns,
        int $requestedById,
        ?int $plantId = null,
        ?array $filters = null,
        ?array $options = null,
        string $format = 'csv'
    ): DataExport {
        return DB::transaction(function () use ($exportType, $dataset, $columns, $requestedById, $plantId, $filters, $options, $format) {
            $export = DataExport::create([
                'uuid' => Str::uuid()->toString(),
                'command_uuid' => Str::uuid()->toString(),
                'plant_id' => $plantId,
                'requested_by_id' => $requestedById,
                'export_type' => $exportType,
                'dataset' => $dataset,
                'format' => $format,
                'status' => 'QUEUED',
                'scope_snapshot' => ['plant_id' => $plantId],
                'filters' => $filters,
                'columns' => $columns,
                'options' => $options,
            ]);

            // App\Jobs\GenerateDataExportJob::dispatch($export->id);

            return $export;
        });
    }

    /**
     * Complete the export generation.
     */
    public function completeExport(
        DataExport $export,
        string $disk,
        string $path,
        string $storedName,
        int $fileSize,
        string $checksum,
        int $totalRows,
        int $expiresInDays = 7
    ): void {
        $export->update([
            'status' => 'READY',
            'disk' => $disk,
            'path' => $path,
            'directory' => dirname($path),
            'stored_name' => $storedName,
            'mime_type' => 'text/csv', // Or application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
            'file_size' => $fileSize,
            'checksum_sha256' => $checksum,
            'total_rows' => $totalRows,
            'processed_rows' => $totalRows,
            'completed_at' => now(),
            'expires_at' => now()->addDays($expiresInDays),
        ]);
    }

    /**
     * Fail the export generation.
     */
    public function failExport(DataExport $export, string $errorCode, string $errorMessage): void
    {
        $export->update([
            'status' => 'FAILED',
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);
    }
}
