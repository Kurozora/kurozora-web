<?php

namespace App\Traits\Controller;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;

trait PaginatesTitles
{
    /**
     * Paginate the titles of a query with the relations their lockups need.
     *
     * @param Builder|Relation $query
     *
     * @return LengthAwarePaginator
     */
    protected function paginateTitles(Builder|Relation $query): LengthAwarePaginator
    {
        return $this->hydrateTitles($query)
            ->paginate(25);
    }

    /**
     * Eager load the relations the lockups of the titles of a query need.
     *
     * @param Builder|Relation $query
     *
     * @return Builder|Relation
     */
    protected function hydrateTitles(Builder|Relation $query): Builder|Relation
    {
        return $query
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });
    }
}
