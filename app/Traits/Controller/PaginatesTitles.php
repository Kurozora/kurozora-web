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
        return $query
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->paginate(25);
    }
}
