<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentPage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'document_generation_id',
        'verification_uuid',
        'page_number',
        'page_checksum_sha256',
        'qr_payload_checksum_sha256',
        'verification_token_hash',
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
            'document_generation_id' => 'integer',
            'page_number' => 'integer',
        ];
    }

    public function documentGeneration(): BelongsTo
    {
        return $this->belongsTo(DocumentGeneration::class);
    }

    public function documentValidations(): HasMany
    {
        return $this->hasMany(DocumentValidation::class);
    }
}
