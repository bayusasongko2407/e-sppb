<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SppbStatusLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sppb_header_id',
        'workflow_instance_id',
        'workflow_instance_step_id',
        'actor_id',
        'command_uuid',
        'action',
        'from_status',
        'to_status',
        'remarks',
        'metadata',
        'correlation_id',
        'logged_at',
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
            'sppb_header_id' => 'integer',
            'workflow_instance_id' => 'integer',
            'workflow_instance_step_id' => 'integer',
            'actor_id' => 'integer',
            'metadata' => 'array',
            'logged_at' => 'timestamp',
        ];
    }

    public function sppbHeader(): BelongsTo
    {
        return $this->belongsTo(SppbHeader::class);
    }

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function workflowInstanceStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstanceStep::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
