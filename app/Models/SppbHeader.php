<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApproverStatus;
use App\Enums\SppbStatus;
use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
                $day = date('d');

                $periodMonth = "{$year}-{$month}";
                $periodYear = "{$year}";
                $periodDay = "{$year}-{$month}-{$day}";

                // Cari record running number aktif yang cocok dengan salah satu tipe periode reset
                $running = RunningNumber::where('document_type', 'SPPB')
                    ->where('plant_id', $model->plant_id)
                    ->whereIn('period_key', [$periodMonth, $periodYear, $periodDay, 'GLOBAL'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (! $running) {
                    // Cari templat acuan terdaftar untuk plant ini
                    $template = RunningNumber::where('document_type', 'SPPB')
                        ->where('plant_id', $model->plant_id)
                        ->where('is_active', true)
                        ->latest('id')
                        ->first();

                    $prefix = $template?->prefix ?? 'SPPB/{PLN}/{DEP}/{YYYY}/{MM}/';
                    $digits = $template?->digits ?? 5;
                    $deptId = $model->department_id;

                    // Tentukan kunci periode baru berdasarkan konfigurasi templat
                    $periodKey = $periodMonth;
                    if ($template) {
                        if (strlen($template->period_key) === 4 && is_numeric($template->period_key)) {
                            $periodKey = $periodYear; // Reset Tahunan
                        } elseif ($template->period_key === 'GLOBAL') {
                            $periodKey = 'GLOBAL'; // Kontinu Tanpa Reset
                        } elseif (strlen($template->period_key) === 10) {
                            $periodKey = $periodDay; // Reset Harian
                        }
                    }

                    $running = RunningNumber::create([
                        'plant_id' => $model->plant_id,
                        'department_id' => $deptId,
                        'document_type' => 'SPPB',
                        'period_key' => $periodKey,
                        'prefix' => $prefix,
                        'digits' => $digits,
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

            if (empty($model->verification_hash)) {
                $model->verification_hash = hash('sha256', ($model->document_number ?? 'SPPB').uniqid('', true));
            }

            if (empty($model->qr_code_url)) {
                $model->qr_code_url = url("/v1/verify/document/{$model->verification_hash}");
            }
        });

        static::deleting(function (SppbHeader $model) {
            if ($model->isForceDeleting()) {
                $model->sppbDetails()->delete();
                $model->sppbStatusLogs()->delete();
                $model->attachments()->delete();

                $instanceIds = $model->workflowInstances()->pluck('id')->toArray();
                if (! empty($instanceIds)) {
                    $stepIds = WorkflowInstanceStep::whereIn('workflow_instance_id', $instanceIds)->pluck('id')->toArray();
                    if (! empty($stepIds)) {
                        WorkflowStepApprover::whereIn('workflow_instance_step_id', $stepIds)->delete();
                        WorkflowInstanceStep::whereIn('id', $stepIds)->delete();
                    }
                    WorkflowCommand::whereIn('workflow_instance_id', $instanceIds)->delete();
                    WorkflowInstance::whereIn('id', $instanceIds)->delete();
                }

                DB::table('goods_release_sppb')->where('sppb_header_id', $model->id)->delete();
                $model->goodsReleases()->delete();
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
        'verification_hash',
        'qr_code_url',
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
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function markAsBatProcessingIfEligible(?User $user): bool
    {
        if (! $user || $this->status !== SppbStatus::WAITING_VERIFICATION_BAT->value) {
            return false;
        }

        $isBatApprover = WorkflowStepApprover::where('approver_id', $user->id)
            ->where('status', ApproverStatus::PENDING->value)
            ->whereHas('workflowInstanceStep', function ($query) {
                $query->where('workflow_instance_id', $this->current_workflow_instance_id)
                    ->where('sequence', $this->current_step_sequence)
                    ->where(function ($sub) {
                        $sub->where('code', 'like', '%BAT%')
                            ->orWhere('name', 'like', '%BAT%');
                    });
            })->exists();

        $hasBatPermission = false;
        try {
            $hasBatPermission = $user->hasPermissionTo('verify_bat');
        } catch (\Throwable) {
            $hasBatPermission = false;
        }

        $hasBatRoleOrPermission = $user->hasAnyRole(['BAT Verifier', 'BAT', 'Gudang', 'Verifikator BAT', 'admin', 'super_admin'])
            || $hasBatPermission;

        if ($isBatApprover || $hasBatRoleOrPermission) {
            $this->update(['status' => SppbStatus::PROCESS_VERIFICATION_BAT->value]);

            SppbStatusLog::create([
                'sppb_header_id' => $this->id,
                'workflow_instance_id' => $this->current_workflow_instance_id,
                'actor_id' => $user->id,
                'actor_name' => $user->name,
                'actor_nik' => $user->nik,
                'action' => 'BAT_OPENED',
                'status' => SppbStatus::PROCESS_VERIFICATION_BAT->value,
                'from_status' => SppbStatus::WAITING_VERIFICATION_BAT->value,
                'to_status' => SppbStatus::PROCESS_VERIFICATION_BAT->value,
                'logged_at' => now(),
            ]);

            return true;
        }

        return false;
    }
}
