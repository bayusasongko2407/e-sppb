<?php

declare(strict_types=1);

namespace App\Enums;

enum WorkflowInstanceStepStatus: string
{
    case QUEUED = 'QUEUED';
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case REVISION_REQUESTED = 'REVISION_REQUESTED';
    case CANCELLED = 'CANCELLED';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::QUEUED => 'Antrian',
            self::PENDING => 'Menunggu',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::REVISION_REQUESTED => 'Minta Revisi',
            self::CANCELLED => 'Dibatalkan',
            self::EXPIRED => 'Kedaluwarsa',
        };
    }

    public function isActive(): bool
    {
        return $this === self::PENDING;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::APPROVED,
            self::REJECTED,
            self::REVISION_REQUESTED,
            self::CANCELLED,
            self::EXPIRED,
        ]);
    }
}
