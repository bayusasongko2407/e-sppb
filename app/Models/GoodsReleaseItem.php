<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReleaseItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (GoodsReleaseItem $item) {
            if ($item->goodsRelease && $item->sppb_detail_id) {
                $item->goodsRelease->syncSppbDetailsDeliveryStatus();
            }
        });

        static::deleted(function (GoodsReleaseItem $item) {
            if ($item->goodsRelease && $item->sppb_detail_id) {
                $item->goodsRelease->syncSppbDetailsDeliveryStatus();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'goods_release_id',
        'sppb_detail_id',
        'item_name',
        'item_type',
        'barcode_code',
        'unit_name',
        'quantity_requested',
        'quantity_released',
        'quantity_received',
        'condition_on_release',
        'condition_on_receipt',
        'is_checked',
        'notes',
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
            'goods_release_id' => 'integer',
            'sppb_detail_id' => 'integer',
            'quantity_requested' => 'decimal:2',
            'quantity_released' => 'decimal:2',
            'quantity_received' => 'decimal:2',
            'is_checked' => 'boolean',
        ];
    }

    public function goodsRelease(): BelongsTo
    {
        return $this->belongsTo(GoodsRelease::class);
    }

    public function sppbDetail(): BelongsTo
    {
        return $this->belongsTo(SppbDetail::class);
    }
}
