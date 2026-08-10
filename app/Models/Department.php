<?php

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory, SecureRouteBinding;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plant_id',
        'code',
        'name',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Department $department): void {
            if ($department->hasDependentRecords()) {
                throw new \DomainException("Departemen '{$department->name}' tidak dapat dihapus karena masih digunakan oleh data lain.");
            }
        });
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function workflowTemplates(): HasMany
    {
        return $this->hasMany(WorkflowTemplate::class);
    }

    public function sppbHeaders(): HasMany
    {
        return $this->hasMany(SppbHeader::class);
    }

    public function runningNumbers(): HasMany
    {
        return $this->hasMany(RunningNumber::class);
    }

    public function documentAccesses(): HasMany
    {
        return $this->hasMany(DocumentAccess::class);
    }

    public function hasDependentRecords(): bool
    {
        return $this->users()->exists()
            || $this->workflowTemplates()->exists()
            || $this->sppbHeaders()->exists()
            || $this->runningNumbers()->exists()
            || $this->documentAccesses()->exists();
    }
}
