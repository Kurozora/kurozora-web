<?php

namespace App\Http\Sorters;

use App\Models\Anime;
use App\Models\AnimeTranslation;
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
        // Join the anime translation table
        $builder->join(AnimeTranslation::TABLE_NAME, AnimeTranslation::TABLE_NAME . '.anime_id', '=', Anime::TABLE_NAME . '.id')
            ->select(Anime::TABLE_NAME . '.*');

        // Order by the user rating
        if ($direction == 'asc') {
            $builder->orderBy(AnimeTranslation::TABLE_NAME . '.title');
        } else {
            $builder->orderBy(AnimeTranslation::TABLE_NAME . '.title', 'desc');
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
        return ['asc', 'desc'];
    }
}
