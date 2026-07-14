<?php

declare(strict_types=1);

namespace App\Enums;

enum DeliveryStatus: string
{
    case PENDING = 'PENDING';
    case IN_TRANSIT = 'IN_TRANSIT';
    case DELIVERED = 'DELIVERED';

    public function label(): string
    {
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
