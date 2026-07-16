<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowInstance extends Model
{
    use HasFactory, SecureRouteBinding;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'workflow_template_id',
        'sppb_header_id',
        'template_version',
        'revision_no',
        'status',
        'current_sequence',
        'started_at',
        'finished_at',
        'failure_code',
        'failure_message',
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
            'sppb_header_id' => 'integer',
            'template_version' => 'integer',
            'revision_no' => 'integer',
            'current_sequence' => 'integer',
            'started_at' => 'timestamp',
            'finished_at' => 'timestamp',
        ];
    }

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class);
    }

    public function sppbHeader(): BelongsTo
    {
        return $this->belongsTo(SppbHeader::class);
    }

    public function workflowInstanceSteps(): HasMany
    {
        return $this->hasMany(WorkflowInstanceStep::class);
    }

    public function sppbStatusLogs(): HasMany
    {
        return $this->hasMany(SppbStatusLog::class);
    }
}
