<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStep extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'workflow_template_id',
        'sequence',
        'code',
        'name',
        'approver_type',
        'approver_user_id',
        'approver_position_id',
        'approver_role',
        'approval_mode',
        'minimum_approvals',
        'sla_hours',
        'allow_self_approval',
        'is_final',
        'configuration',
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
            'workflow_template_id' => 'integer',
            'sequence' => 'integer',
            'approver_user_id' => 'integer',
            'approver_position_id' => 'integer',
            'minimum_approvals' => 'integer',
            'sla_hours' => 'integer',
            'allow_self_approval' => 'boolean',
            'is_final' => 'boolean',
            'configuration' => 'array',
        ];
    }

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function approverUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approverPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function workflowInstanceSteps(): HasMany
    {
        return $this->hasMany(WorkflowInstanceStep::class);
    }
}
