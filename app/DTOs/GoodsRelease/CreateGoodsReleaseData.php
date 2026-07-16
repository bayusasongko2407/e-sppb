<?php

declare(strict_types=1);

namespace App\DTOs\GoodsRelease;

final readonly class CreateGoodsReleaseData
{
    /**
     * @param  GoodsReleaseItemData[]  $items
     */
    public function __construct(
        public int $sppbHeaderId,
        public int $actorId,
        public array $items,
        public ?string $driverName = null,
        public ?string $vehicleNumber = null,
        public ?string $expeditionName = null,
        public ?string $deliveryDate = null,
        public ?string $notes = null,
    ) {}
}
