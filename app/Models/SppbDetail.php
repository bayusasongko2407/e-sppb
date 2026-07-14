<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SppbDetail extends Model
{
    use HasFactory;

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
}
