<?php

declare(strict_types=1);

namespace App\Enums;

enum WorkflowInstanceStatus: string
{
    case QUEUED = 'QUEUED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case REVISION_REQUIRED = 'REVISION_REQUIRED';
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';

    public function label(): string
    {
        return match ($this) {
            self::QUEUED => 'Antrian',
            self::IN_PROGRESS => 'Berjalan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::REVISION_REQUIRED => 'Perlu Revisi',
            self::CANCELLED => 'Dibatalkan',
            self::FAILED => 'Gagal',
        };
    }
}
