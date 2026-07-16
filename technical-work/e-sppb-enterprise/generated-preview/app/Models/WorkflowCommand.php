<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowCommand extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'command_uuid',
        'command_type',
        'aggregate_type',
        'aggregate_id',
        'actor_id',
        'payload',
        'status',
        'attempts',
        'processed_at',
        'error_code',
        'error_message',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'aggregate_id' => 'integer',
            'actor_id' => 'integer',
            'payload' => 'array',
            'attempts' => 'integer',
            'processed_at' => 'timestamp',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
