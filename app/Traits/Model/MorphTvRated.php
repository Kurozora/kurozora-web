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
    use TvRated;

    /**
     * Boot the soft deleting trait for a model.
     *
     * @return void
     */
    public static function bootTvRated(): void
    {
        static::addGlobalScope(new MorphTvRatingScope);
    }

    /**
     * Get the fully qualified "tv rating" column.
     *
     * @return string
     */
    public function getQualifiedTvRatingColumn(): string
    {
        return $this->getTvRatingColumn();
    }
}
