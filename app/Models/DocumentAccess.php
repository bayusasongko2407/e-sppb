<?php

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Database\Factories\DocumentAccessFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAccess extends Model
{
    /** @use HasFactory<DocumentAccessFactory> */
    use HasFactory, SecureRouteBinding;

    protected $fillable = [
        'user_id',
        'plant_id',
        'department_id',
        'module',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
