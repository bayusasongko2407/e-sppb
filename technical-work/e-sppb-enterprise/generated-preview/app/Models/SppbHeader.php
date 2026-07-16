<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SppbHeader extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'document_number',
        'company_id',
        'plant_id',
        'department_id',
        'requester_id',
        'origin_location_id',
        'destination_location_id',
        'project_name',
        'request_date',
        'date_needed',
        'purpose',
        'is_urgent',
        'status',
        'revision_no',
        'current_workflow_instance_id',
        'current_step_sequence',
        'current_approver_id',
        'lock_version',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'completed_at',
        'rejected_reason',
        'cancelled_reason',
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
            'company_id' => 'integer',
            'plant_id' => 'integer',
            'department_id' => 'integer',
            'requester_id' => 'integer',
            'origin_location_id' => 'integer',
            'destination_location_id' => 'integer',
            'request_date' => 'date',
            'date_needed' => 'date',
            'is_urgent' => 'boolean',
            'revision_no' => 'integer',
            'current_workflow_instance_id' => 'integer',
            'current_step_sequence' => 'integer',
            'current_approver_id' => 'integer',
            'lock_version' => 'integer',
            'submitted_at' => 'timestamp',
            'approved_at' => 'timestamp',
            'rejected_at' => 'timestamp',
            'cancelled_at' => 'timestamp',
            'completed_at' => 'timestamp',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currentWorkflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function sppbDetails(): HasMany
    {
        return $this->hasMany(SppbDetail::class);
    }

    public function workflowInstances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    public function sppbStatusLogs(): HasMany
    {
        return $this->hasMany(SppbStatusLog::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function goodsReleases(): HasMany
    {
        return $this->hasMany(GoodsRelease::class);
    }
}
