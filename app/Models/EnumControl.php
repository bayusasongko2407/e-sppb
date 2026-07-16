<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnumControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_name',
        'column_name',
        'value',
        'label',
        'sequence',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'sequence' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
