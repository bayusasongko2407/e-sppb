<?php

declare(strict_types=1);

namespace App\Enums;

enum SppbStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMISSION_QUEUED = 'SUBMISSION_QUEUED';
    case WAITING_APPROVAL = 'WAITING_APPROVAL';
    case APPROVED = 'APPROVED';
    case REVISION_REQUIRED = 'REVISION_REQUIRED';
    case REJECTED = 'REJECTED';
    case CANCELLED = 'CANCELLED';
    case RELEASE_IN_PROGRESS = 'RELEASE_IN_PROGRESS';
    case COMPLETED = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMISSION_QUEUED => 'Sedang Diproses',
            self::WAITING_APPROVAL => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::REVISION_REQUIRED => 'Perlu Revisi',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
            self::RELEASE_IN_PROGRESS => 'Proses Pengiriman',
            self::COMPLETED => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SUBMISSION_QUEUED => 'warning',
            self::WAITING_APPROVAL => 'warning',
            self::APPROVED => 'success',
            self::REVISION_REQUIRED => 'warning',
            self::REJECTED => 'danger',
            self::CANCELLED => 'gray',
            self::RELEASE_IN_PROGRESS => 'info',
            self::COMPLETED => 'success',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::REVISION_REQUIRED]);
    }

    public function isCancellable(): bool
    {
        return in_array($this, [self::DRAFT, self::WAITING_APPROVAL, self::REVISION_REQUIRED, self::APPROVED]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::REJECTED, self::CANCELLED, self::COMPLETED]);
    }
}
