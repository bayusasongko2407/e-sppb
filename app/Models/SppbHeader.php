<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SppbHeader extends Model
{
    use HasFactory, HasUuids, SecureRouteBinding, SoftDeletes {
        SecureRouteBinding::resolveRouteBindingQuery insteadof HasUuids;
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected static function booted(): void
    {
        static::creating(function (SppbHeader $model) {
            if (empty($model->document_number)) {
                $year = date('Y');
                $month = date('m');
                $period = "{$year}-{$month}";

                $running = RunningNumber::where('document_type', 'SPPB')
                    ->where('plant_id', $model->plant_id)
                    ->where('period_key', $period)
                    ->lockForUpdate()
                    ->first();

                if (! $running) {
                    $running = RunningNumber::create([
                        'plant_id' => $model->plant_id,
                        'department_id' => $model->department_id,
                        'document_type' => 'SPPB',
                        'period_key' => $period,
                        'prefix' => 'SPPB/{PLN}/{DEP}/{YYYY}/{MM}/',
                        'digits' => 5,
                        'last_number' => 0,
                        'is_active' => true,
                    ]);
                }

                $running->last_number += 1;
                $running->save();

                $prefix = $running->prefix;
                $prefix = str_replace('{DD}', date('d'), $prefix);
                $prefix = str_replace('{MM}', date('m'), $prefix);
                $prefix = str_replace('{YY}', date('y'), $prefix);
                $prefix = str_replace('{YYYY}', date('Y'), $prefix);

                if (str_contains($prefix, '{DEP}')) {
                    $prefix = str_replace('{DEP}', $model->department?->code ?? 'NODEP', $prefix);
                }

                if (str_contains($prefix, '{PLN}')) {
                    $prefix = str_replace('{PLN}', $model->plant?->code ?? 'NOPLN', $prefix);
                }

                $model->document_number = $prefix.str_pad((string) $running->last_number, $running->digits, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'document_number',
        'plant_id',
        'department_id',
        'requester_id',
        'origin_location_id',
        'destination_location_id',
        'needed_name',
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
            'plant_id' => 'integer',
            'department_id' => 'integer',
            'requester_id' => 'integer',
            'origin_location_id' => 'integer',
            'destination_location_id' => 'integer',
            'request_date' => 'date:Y-m-d',
            'date_needed' => 'date:Y-m-d',
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

    /**
     * Alias untuk sppbDetails() — digunakan di WorkflowService.
     */
    public function details(): HasMany
    {
        return $this->hasMany(SppbDetail::class)->orderBy('line_no');
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

    public function goodsReleasesPivot(): BelongsToMany
    {
        return $this->belongsToMany(GoodsRelease::class, 'goods_release_sppb', 'sppb_header_id', 'goods_release_id');
    }

    public function getSppbNoAttribute(): ?string
    {
        return $this->document_number;
    }
}
