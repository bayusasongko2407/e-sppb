<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentValidation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'document_generation_id',
        'document_page_id',
        'actor_id',
        'validation_result',
        'verification_channel',
        'lookup_fingerprint_sha256',
        'request_fingerprint_sha256',
        'ip_address_hash_sha256',
        'user_agent_hash_sha256',
        'correlation_id',
        'verified_at',
        'metadata',
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
            'document_page_id' => 'integer',
            'actor_id' => 'integer',
            'verified_at' => 'timestamp',
            'metadata' => 'array',
        ];
    }

    public function documentGeneration(): BelongsTo
    {
        return $this->belongsTo(DocumentGeneration::class);
    }

    public function documentPage(): BelongsTo
    {
        return $this->belongsTo(DocumentPage::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
