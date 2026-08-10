<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTemplate extends Model
{
    use HasFactory, HasUuids, SecureRouteBinding {
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

    /**
     * Default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'version' => 1,
        'document_type' => 'SPPB',
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'code',
        'name',
        'version',
        'plant_id',
        'department_id',
        'document_type',
        'description',
        'is_active',
        'effective_from',
        'effective_until',
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
            'version' => 'integer',
            'plant_id' => 'integer',
            'department_id' => 'integer',
            'is_active' => 'boolean',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (WorkflowTemplate $template): void {
            if ($template->hasDependentRecords()) {
                throw new \DomainException("Template workflow '{$template->name}' tidak dapat dihapus karena masih digunakan oleh dokumen SPPB / alur persetujuan aktif.");
            }

            $template->workflowSteps()->delete();
        });
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function workflowSteps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class);
    }

    public function workflowInstances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    public function hasDependentRecords(): bool
    {
        return $this->workflowInstances()->exists();
    }
}
