<?php

declare(strict_types=1);

namespace App\Exceptions\Workflow;

use RuntimeException;

final class ApproverNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Tidak ada approver yang ditemukan untuk step workflow ini.');
    }
}
