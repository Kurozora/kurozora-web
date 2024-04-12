<?php

namespace App\Http\Sorters;

use App\Models\UserLibrary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use kiritokatklian\SortRequest\Support\Foundation\Contracts\Sorter;

class AnimeAgeSorter extends Sorter
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
        // Order by when the user added the anime to their library
        if ($direction == 'newest') {
            $builder->orderBy(UserLibrary::TABLE_NAME . '.created_at', 'desc');
        } else {
            $builder->orderBy(UserLibrary::TABLE_NAME . '.created_at');
        }

        return $builder;
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
