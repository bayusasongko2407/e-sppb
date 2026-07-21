<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    /**
     * Get setting value by key, with dynamic casting.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::find($key);

        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set setting value by key.
     */
    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): self
    {
        $processedValue = is_array($value) ? json_encode($value) : (string) $value;

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $processedValue,
                'group' => $group,
                'type' => $type,
            ]
        );
    }
}
