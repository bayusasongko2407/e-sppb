<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataImport extends Model
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
        'commit_command_uuid',
        'plant_id',
        'requested_by_id',
        'committed_by_id',
        'import_type',
        'schema_version',
        'status',
        'scan_status',
        'original_name',
        'stored_name',
        'disk',
        'directory',
        'path',
        'mime_type',
        'extension',
        'file_size',
        'checksum_sha256',
        'scope_snapshot',
        'options',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'validation_report_disk',
        'validation_report_path',
        'validation_report_checksum_sha256',
        'validation_started_at',
        'validated_at',
        'processing_started_at',
        'completed_at',
        'expires_at',
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
            'committed_by_id' => 'integer',
            'schema_version' => 'integer',
            'file_size' => 'integer',
            'scope_snapshot' => 'array',
            'options' => 'array',
            'total_rows' => 'integer',
            'valid_rows' => 'integer',
            'invalid_rows' => 'integer',
            'processed_rows' => 'integer',
            'successful_rows' => 'integer',
            'failed_rows' => 'integer',
            'validation_started_at' => 'datetime',
            'validated_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
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

    public function committedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
