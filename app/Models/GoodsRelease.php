<?php

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        'recipient_name',
        'recipient_signature',
        'receiving_notes',
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
            'received_at' => 'datetime',
            'received_by_id' => 'integer',
            'sender_user_id_id' => 'integer',
            'receiver_user_id_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GoodsRelease $release) {
            if (empty($release->uuid)) {
                $release->uuid = (string) Str::uuid();
            }

            if (empty($release->created_by_id)) {
                $release->created_by_id = auth()->id() ?? 1;
            }

            if (empty($release->release_sequence)) {
                if ($release->sppb_header_id) {
                    $release->release_sequence = static::where('sppb_header_id', $release->sppb_header_id)->count() + 1;
                } else {
                    $release->release_sequence = static::whereDate('created_at', today())->count() + 1;
                }
            }

            if (empty($release->release_number)) {
                $count = static::whereDate('created_at', today())->count() + 1;
                $release->release_number = 'SJ-'.now()->format('Ymd').'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            }

            if (empty($release->verification_hash)) {
                $release->verification_hash = hash('sha256', $release->release_number.uniqid('', true));
            }
        });

        static::created(function (GoodsRelease $release) {
            if ($release->sppb_header_id) {
                $release->sppbHeaders()->syncWithoutDetaching([$release->sppb_header_id]);
            }
            $release->syncSppbDetailsDeliveryStatus();
        });

        static::updated(function (GoodsRelease $release) {
            if ($release->sppb_header_id) {
                $release->sppbHeaders()->syncWithoutDetaching([$release->sppb_header_id]);
            }
            $release->syncSppbDetailsDeliveryStatus();
        });

        static::saved(function (GoodsRelease $release) {
            $release->syncSppbDetailsDeliveryStatus();
        });

        static::deleting(function (GoodsRelease $release) {
            $release->goodsReleaseItems()->delete();
            $release->sppbHeaders()->detach();
            DocumentGeneration::where('goods_release_id', $release->id)->delete();
        });
    }

    public function syncSppbDetailsDeliveryStatus(): void
    {
        if (! $this->sppb_header_id) {
            return;
        }

        $sppb = SppbHeader::with(['sppbDetails'])->find($this->sppb_header_id);
        if (! $sppb) {
            return;
        }

        $activeReleases = static::where('sppb_header_id', $sppb->id)
            ->where('status', '!=', 'CANCELLED')
            ->with('goodsReleaseItems')
            ->get();

        if ($activeReleases->isEmpty()) {
            if (in_array($sppb->status, ['RELEASE_IN_PROGRESS', 'COMPLETED'])) {
                $sppb->status = 'APPROVED';
                $sppb->completed_at = null;
                $sppb->save();
            }

            foreach ($sppb->sppbDetails as $detail) {
                $detail->delivery_status = 'PENDING';
                $detail->save();
            }

            return;
        }

        $isAllCompleted = true;

        foreach ($sppb->sppbDetails as $detail) {
            $confirmedItems = GoodsReleaseItem::where('sppb_detail_id', $detail->id)
                ->whereHas('goodsRelease', fn ($q) => $q->whereIn('status', ['DELIVERED', 'RECEIVED', 'COMPLETED']))
                ->get();

            $activeItems = GoodsReleaseItem::where('sppb_detail_id', $detail->id)
                ->whereHas('goodsRelease', fn ($q) => $q->where('status', '!=', 'CANCELLED'))
                ->get();

            $requested = (float) $detail->quantity;

            if ($activeItems->isEmpty()) {
                $detail->delivery_status = 'PENDING';
                $isAllCompleted = false;
            } elseif ($confirmedItems->isNotEmpty()) {
                $totalConfirmed = (float) $confirmedItems->sum('quantity_released');
                if ($totalConfirmed >= $requested && $requested > 0) {
                    $detail->delivery_status = 'DELIVERED';
                } else {
                    $detail->delivery_status = 'PARTIALLY_DELIVERED';
                    $isAllCompleted = false;
                }
            } else {
                $totalReleased = (float) $activeItems->sum('quantity_released');
                if ($totalReleased >= $requested && $requested > 0) {
                    $detail->delivery_status = 'FULLY_RELEASED';
                    $isAllCompleted = false;
                } else {
                    $detail->delivery_status = 'PARTIALLY_RELEASED';
                    $isAllCompleted = false;
                }
            }

            $detail->save();
        }

        $newHeaderStatus = $isAllCompleted ? 'COMPLETED' : 'RELEASE_IN_PROGRESS';
        if ($sppb->status !== $newHeaderStatus) {
            $sppb->status = $newHeaderStatus;
            if ($isAllCompleted) {
                $sppb->completed_at = now();
            } else {
                $sppb->completed_at = null;
            }
            $sppb->save();
        }
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
        return $this->belongsTo(User::class, 'created_by_id');
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
