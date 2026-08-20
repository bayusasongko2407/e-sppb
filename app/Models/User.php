<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SecureRouteBinding;

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
        'phone',
        'email_verified_at',
        'password',
        'is_active',
        'last_login_at',
        'failed_login_attempts',
        'locked_until',
        'theme_color',
        'theme_font',
        'theme_preset',
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
        return $this->hasMany(User::class, 'manager_id');
    }

    public function workflowStepApprovers(): HasMany
    {
        return $this->hasMany(WorkflowStepApprover::class, 'approver_id');
    }

    public function delegationsGivens(): HasMany
    {
        return $this->hasMany(WorkflowDelegation::class, 'delegator_id');
    }

    public function delegationsReceiveds(): HasMany
    {
        return $this->hasMany(WorkflowDelegation::class, 'delegate_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(SppbHeader::class, 'requester_id');
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Attachment::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'actor_id');
    }

    public function documentAccesses(): HasMany
    {
        return $this->hasMany(DocumentAccess::class);
    }

    public function emailChangeRequests(): HasMany
    {
        return $this->hasMany(EmailChangeRequest::class);
    }

    /**
     * Check if user has specific document access based on module, action, plant, and department.
     */
    public function hasDocumentAccess(string $module, string $action, ?int $plantId = null, ?int $departmentId = null): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // 1. Spatie Permission Check
        $moduleToModelMap = [
            'sppb' => 'sppbheader',
            'goodsrelease' => 'goodsrelease',
            'goods_release' => 'goodsrelease',
        ];
        $model = $moduleToModelMap[$module] ?? $module;
        $spatieAction = $action === 'edit' ? 'update' : $action;

        $hasSpatieAccess = false;
        try {
            if ($spatieAction === 'view') {
                $hasSpatieAccess = $this->hasPermissionTo("view_{$model}")
                    || $this->hasPermissionTo("view_any_{$model}")
                    || $this->hasPermissionTo("create_{$model}")
                    || $this->hasPermissionTo("update_{$model}")
                    || $this->hasPermissionTo("delete_{$model}");
            } else {
                $hasSpatieAccess = $this->hasPermissionTo("{$spatieAction}_{$model}");
            }
        } catch (PermissionDoesNotExist $e) {
            $hasSpatieAccess = false;
        }

        // 2. DocumentAccess Matrix Check
        $roleIds = $this->roles->pluck('id')->toArray();

        $matrixQuery = DocumentAccess::query()
            ->where('module', $module)
            ->where(function ($q) use ($roleIds) {
                $q->where('user_id', $this->id);
                if (! empty($roleIds)) {
                    $q->orWhereIn('role_id', $roleIds);
                }
            });

        switch ($action) {
            case 'view':
                $matrixQuery->where(function ($q) {
                    $q->where('can_view', true)
                        ->orWhere('can_create', true)
                        ->orWhere('can_edit', true)
                        ->orWhere('can_delete', true);
                });
                break;
            case 'create':
                $matrixQuery->where('can_create', true);
                break;
            case 'edit':
                $matrixQuery->where('can_edit', true);
                break;
            case 'delete':
                $matrixQuery->where('can_delete', true);
                break;
            default:
                return false;
        }

        // If specific plant or department is requested, matrix MUST match
        if ($plantId !== null || $departmentId !== null) {
            if ($plantId !== null) {
                $matrixQuery->where(function ($q) use ($plantId) {
                    $q->where('plant_id', $plantId)
                        ->orWhereNull('plant_id');
                });
            }

            if ($departmentId !== null) {
                $matrixQuery->where(function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId)
                        ->orWhereNull('department_id');
                });
            }

            return $matrixQuery->exists();
        }

        // General module check (plantId and departmentId are null):
        return $matrixQuery->exists() || $hasSpatieAccess;
    }

    /**
     * Route notifications for WhatsApp channel.
     */
    public function routeNotificationForWhatsApp(): ?string
    {
        return $this->phone;
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->hasDependentRecords()) {
                throw new \DomainException("Pengguna '{$user->name}' tidak dapat dihapus karena memiliki riwayat transaksi, alur persetujuan, atau hirarki yang terkait.");
            }

            $user->userPositions()->delete();
            $user->documentAccesses()->delete();
            $user->emailChangeRequests()->delete();
        });
    }

    public function hasDependentRecords(): bool
    {
        return $this->subordinates()->exists()
            || $this->requests()->exists()
            || $this->workflowStepApprovers()->exists()
            || $this->delegationsGivens()->exists()
            || $this->delegationsReceiveds()->exists()
            || $this->activityLogs()->exists();
    }
}
