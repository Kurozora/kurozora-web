<?php

namespace App\Http\Controllers\Web;

use App\Enums\EpisodeFillerKind;
use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;

class SeasonController extends Controller
{
    /**
     * Show an anime's seasons.
     *
     * @param Anime $anime
     *
     * @return Application|Factory|View
     */
    public function index(Anime $anime): Application|Factory|View
    {
        $anime->load(['media', 'translation']);

        $seasons = $anime->seasons()
            ->withoutGlobalScopes()
            ->with([
                'media',
                'translation',
            ])
            ->withCount([
                'episodes' => function ($query) {
                    $query->withoutGlobalScopes();
                },
            ])
            ->withAvg([
                'episodesMediaStats as rating_average' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->where('rating_average', '!=', 0);
                },
            ], 'rating_average')
            ->paginate(25);

        $seasons->each(function (Season $season) use ($anime) {
            $season->setRelation('anime', $anime);
        });

        return view('season.details', [
            'anime' => $anime,
            'seasons' => $seasons,
        ]);
    }

    /**
     * Show a season's episodes.
     *
     * @param GetSearchIndexRequest $request
     * @param Season                $season
     *
     * @return Application|Factory|View
     */
    public function episodes(GetSearchIndexRequest $request, Season $season): Application|Factory|View
    {
        ModelViewed::dispatch($season, $request->ip());

        $season->loadMissing([
            'anime' => function ($query) {
                $query->withoutGlobalScopes()
                    ->with(['media', 'translation']);
            },
            'media',
            'translation',
        ]);
        $anime = $season->anime;

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: Episode::webSearchFilters(),
            orders: Episode::webSearchOrders(),
            searchTypes: [
                'all' => __('All'),
                EpisodeFillerKind::AnimeCanon => __('Anime Canon'),
                EpisodeFillerKind::MangaCanon => __('Manga Canon'),
                EpisodeFillerKind::MixedCanonFiller => __('Mixed Canon/Filler'),
                EpisodeFillerKind::Filler => __('Filler'),
            ],
        );

        $episodes = (new SearchIndex(Episode::class, $criteria))
            ->hydrate(fn (Builder $query) => $this->hydrateEpisodes($query))
            ->letter('title', 'translations')
            ->type('filler_kind')
            ->index(fn (Builder $query) => $query->where('season_id', '=', $season->id))
            ->where('season_id', $season->id)
            ->paginate()
            ->withQueryString();

        return view('season.episodes', [
            'anime' => $anime,
            'season' => $season,
            'criteria' => $criteria,
            'episodes' => $episodes,
            'canUpdateEpisodes' => $anime->tvdb_id != null && $anime->season_count == 1,
        ]);
    }

    /**
     * Send the visitor to a random episode of a season.
     *
     * @param Season $season
     *
     * @return RedirectResponse
     */
    public function randomEpisode(Season $season): RedirectResponse
    {
        $episode = Episode::where('season_id', '=', $season->id)
            ->withoutGlobalScopes()
            ->inRandomOrder()
            ->first();

        if ($episode === null) {
            return back();
        }

        return to_route('episodes.details', $episode);
    }

    /**
     * Eager load the relations the lockups of the episodes of a query need.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    protected function hydrateEpisodes(Builder $query): Builder
    {
        return $query->withoutGlobalScopes()
            ->with([
                'anime' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->with(['media', 'translation']);
                },
                'media',
                'season' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->with(['translation']);
                },
                'translation',
            ])
            ->when(auth()->user(), function ($query, $user) {
                return $query->withExists([
                    'userWatchedEpisodes as isWatched' => function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->completed();
                    },
                ]);
            });
    }
}
