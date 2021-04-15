<?php

namespace App\Http\Sorters;

use App\Models\Anime;
use App\Models\AnimeRating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use musa11971\SortRequest\Support\Foundation\Contracts\Sorter;

class AnimeMyRatingSorter extends Sorter
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
        // Join the user ratings table
        $builder->join(AnimeRating::TABLE_NAME, AnimeRating::TABLE_NAME . '.anime_id', '=', Anime::TABLE_NAME . '.id')
            ->select(Anime::TABLE_NAME . '.*');

        // Order by the user rating
        if ($direction == 'worst')
            $builder->orderBy(AnimeRating::TABLE_NAME . '.rating', 'asc');
        else
            $builder->orderBy(AnimeRating::TABLE_NAME . '.rating', 'desc');

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
