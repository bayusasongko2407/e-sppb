<?php

declare(strict_types=1);

namespace App\Exceptions\Workflow;

use RuntimeException;

final class AmbiguousWorkflowTemplateException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Ditemukan lebih dari satu template workflow yang cocok. Konfigurasi template harus unik.');
    }
}
