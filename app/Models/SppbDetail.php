<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SppbDetail extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (SppbDetail $detail) {
            if (empty($detail->line_no)) {
                $maxLineNo = self::where('sppb_header_id', $detail->sppb_header_id)->max('line_no');
                $detail->line_no = ($maxLineNo ?? 0) + 1;
            }

            if (empty($detail->item_asset_name)) {
                if ($detail->item_id) {
                    $item = Item::find($detail->item_id);
                    if ($item) {
                        $detail->item_asset_name = $item->name;
                        if (empty($detail->unit_id)) {
                            $detail->unit_id = $item->unit_id;
                        }
                    }
                } elseif ($detail->asset_id) {
                    $asset = Asset::find($detail->asset_id);
                    if ($asset) {
                        $detail->item_asset_name = $asset->asset_name;
                    }
                } else {
                    $detail->item_asset_name = '-';
                }
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sppb_header_id',
        'line_no',
        'barcode_confirmed',
        'item_id',
        'asset_id',
        'reference_code',
        'is_from_master',
        'item_asset_name',
        'unit_id',
        'quantity',
        'remarks',
        'delivery_status',
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
            'line_no' => 'integer',
            'barcode_confirmed' => 'boolean',
            'item_id' => 'integer',
            'asset_id' => 'integer',
            'is_from_master' => 'boolean',
            'unit_id' => 'integer',
            'quantity' => 'decimal:2',
        ];
    }

    public function sppbHeader(): BelongsTo
    {
        return $this->belongsTo(SppbHeader::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function goodsReleaseItems(): HasMany
    {
        return $this->hasMany(GoodsReleaseItem::class);
    }

    public function getDeliveryStatusAttribute($value): string
    {
        $activeReleases = $this->goodsReleaseItems()
            ->whereHas('goodsRelease', fn ($q) => $q->where('status', '!=', 'CANCELLED'))
            ->with('goodsRelease')
            ->get();

        if ($activeReleases->isEmpty()) {
            return ! empty($value) ? $value : 'PENDING';
        }

        $requested = (float) $this->quantity;

        $confirmedReleases = $activeReleases->filter(function ($item) {
            $status = strtoupper((string) ($item->goodsRelease?->status ?? ''));

            return in_array($status, ['DELIVERED', 'RECEIVED', 'COMPLETED']);
        });

        if ($confirmedReleases->isNotEmpty()) {
            $totalConfirmed = (float) $confirmedReleases->sum('quantity_released');
            if ($totalConfirmed >= $requested && $requested > 0) {
                return 'DELIVERED';
            }

            return 'PARTIALLY_DELIVERED';
        }

        $totalReleased = (float) $activeReleases->sum('quantity_released');
        if ($totalReleased >= $requested && $requested > 0) {
            return 'FULLY_RELEASED';
        }

        return 'PARTIALLY_RELEASED';
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        return match (strtoupper((string) $this->delivery_status)) {
            'DELIVERED', 'RECEIVED', 'COMPLETED' => 'Diterima / Terkirim',
            'PARTIALLY_DELIVERED' => 'Diterima Sebagian',
            'FULLY_RELEASED' => 'Pengiriman Penuh',
            'PARTIALLY_RELEASED' => 'Pengiriman Sebagian',
            'IN_TRANSIT', 'RELEASED' => 'Pengiriman Penuh',
            'DRAFT' => 'Draft Surat Jalan',
            default => 'Belum Dikirim',
        };
    }
}
