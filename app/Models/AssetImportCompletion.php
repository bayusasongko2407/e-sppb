<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetImportCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'requested_by_id',
        'stored_name',
        'original_name',
        'missing_barcodes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'missing_barcodes' => 'array',
            'requested_by_id' => 'integer',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }
}
