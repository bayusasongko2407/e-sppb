<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsRelease extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'release_number',
        'sppb_header_id',
        'release_sequence',
        'created_by',
        'sender_name',
        'sender_address',
        'receiver_name',
        'receiver_address',
        'sender_user_id',
        'receiver_user_id',
        'driver_name',
        'vehicle_number',
        'expedition_name',
        'delivery_date',
        'received_at',
        'received_by',
        'status',
        'notes',
        'verification_hash',
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
            'release_sequence' => 'integer',
            'created_by' => 'integer',
            'sender_user_id' => 'integer',
            'receiver_user_id' => 'integer',
            'delivery_date' => 'date',
            'received_at' => 'timestamp',
            'received_by' => 'integer',
        ];
    }

    public function sppbHeader(): BelongsTo
    {
        return $this->belongsTo(SppbHeader::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function receiverUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goodsReleaseItems(): HasMany
    {
        return $this->hasMany(GoodsReleaseItem::class);
    }
}
