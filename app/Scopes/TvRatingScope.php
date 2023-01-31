<?php

namespace App\Scopes;

use App\Models\TvRating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TvRatingScope implements Scope
{
    /**
     * All extensions to be added to the builder.
     *
     * @var string[]
     */
    protected array $extensions = ['WithTvRatings', 'WithoutTvRatings'];

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check()) {
            $preferredTvRating = settings('tv_rating');
            $tvRating = TvRating::firstWhere('weight', $preferredTvRating);

            // If Tv Rating exists, so it's not -1
            if (!empty($tvRating)) {
                $builder->where($model->getQualifiedTvRatingColumn(), '<=', $tvRating->id);
            }
        } else {
            // User not signed in so default to user friendly.
            $builder->where($model->getQualifiedTvRatingColumn(), '<=', 4);
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param Builder $builder
     * @return void
     */
    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Get the "tv rating" column for the builder.
     *
     * @param Builder $builder
     * @return string
     */
    protected function getTvRatingColumn(Builder $builder): string
    {
        if (count($builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedTvRatingColumn();
        }

        return $builder->getModel()->getTvRatingColumn();
    }

    /**
     * Add the with-tv-ratings extension to the builder.
     *
     * @param Builder $builder
     * @return void
     */
    protected function addWithTvRatings(Builder $builder): void
    {
        $builder->macro('withTvRatings', function (Builder $builder, $withTvRatings = true) {
            if (!$withTvRatings) {
                return $builder->withoutTvRatings();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-tv-ratings extension to the builder.
     *
     * @param Builder $builder
     * @return void
     */
    protected function addWithoutTvRatings(Builder $builder): void
    {
        $builder->macro('withoutTvRatings', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
