<?php

declare(strict_types=1);

namespace App\DTOs\Workflow;

final class SubmitSppbData
{
    public function __construct(
        public readonly int $sppbHeaderId,
        public readonly int $actorId,
        public readonly string $commandUuid,
        public readonly ?string $correlationId = null,
    ) {}
}
