<?php

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    use HasFactory, SecureRouteBinding;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'address',
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
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Plant $plant): void {
            if ($plant->hasDependentRecords()) {
                throw new \DomainException("Plant '{$plant->name}' tidak dapat dihapus karena masih digunakan oleh data departemen, lokasi, pengguna, atau transaksi.");
            }
        });
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function workflowTemplates(): HasMany
    {
        return $this->hasMany(WorkflowTemplate::class);
    }

    public function workflowDelegations(): HasMany
    {
        return $this->hasMany(WorkflowDelegation::class);
    }

    public function sppbHeaders(): HasMany
    {
        return $this->hasMany(SppbHeader::class);
    }

    public function runningNumbers(): HasMany
    {
        return $this->hasMany(RunningNumber::class);
    }

    public function hasDependentRecords(): bool
    {
        return $this->departments()->exists()
            || $this->locations()->exists()
            || $this->users()->exists()
            || $this->assets()->exists()
            || $this->workflowTemplates()->exists()
            || $this->workflowDelegations()->exists()
            || $this->sppbHeaders()->exists()
            || $this->runningNumbers()->exists();
    }
}
