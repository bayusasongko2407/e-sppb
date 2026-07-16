<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStepApprover extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'workflow_instance_step_id',
        'approver_id',
        'delegated_from_id',
        'status',
        'acted_at',
        'remarks',
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
            'workflow_instance_step_id' => 'integer',
            'approver_id' => 'integer',
            'delegated_from_id' => 'integer',
            'acted_at' => 'timestamp',
        ];
    }

    public function workflowInstanceStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstanceStep::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function delegatedFrom(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
