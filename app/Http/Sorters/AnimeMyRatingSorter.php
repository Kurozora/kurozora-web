<?php

namespace App\Http\Sorters;

use App\Models\Anime;
use App\Models\MediaRating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use kiritokatklian\SortRequest\Support\Foundation\Contracts\Sorter;

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
    public function apply(Request $request, Builder $builder, string $direction): Builder
    {
        // Join the user ratings table
        $builder->leftJoin(MediaRating::TABLE_NAME, MediaRating::TABLE_NAME . '.model_id', '=', Anime::TABLE_NAME . '.id')
        ->where(MediaRating::TABLE_NAME . '.model_type', Anime::class);

        // Order by the user rating
        if ($direction == 'worst') {
            $builder->orderBy(MediaRating::TABLE_NAME . '.rating');
        } else {
            $builder->orderBy(MediaRating::TABLE_NAME . '.rating', 'desc');
        }

        return $builder->select(Anime::TABLE_NAME . '.*');
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
