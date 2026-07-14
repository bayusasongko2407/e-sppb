<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\Workflow\ApprovalDecisionData;
use App\DTOs\Workflow\SubmitSppbData;
use App\Models\WorkflowCommand;
use App\Models\WorkflowInstance;
use App\Models\WorkflowInstanceStep;

interface WorkflowServiceContract
{
    public function queueSubmission(SubmitSppbData $data): WorkflowCommand;

    public function generateWorkflow(int $sppbHeaderId, string $correlationId): WorkflowInstance;

    public function queueApproval(ApprovalDecisionData $data): WorkflowCommand;

    public function approve(ApprovalDecisionData $data): WorkflowInstanceStep;

    public function reject(ApprovalDecisionData $data): WorkflowInstanceStep;

    public function requestRevision(ApprovalDecisionData $data): WorkflowInstanceStep;

    public function cancelWorkflow(int $sppbHeaderId, int $actorId, string $reason): void;
}
