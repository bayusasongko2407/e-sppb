<?php

declare(strict_types=1);

namespace App\DTOs\Sppb;

final class CreateSppbData
{
    public function __construct(
        public readonly int $plantId,
        public readonly int $departmentId,
        public readonly int $requesterId,
        public readonly int $originLocationId,
        public readonly int $destinationLocationId,
        public readonly string $requestDate,
        public readonly string $purpose,
        public readonly ?string $neededName = null,
        public readonly ?string $dateNeeded = null,
        public readonly bool $isUrgent = false,
    ) {}
}
