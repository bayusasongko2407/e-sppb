<?php

declare(strict_types=1);

namespace App\Exceptions\Workflow;

use RuntimeException;

final class SppbNotEditableException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Dokumen SPPB ini tidak dapat diedit pada status saat ini.');
    }
}
