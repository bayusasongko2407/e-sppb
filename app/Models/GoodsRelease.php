<?php

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class GoodsRelease extends Model
{
    use HasFactory, SecureRouteBinding;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'release_number',
        'manual_release_number',
        'sppb_header_id',
        'release_sequence',
        'is_manual',
        'created_by_id',
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
        'received_by_id',
        'status',
        'notes',
        'verification_hash',
        'sender_user_id_id',
        'receiver_user_id_id',
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
            'is_manual' => 'boolean',
            'created_by_id' => 'integer',
            'sender_user_id' => 'integer',
            'receiver_user_id' => 'integer',
            'delivery_date' => 'date:Y-m-d',
            'received_at' => 'timestamp',
            'received_by_id' => 'integer',
            'sender_user_id_id' => 'integer',
            'receiver_user_id_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (GoodsRelease $release) {
            if ($release->sppb_header_id) {
                $release->sppbHeaders()->syncWithoutDetaching([$release->sppb_header_id]);
            }
        });

        static::updated(function (GoodsRelease $release) {
            if ($release->sppb_header_id) {
                $release->sppbHeaders()->syncWithoutDetaching([$release->sppb_header_id]);
            }
        });
    }

    public function sppbHeader(): BelongsTo
    {
        return $this->belongsTo(SppbHeader::class);
    }

    public function sppbHeaders(): BelongsToMany
    {
        return $this->belongsToMany(SppbHeader::class, 'goods_release_sppb', 'goods_release_id', 'sppb_header_id');
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

    public function getStatusAttribute($value): string
    {
        if ($value === 'RELEASED' && $this->delivery_date) {
            $deliveryDate = Carbon::parse($this->delivery_date)->startOfDay();
            if ($deliveryDate->lt(today())) {
                return 'RECEIVED';
            }
        }

        return $value;
    }
}
