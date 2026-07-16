<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\Sppb\CreateSppbData;
use App\DTOs\Sppb\SppbDetailData;
use App\Models\SppbDetail;
use App\Models\SppbHeader;
use App\Models\WorkflowCommand;

interface SppbServiceContract
{
    public function createDraft(CreateSppbData $data): SppbHeader;

    public function updateDraft(int $sppbHeaderId, CreateSppbData $data): SppbHeader;

    public function addDetail(int $sppbHeaderId, SppbDetailData $data): SppbDetail;

    public function removeDetail(int $sppbHeaderId, int $detailId): void;

    public function queueSubmit(int $sppbHeaderId, int $actorId): WorkflowCommand;

    public function cancel(int $sppbHeaderId, int $actorId, string $reason): void;

    public function getAllowedActions(int $sppbHeaderId, int $actorId): array;
}
