<?php

declare(strict_types=1);

namespace App\DTOs\GoodsRelease;

final readonly class GoodsReleaseItemData
{
    public function __construct(
        public int $sppbDetailId,
        public float $quantityReleased,
        public ?string $conditionOnRelease = null,
        public ?string $notes = null,
    ) {}
}
