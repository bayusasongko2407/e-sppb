<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\EnumControl;

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
    case WAITING_VERIFICATION_BAT = 'WAITING_VERIFICATION_BAT';
    case PROCESS_VERIFICATION_BAT = 'PROCESS_VERIFICATION_BAT';
    case WAITING_APPROVAL_MANAGER = 'WAITING_APPROVAL_MANAGER';

    public function label(): string
    {
        $override = EnumControl::where('table_name', 'sppb_headers')
            ->where('column_name', 'status')
            ->where('value', $this->value)
            ->where('is_active', true)
            ->value('label');

        if ($override) {
            return $override;
        }

        return match ($this) {
            self::DRAFT => 'Draft',
            self::SUBMISSION_QUEUED => 'Sedang Diproses',
            self::WAITING_APPROVAL => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::REVISION_REQUIRED => 'Perlu Revisi',
            self::REJECTED => 'Ditolak',
            self::CANCELLED => 'Dibatalkan',
            self::RELEASE_IN_PROGRESS => 'Proses Pengeluaran Barang',
            self::COMPLETED => 'Selesai',
            self::WAITING_VERIFICATION_BAT => 'Menunggu Verifikasi BAT',
            self::PROCESS_VERIFICATION_BAT => 'Proses Verifikasi BAT',
            self::WAITING_APPROVAL_MANAGER => 'Menunggu Persetujuan Manager',
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
            self::WAITING_VERIFICATION_BAT => 'warning',
            self::PROCESS_VERIFICATION_BAT => 'info',
            self::WAITING_APPROVAL_MANAGER => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-pencil-square',
            self::SUBMISSION_QUEUED => 'heroicon-o-clock',
            self::WAITING_APPROVAL => 'heroicon-o-hand-raised',
            self::APPROVED => 'heroicon-o-check-circle',
            self::REVISION_REQUIRED => 'heroicon-o-arrow-uturn-left',
            self::REJECTED => 'heroicon-o-x-circle',
            self::CANCELLED => 'heroicon-o-no-symbol',
            self::RELEASE_IN_PROGRESS => 'heroicon-o-truck',
            self::COMPLETED => 'heroicon-o-check-badge',
            self::WAITING_VERIFICATION_BAT => 'heroicon-o-clock',
            self::PROCESS_VERIFICATION_BAT => 'heroicon-o-arrow-path',
            self::WAITING_APPROVAL_MANAGER => 'heroicon-o-hand-raised',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::REJECTED]);
    }

    public function isCancellable(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::WAITING_APPROVAL,
            self::WAITING_VERIFICATION_BAT,
            self::PROCESS_VERIFICATION_BAT,
            self::WAITING_APPROVAL_MANAGER,
            self::REJECTED,
            self::APPROVED,
        ]);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::CANCELLED, self::COMPLETED]);
    }
}
