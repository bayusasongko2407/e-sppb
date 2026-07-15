<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataExport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'command_uuid',
        'plant_id',
        'requested_by_id',
        'export_type',
        'dataset',
        'schema_version',
        'format',
        'status',
        'scope_snapshot',
        'filters',
        'columns',
        'options',
        'disk',
        'directory',
        'stored_name',
        'path',
        'mime_type',
        'file_size',
        'checksum_sha256',
        'total_rows',
        'processed_rows',
        'download_count',
        'processing_started_at',
        'completed_at',
        'expires_at',
        'last_downloaded_at',
        'error_code',
        'error_message',
        'lock_version',
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
            'requested_by_id' => 'integer',
            'schema_version' => 'integer',
            'scope_snapshot' => 'array',
            'filters' => 'array',
            'columns' => 'array',
            'options' => 'array',
            'file_size' => 'integer',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'download_count' => 'integer',
            'processing_started_at' => 'timestamp',
            'completed_at' => 'timestamp',
            'expires_at' => 'timestamp',
            'last_downloaded_at' => 'timestamp',
            'lock_version' => 'integer',
        ];
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
