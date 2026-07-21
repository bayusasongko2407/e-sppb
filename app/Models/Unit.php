<?php

namespace App\Models;

use App\Traits\SecureRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory, SecureRouteBinding;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'category',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function sppbDetails(): HasMany
    {
        return $this->hasMany(SppbDetail::class);
    }

    /**
     * Get grouped units options by category for Filament Select dropdowns.
     *
     * @return array<string, array<int, string>>
     */
    public static function getGroupedOptions(): array
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (self $unit) => $unit->category ?: 'Lain-lain')
            ->map(function ($units) {
                $options = [];
                foreach ($units as $unit) {
                    $options[$unit->id] = "{$unit->name} ({$unit->code})";
                }

                return $options;
            })
            ->toArray();
    }
}
