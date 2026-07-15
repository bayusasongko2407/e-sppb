<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plant_id',
        'code',
        'name',
        'address',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (Location $location) {
            if (empty($location->code)) {
                $lastLocation = self::orderBy('id', 'desc')->first();
                $nextNumber = $lastLocation ? intval(substr($lastLocation->code, 4)) + 1 : 1;
                $location->code = 'LOC-'.str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'plant_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
