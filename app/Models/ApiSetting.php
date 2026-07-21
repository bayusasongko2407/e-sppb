<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'environment',
        'is_sandbox',
        'is_mock_approval_enabled',
        'webhook_url',
        'api_rate_limit',
        'extra_config',
    ];

    protected function casts(): array
    {
        return [
            'is_sandbox' => 'boolean',
            'is_mock_approval_enabled' => 'boolean',
            'api_rate_limit' => 'integer',
            'extra_config' => 'json',
        ];
    }
}
