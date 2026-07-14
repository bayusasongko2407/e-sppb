<?php

declare(strict_types=1);

namespace App\Exceptions\Workflow;

use RuntimeException;

final class UnauthorizedApprovalException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Anda tidak memiliki wewenang untuk melakukan tindakan persetujuan ini.');
    }
}
