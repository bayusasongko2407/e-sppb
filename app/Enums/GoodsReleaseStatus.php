<?php

declare(strict_types=1);

namespace App\Enums;

enum GoodsReleaseStatus: string
{
    case DRAFT = 'DRAFT';
    case RELEASED = 'RELEASED';
    case RECEIVED = 'RECEIVED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::RELEASED => 'Dikirim',
            self::RECEIVED => 'Diterima',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::RELEASED => 'info',
            self::RECEIVED => 'success',
            self::CANCELLED => 'danger',
        };
    }
}
