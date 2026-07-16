<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait SecureRouteBinding
{
    /**
     * Get the value of the model's route key encrypted.
     */
    public function getRouteKey(): string
    {
        try {
            $encrypted = encrypt($this->getKey());

            return strtr($encrypted, '+/=', '-_~');
        } catch (\Throwable $e) {
            return parent::getRouteKey();
        }
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->resolveRouteBindingQuery($this, $value, $field)->first();
    }

    /**
     * Retrieve the model query for a bound value.
     *
     * @param  Builder  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Builder
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        try {
            $decrypted = decrypt(strtr((string) $value, '-_~', '+/='));

            return $query->where($field ?? $this->getKeyName(), $decrypted);
        } catch (\Throwable $e) {
            // Return a query that will fail (return no records) to trigger a 404
            return $query->whereRaw('1 = 0');
        }
    }
}
