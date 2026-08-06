<?php

namespace App\Traits\Model;

use App\Scopes\MorphTvRatingScope;
use Illuminate\Database\Query\Builder;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|Builder withMorphTvRatings(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder|Builder withoutMorphTvRatings()
 */
trait MorphTvRated
{
    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootMorphTvRated(): void
    {
        static::addGlobalScope(new MorphTvRatingScope);
    }

    /**
     * Get the name of the "tv rating" column resolved against the morph target.
     *
     * @return string
     */
    public function getTvRatingColumn(): string
    {
        return defined(static::class . '::TV_RATING_ID') ? static::TV_RATING_ID : 'tv_rating_id';
    }

    /**
     * Get the unqualified "tv rating" column so the morph scope can apply it against the related table.
     *
     * @return string
     */
    public function getQualifiedTvRatingColumn(): string
    {
        return $this->getTvRatingColumn();
    }
}
