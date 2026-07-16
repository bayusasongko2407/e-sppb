<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'sppb_header_id',
        'sppb_detail_id',
        'original_name',
        'stored_name',
        'disk',
        'directory',
        'path',
        'mime_type',
        'extension',
        'file_size',
        'checksum_sha256',
        'uploaded_by',
        'scan_status',
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
            'sppb_header_id' => 'integer',
            'sppb_detail_id' => 'integer',
            'file_size' => 'integer',
            'uploaded_by' => 'integer',
        ];
    }

    public function sppbHeader(): BelongsTo
    {
        return $this->belongsTo(SppbHeader::class);
    }

    public function sppbDetail(): BelongsTo
    {
        return $this->belongsTo(SppbDetail::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
