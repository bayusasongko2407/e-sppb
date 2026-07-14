<?php

declare(strict_types=1);

namespace App\Exceptions\Workflow;

use RuntimeException;

final class DuplicateGoodsReleaseException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Surat Jalan untuk SPPB ini sudah dibuat.');
    }
}
