<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plant_id',
        'department_id',
        'manager_id',
        'nik',
        'name',
        'email',
        'email_verified_at',
        'password',
        'is_active',
        'last_login_at',
        'failed_login_attempts',
        'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'manager_id' => 'integer',
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'failed_login_attempts' => 'integer',
            'locked_until' => 'datetime',
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

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userPositions(): HasMany
    {
        return $this->hasMany(UserPosition::class);
    }

    /**
     * Alias untuk userPositions() — digunakan oleh ApproverResolver.
     */
    public function positions(): HasMany
    {
        return $this->hasMany(UserPosition::class);
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function workflowStepApprovers(): HasMany
    {
        return $this->hasMany(WorkflowStepApprover::class);
    }

    public function delegationsGivens(): HasMany
    {
        return $this->hasMany(WorkflowDelegation::class);
    }

    public function delegationsReceiveds(): HasMany
    {
        return $this->hasMany(WorkflowDelegation::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(SppbHeader::class);
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
