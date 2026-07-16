<?php

declare(strict_types=1);

namespace App\Exceptions\GoodsRelease;

use Exception;

class InvalidGoodsReleaseQuantityException extends Exception
{
    public function __construct(string $message = 'Kuantitas pelepasan barang melebihi sisa kuantitas yang diizinkan.')
    {
        parent::__construct($message);
    }
}
