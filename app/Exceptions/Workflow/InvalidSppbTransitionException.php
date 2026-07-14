<?php

declare(strict_types=1);

namespace App\Exceptions\Workflow;

use RuntimeException;

final class InvalidSppbTransitionException extends RuntimeException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Transisi status SPPB tidak valid dari '{$from}' ke '{$to}'.");
    }
}
