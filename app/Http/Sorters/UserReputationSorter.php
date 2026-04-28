<?php

namespace App\Http\Sorters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use kiritokatklian\SortRequest\Support\Foundation\Contracts\Sorter;

class UserReputationSorter extends Sorter
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
        $orderDirection = $direction == 'most' ? 'desc' : 'asc';
        return $builder->orderBy('reputation_count', $orderDirection);
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

