<?php

namespace App\Http\Sorters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use musa11971\SortRequest\Support\Foundation\Contracts\Sorter;

class AnimeTitleSorter extends Sorter
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
        // Order by the user rating
        return $builder->orderBy('original_title', $direction);
    }

    /**
     * Returns the directions that can be sorted on.
     *
     * @return array
     */
    public function getDirections(): array
    {
        return ['asc', 'desc'];
    }
}
