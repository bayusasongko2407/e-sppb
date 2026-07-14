<?php

declare(strict_types=1);

namespace App\Exceptions\Workflow;

use RuntimeException;

final class QuantityExceededException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Kuantitas yang diinput melebihi jumlah yang disetujui.');
    }
}
