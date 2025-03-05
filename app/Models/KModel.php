<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KModel extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();
    }

    /**
     * Generates a key to be used for caching
     *
     * @param array $options
     * @return string
     */
    static function cacheKey(array $options = []): string
    {
        // Start with the table name
        $keyParts = [get_called_class()::TABLE_NAME];

        // Iterate through the $options array
        foreach ($options as $key => $value) {
            // Skip keys with empty values (excluding false, -1, etc.)
            if ($value === null || $value === '') {
                continue;
            }

            // If the value is an array, convert it to a string representation
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            // Append the key and value to $keyParts
            $keyParts[] = $key . '=' . $value;
        }

        // Concatenate the key parts
        $key = implode(';', $keyParts);

        return md5($key);
    }

    /**
     * Get the user's preferred "tv rating".
     *
     * @return int
     */
    static function getTvRatingSettings(): int
    {
        return (int) config('app.tv_rating') ?? 4;
    }
}
