<?php

declare(strict_types=1);

namespace App\Exceptions\Workflow;

use RuntimeException;

final class StaleWorkflowCommandException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Perintah workflow ini sudah diproses sebelumnya.');
    }
}
