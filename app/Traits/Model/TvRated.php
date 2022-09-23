<?php

namespace App\Traits\Model;

use App\Scopes\TvRatingScope;
use Illuminate\Database\Query\Builder;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|Builder withoutTvRatings()
 */
trait TvRated
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootTvRated(): void
    {
        static::addGlobalScope(new TvRatingScope);
    }

    /**
     * Get the name of the "tv rating" column.
     *
     * @return string
     */
    public function getTvRatingColumn(): string
    {
        return defined(static::class.'::TV_RATING_ID') ? static::TV_RATING_ID : 'tv_rating_id';
    }

    /**
     * Get the fully qualified "tv rating" column.
     *
     * @return string
     */
    public function getQualifiedTvRatingColumn(): string
    {
        return $this->qualifyColumn($this->getTvRatingColumn());
    }
}
