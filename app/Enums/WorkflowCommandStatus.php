<?php

declare(strict_types=1);

namespace App\Enums;

enum WorkflowCommandStatus: string
{
    case QUEUED = 'QUEUED';
    case PROCESSING = 'PROCESSING';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
}
