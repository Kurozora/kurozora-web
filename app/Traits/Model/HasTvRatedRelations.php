<?php

namespace App\Traits\Model;

use App\Scopes\TvRatingScope;

trait HasTvRatedRelations
{
    /**
     * Returns the relation with the TV-rating filter bypassed when the parent is viewable to the user.
     *
     * @template T
     *
     * @param T $relation
     *
     * @return T
     */
    public function viewableViaParent($relation)
    {
        return $relation->when(
            auth()->guest() || ((int) ($this->tv_rating_id ?? 0) > request()->tvRating()),
            function($query) {
                $query->withoutGlobalScope(TvRatingScope::class);
            }
        );
    }
}
