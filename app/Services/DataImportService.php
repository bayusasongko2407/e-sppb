<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DataImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DataImportService
{
    /**
     * Upload an import file and begin the validation phase.
     */
    public function uploadImport(
        string $importType,
        int $requestedById,
        string $originalName,
        string $disk,
        string $path,
        string $mimeType,
        string $extension,
        int $fileSize,
        string $checksum,
        ?int $plantId = null,
        ?array $options = null
    ): DataImport {
        return DB::transaction(function () use (
            $importType, $requestedById, $originalName, $disk, $path, $mimeType, $extension, $fileSize, $checksum, $plantId, $options
        ) {
            $import = DataImport::create([
                'uuid' => Str::uuid()->toString(),
                'command_uuid' => Str::uuid()->toString(),
                'plant_id' => $plantId,
                'requested_by_id' => $requestedById,
                'import_type' => $importType,
                'status' => 'UPLOADED',
                'scan_status' => 'PENDING',
                'original_name' => $originalName,
                'stored_name' => basename($path),
                'disk' => $disk,
                'directory' => dirname($path),
                'path' => $path,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'file_size' => $fileSize,
                'checksum_sha256' => $checksum,
                'scope_snapshot' => ['plant_id' => $plantId],
                'options' => $options,
            ]);

            // App\Jobs\ProcessDataImportJob::dispatch($import->id, 'validate');

            return $import;
        });
    }

    /**
     * Commit a validated import.
     */
    public function commitImport(DataImport $import, int $committedById): void
    {
        if ($import->status !== 'VALIDATED') {
            throw new \Exception('Import must be in VALIDATED status before it can be committed.');
        }

        $import->update([
            'status' => 'PROCESSING',
            'committed_by_id' => $committedById,
            'commit_command_uuid' => Str::uuid()->toString(),
            'processing_started_at' => now(),
        ]);

        // App\Jobs\ProcessDataImportJob::dispatch($import->id, 'commit');
    }

    public function failImport(DataImport $import, string $errorCode, string $errorMessage): void
    {
        $import->update([
            'status' => 'FAILED',
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);
    }
}
