<?php

namespace App\Http\Sorters;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
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
        // Get morph class
        $morphClass = match ((int) ($request->get('library') ?? UserLibraryKind::Anime)) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };
        $morphTable = match ((int) ($request->get('library') ?? UserLibraryKind::Anime)) {
            UserLibraryKind::Manga => Manga::TABLE_NAME,
            UserLibraryKind::Game => Game::TABLE_NAME,
            default => Anime::TABLE_NAME,
        };

        // Join the media stats table
        $builder->join(MediaStat::TABLE_NAME, MediaStat::TABLE_NAME . '.model_id', '=', $morphTable . '.id')
            ->where(MediaStat::TABLE_NAME . '.model_type', $morphClass);

        // Order by the rating average
        if ($direction == 'worst') {
            $builder->orderBy(MediaStat::TABLE_NAME . '.rating_average');
        } else {
            $builder->orderBy(MediaStat::TABLE_NAME . '.rating_average', 'desc');
        }

        return $builder->select($morphTable . '.*');
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
