<?php

declare(strict_types=1);

namespace App\DTOs\Workflow;

final class ApprovalDecisionData
{
    public function __construct(
        public readonly int $workflowInstanceStepId,
        public readonly int $actorId,
        public readonly string $commandUuid,
        public readonly string $decision, // 'approve' | 'reject' | 'revision'
        public readonly ?string $remarks = null,
        public readonly ?int $delegatedFromId = null,
        public readonly ?string $correlationId = null,
        public readonly ?bool $requirePlantManager = null,
    ) {}
}
