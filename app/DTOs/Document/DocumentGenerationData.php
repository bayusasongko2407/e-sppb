<?php

declare(strict_types=1);

namespace App\DTOs\Document;

use Illuminate\Support\Str;

readonly class DocumentGenerationData
{
    public function __construct(
        public string $documentType,
        public int $templateId,
        public int $plantId,
        public int $generatedById,
        public array $renderPayload,
        public ?int $sppbHeaderId = null,
        public ?int $goodsReleaseId = null,
        public ?int $supersedesId = null,
        public bool $isOfficial = false,
        public ?string $commandUuid = null,
    ) {}

    public function resolveCommandUuid(): string
    {
        return $this->commandUuid ?? Str::uuid()->toString();
    }
}
