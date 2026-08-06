<?php

namespace App\Http\Sorters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use kiritokatklian\SortRequest\Support\Foundation\Contracts\Sorter;

class UserDateSorter extends Sorter
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
        $orderDirection = $direction == 'newest' ? 'desc' : 'asc';
        return $builder->orderBy('created_at', $orderDirection);
    }

    /**
     * Returns the directions that can be sorted on.
     *
     * @return array
     */
    public function getDirections(): array
    {
        return ['newest', 'oldest'];
    }
}

