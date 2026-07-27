<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnumControl extends Model
{
    use HasFactory, SecureRouteBinding;

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
