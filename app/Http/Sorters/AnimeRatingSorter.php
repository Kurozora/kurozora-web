<?php

namespace App\Http\Sorters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use musa11971\SortRequest\Support\Foundation\Contracts\Sorter;

class AnimeRatingSorter extends Sorter
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
    public function apply(Request $request, Builder $builder, $direction): Builder
    {
        if($direction == 'worst')
            $builder->orderBy('average_rating', 'asc');
        else
            $builder->orderBy('average_rating', 'desc');

        return $builder;
    }

    /**
     * Returns the directions that can be sorted on.
     *
     * @return array
     */
    public function getDirections(): array
    {
        return ['worst', 'best'];
    }
}
