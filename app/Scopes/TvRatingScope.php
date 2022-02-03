<?php

namespace App\Scopes;

use App\Models\TvRating;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TvRatingScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $preferredTvRating = settings('tv_rating');
            $tvRating = TvRating::firstWhere('weight', $preferredTvRating);

            // If Tv Rating exists, so it's not -1
            if (!empty($tvRating)) {
                $builder->where('tv_rating_id', '<=', $tvRating->id);
            }
        } else {
            // User not signed in so default to user friendly.
            $builder->where('tv_rating_id', '<=', 4);
        }
    }
}
