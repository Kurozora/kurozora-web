<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends KModel
{
    // Table name
    const TABLE_NAME = 'genres';
    protected $table = self::TABLE_NAME;

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tv_rating', function (Builder $builder) {
            if (Auth::check()) {
                $preferredTvRating = settings('tv_rating');
                $tvRating = TvRating::firstWhere('weight', $preferredTvRating);

                if (!empty($tvRating)) {
                    $builder->where('tv_rating_id', '<=', $tvRating->id);
                }
            } else {
                $builder->where('tv_rating_id', '<=', 4);
            }
        });
    }

    /**
     * Returns the Anime with the genre
     *
     * @return BelongsToMany
     */
    function animes(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, MediaGenre::TABLE_NAME, 'genre_id', 'media_id')
            ->where('type', 'anime')
            ->withTimestamps();
    }

    /**
     * The genre's TV rating.
     *
     * @return BelongsTo
     */
    public function tv_rating(): BelongsTo
    {
        return $this->belongsTo(TvRating::class);
    }
}
