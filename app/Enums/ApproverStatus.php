<?php

declare(strict_types=1);

namespace App\Enums;

enum ApproverStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case REVISION_REQUESTED = 'REVISION_REQUESTED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::REVISION_REQUESTED => 'Minta Revisi',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
