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

        if (! $setting || $setting->value === null) {
            return $default;
        }

        if ($setting->value === '' && $setting->type !== 'boolean') {
            return $default;
        }

        $val = match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };

        if (in_array($key, ['logo_light', 'logo_dark', 'logo_favicon', 'logo_login', 'logo_pdf'], true) && is_string($val)) {
            if (str_starts_with($val, '[')) {
                $decoded = json_decode($val, true);

                return is_array($decoded) ? ($decoded[0] ?? $default) : $val;
            }
        }

        return $val;
    }

    /**
     * Set setting value by key.
     */
    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): self
    {
        if (in_array($key, ['logo_light', 'logo_dark', 'logo_favicon', 'logo_login', 'logo_pdf'], true)) {
            if (is_array($value)) {
                $value = reset($value) ?: null;
            }
        }

        if ($type === 'boolean') {
            $processedValue = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        } elseif (is_array($value)) {
            $processedValue = json_encode($value);
        } else {
            $processedValue = (string) ($value ?? '');
        }

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
