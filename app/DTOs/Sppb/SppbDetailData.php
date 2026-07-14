<?php

declare(strict_types=1);

namespace App\DTOs\Sppb;

final class SppbDetailData
{
    public function __construct(
        public readonly bool $barcodeConfirmed,
        public readonly int $unitId,
        public readonly float $quantity,
        public readonly ?int $itemId = null,
        public readonly ?int $assetId = null,
        public readonly ?string $referenceCode = null,
        public readonly ?string $itemAssetName = null,
        public readonly ?string $remarks = null,
    ) {}
}
