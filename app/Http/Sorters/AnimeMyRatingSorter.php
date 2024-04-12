<?php

namespace App\Http\Sorters;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
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

        // Join the user ratings table
        $builder->leftJoin(MediaRating::TABLE_NAME, MediaRating::TABLE_NAME . '.model_id', '=', $morphTable . '.id')
            ->where(MediaRating::TABLE_NAME . '.model_type', $morphClass)
            ->where(MediaRating::TABLE_NAME . '.user_id', '=', auth()->user()->id);

        // Order by the user rating
        if ($direction == 'worst') {
            $builder->orderBy(MediaRating::TABLE_NAME . '.rating');
        } else {
            $builder->orderBy(MediaRating::TABLE_NAME . '.rating', 'desc');
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
