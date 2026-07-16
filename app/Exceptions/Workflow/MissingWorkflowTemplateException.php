<?php

declare(strict_types=1);

namespace App\Exceptions\Workflow;

use RuntimeException;

final class MissingWorkflowTemplateException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Template workflow tidak ditemukan untuk SPPB ini.');
    }
}
