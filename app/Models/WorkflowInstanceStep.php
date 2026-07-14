<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowInstanceStep extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'workflow_instance_id',
        'workflow_step_id',
        'sequence',
        'code',
        'name',
        'approver_type',
        'approval_mode',
        'minimum_approvals',
        'sla_hours',
        'status',
        'activated_at',
        'due_at',
        'acted_at',
        'acted_by_id',
        'remarks',
        'lock_version',
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
            'workflow_instance_id' => 'integer',
            'workflow_step_id' => 'integer',
            'sequence' => 'integer',
            'minimum_approvals' => 'integer',
            'sla_hours' => 'integer',
            'activated_at' => 'timestamp',
            'due_at' => 'timestamp',
            'acted_at' => 'timestamp',
            'acted_by_id' => 'integer',
            'lock_version' => 'integer',
        ];
    }

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function actedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by_id');
    }

    public function stepApprovers(): HasMany
    {
        return $this->hasMany(WorkflowStepApprover::class);
    }

    public function workflowStepApprovers(): HasMany
    {
        return $this->hasMany(WorkflowStepApprover::class);
    }

    public function sppbStatusLogs(): HasMany
    {
        return $this->hasMany(SppbStatusLog::class);
    }
}
