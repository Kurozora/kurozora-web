<?php

namespace App\Http\Sorters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use kiritokatklian\SortRequest\Support\Foundation\Contracts\Sorter;

class LibraryPopularitySorter extends Sorter
{
    /**
     * Applies the sorter to the Eloquent builder.
     *
     * @param Request $request
     * @param Builder $builder
     * @param string $direction
     *
     * @return Builder
     */
    public function apply(Request $request, Builder $builder, string $direction): Builder
    {
        // Order by the total rank
        $orderDirection = $direction == 'most' ? 'asc' : 'desc';
        return $builder->orderBy('rank_total', $orderDirection);
    }

    /**
     * Returns the directions that can be sorted on.
     *
     * @return array
     */
    public function getDirections(): array
    {
        return ['most', 'least'];
    }
}
