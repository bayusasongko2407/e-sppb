<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentGeneration extends Model
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
        'document_template_id',
        'template_version',
        'plant_id',
        'sppb_header_id',
        'goods_release_id',
        'document_type',
        'document_number',
        'source_revision_no',
        'generation_no',
        'supersedes_id',
        'generated_by_id',
        'revoked_by_id',
        'status',
        'is_official',
        'plant_code_snapshot',
        'plant_name_snapshot',
        'render_payload',
        'source_checksum_sha256',
        'disk',
        'directory',
        'stored_name',
        'path',
        'mime_type',
        'file_size',
        'checksum_sha256',
        'page_count',
        'processing_started_at',
        'generated_at',
        'expires_at',
        'revoked_at',
        'revocation_reason',
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
            'document_template_id' => 'integer',
            'template_version' => 'integer',
            'plant_id' => 'integer',
            'sppb_header_id' => 'integer',
            'goods_release_id' => 'integer',
            'source_revision_no' => 'integer',
            'generation_no' => 'integer',
            'supersedes_id' => 'integer',
            'generated_by_id' => 'integer',
            'revoked_by_id' => 'integer',
            'is_official' => 'boolean',
            'render_payload' => 'array',
            'file_size' => 'integer',
            'page_count' => 'integer',
            'processing_started_at' => 'datetime',
            'generated_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    public function documentTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function sppbHeader(): BelongsTo
    {
        return $this->belongsTo(SppbHeader::class);
    }

    public function goodsRelease(): BelongsTo
    {
        return $this->belongsTo(GoodsRelease::class);
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(DocumentGeneration::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supersededBy(): HasOne
    {
        return $this->hasOne(DocumentGeneration::class);
    }

    public function documentPages(): HasMany
    {
        return $this->hasMany(DocumentPage::class);
    }

    public function documentValidations(): HasMany
    {
        return $this->hasMany(DocumentValidation::class);
    }
}
