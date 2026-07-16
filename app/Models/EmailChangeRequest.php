<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailChangeRequest extends Model
{
    use HasFactory, SecureRouteBinding;

    protected $fillable = [
        'user_id',
        'old_email',
        'new_email',
        'status',
        'approved_by_id',
        'reason',
        'requested_at',
        'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'approved_by_id' => 'integer',
            'requested_at' => 'datetime',
            'acted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
