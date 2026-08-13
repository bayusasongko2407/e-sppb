<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\EnumControl;

enum GoodsReleaseStatus: string
{
    case DRAFT = 'DRAFT';
    case RELEASED = 'RELEASED';
    case RECEIVED = 'RECEIVED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        $override = EnumControl::where('table_name', 'goods_releases')
            ->where('column_name', 'status')
            ->where('value', $this->value)
            ->where('is_active', true)
            ->value('label');

        if ($override) {
            return $override;
        }

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
