<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RunningNumber;
use Illuminate\Support\Facades\DB;

final class RunningNumberService
{
    public function generate(
        int $plantId,
        string $documentType,
        string $plantCode,
        ?string $deptCode = null,
    ): string {
        return DB::transaction(function () use ($plantId, $documentType, $plantCode, $deptCode) {
            $periodKey = now()->format('Ym');
            $year = now()->format('Y');

            $record = RunningNumber::where('plant_id', $plantId)
                ->where('document_type', $documentType)
                ->where('period_key', $periodKey)
                ->lockForUpdate()
                ->first();

            if (! $record) {
                $record = RunningNumber::create([
                    'plant_id' => $plantId,
                    'department_id' => null,
                    'document_type' => $documentType,
                    'period_key' => $periodKey,
                    'prefix' => $documentType,
                    'digits' => 4,
                    'last_number' => 0,
                    'lock_version' => 1,
                ]);
            }

            $next = $record->last_number + 1;
            $record->last_number = $next;
            $record->lock_version += 1;
            $record->save();

            $seq = str_pad((string) $next, (int) $record->digits, '0', STR_PAD_LEFT);

            if ($deptCode) {
                return "{$documentType}/{$plantCode}/{$deptCode}/{$year}/{$seq}";
            }

            return "{$documentType}/{$plantCode}/{$year}/{$seq}";
        });
    }
}
