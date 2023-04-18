<?php

namespace App\Http\Sorters;

use App\Models\Anime;
use App\Models\MediaStat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use kiritokatklian\SortRequest\Support\Foundation\Contracts\Sorter;

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
    public function apply(Request $request, Builder $builder, string $direction): Builder
    {
        // Join the media stats table
        $builder->join(MediaStat::TABLE_NAME, MediaStat::TABLE_NAME . '.model_id', '=', Anime::TABLE_NAME . '.id')
            ->where(MediaStat::TABLE_NAME . '.model_type', Anime::class);

        // Order by the rating average
        if ($direction == 'worst') {
            $builder->orderBy(MediaStat::TABLE_NAME . '.rating_average');
        } else {
            $builder->orderBy(MediaStat::TABLE_NAME . '.rating_average', 'desc');
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
