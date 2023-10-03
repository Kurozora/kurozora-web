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
        $key = get_called_class()::TABLE_NAME;

        // Add a name
        if (isset($options['name'])) $key .= '-' . $options['name'];

        // Add an ID
        if (isset($options['id'])) $key .= '-' . $options['id'];

        // Add a tv rating
        if (isset($options['tvRating'])) $key .= '-' . $options['tvRating'];

        // Add a limit
        if (isset($options['limit'])) $key .= '-' . $options['limit'];

        // Add a page
        if (isset($options['page'])) $key .= '-' . $options['page'];

        // Add a reversed
        if (isset($options['reversed'])) $key .= '-' . $options['reversed'];

        // Add a where
        if (isset($options['where'])) $key .= '-' . implode(',', array_map('implode', $options['where']));

        // Add a whereBetween
        if (isset($options['whereBetween'])) $key .= '-' . implode(',', $options['whereBetween']);

        return $key;
    }

    /**
     * Get the user's preferred "tv rating".
     *
     * @return int
     */
    static function getTvRatingSettings(): int
    {
        if (auth()->check()) {
            return auth()->user()->tv_rating;
        }

        return 4;
    }
}
