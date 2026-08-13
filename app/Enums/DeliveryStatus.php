<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\EnumControl;

enum DeliveryStatus: string
{
    case PENDING = 'PENDING';
    case IN_TRANSIT = 'IN_TRANSIT';
    case DELIVERED = 'DELIVERED';

    public function label(): string
    {
        $override = EnumControl::where('table_name', 'goods_releases')
            ->where('column_name', 'delivery_status')
            ->where('value', $this->value)
            ->where('is_active', true)
            ->value('label');

        if ($override) {
            return $override;
        }

        return match ($this) {
            self::PENDING => 'Menunggu Pengiriman',
            self::IN_TRANSIT => 'Sedang Dikirim',
            self::DELIVERED => 'Sudah Diterima',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::IN_TRANSIT => 'info',
            self::DELIVERED => 'success',
        };
    }
}
