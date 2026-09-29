<?php

namespace App\Http\Controllers\Web;

use App\Enums\MediaCollection;
use App\Enums\UserLibraryStatus;
use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\UserLibrary;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimeController extends Controller
{
    /**
     * Show an anime's page.
     *
     * @param Request $request
     * @param Anime   $anime
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Anime $anime): Application|Factory|View
    {
        ModelViewed::dispatch($anime, $request->ip());

        $user = $request->user();

        $anime->loadMissing([
            'audioLanguages',
            'genres',
            'media',
            'mediaStat',
            'mediaType',
            'mediaLanguages.language',
            'subtitleLanguages',
            'themes',
            'translation',
            'status',
            'tvRating',
            'countryOfOrigin',
            'latestAiredEpisode',
            'nextEpisode.translation',
            'studios' => function (BelongsToMany $query) {
                $query->withoutGlobalScopes()
                    ->orderByRaw('CASE WHEN is_studio = true THEN 0 ELSE 1 END')
                    ->limit(1);
            },
        ])
            ->loadCount(['supportedLanguages as supported_languages_count' => fn (Builder $query) => $query->select(DB::raw('count(distinct languages.id)'))])
            ->when($user, function ($query, $user) use ($anime) {
                return $anime->loadMissing(['mediaRatings' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }])
                    ->loadExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                        'reminderers as isReminded' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                    ]);
            });

        if ($user !== null) {
            $anime->setRelation('library', UserLibrary::where([
                ['trackable_type', '=', $anime->getMorphClass()],
                ['trackable_id', '=', $anime->id],
                ['user_id', '=', $user->id],
            ])->get());
        } else {
            $anime->setRelation('library', collect());
            $anime->setRelation('mediaRatings', collect());
        }

        $hasUpcomingBroadcast = $anime->status_id !== 4 && !empty($anime->broadcast_date);
        $metaDescription = $this->metaDescription($anime, $hasUpcomingBroadcast);

        return view('anime.details', [
            'anime' => $anime,
            'studio' => $anime->studios->first(),
            'userRating' => $anime->mediaRatings,
            'isTracking' => $anime->library->isNotEmpty(),
            'isFavorited' => (bool) $anime->isFavorited,
            'isReminded' => (bool) $anime->isReminded,
            'addToLibraryStatus' => $user !== null && $anime->library->isEmpty()
                ? UserLibraryStatus::fromSlug($request->string('add_to_library')->toString())
                : null,
            'reviewBoxID' => str()->random(20),
            'bannerUrl' => $anime->getFirstMediaFullUrl(MediaCollection::Banner())
                ?? $anime->getFirstMediaFullUrl(MediaCollection::Poster())
                ?? asset('images/static/placeholders/anime_banner.webp'),
            'hasUpcomingBroadcast' => $hasUpcomingBroadcast,
            'pageTitle' => $hasUpcomingBroadcast
                ? __(':x — Next Episode, Cast & Reviews', ['x' => $anime->title])
                : __(':x — Episodes, Cast & Reviews', ['x' => $anime->title]),
            'metaDescription' => $metaDescription,
            'socialDescription' => $hasUpcomingBroadcast
                ? $metaDescription
                : $anime->synopsis ?? __('A community for anime fans with an extensive library of anime, manga, music, games, movies, specials, OVA, and ONA. Only on :x, the largest, free online anime, manga, game & music database in the world. Track, share and discover anime with friends.', ['x' => config('app.name')]),
            'schema' => $anime->toSchemaOrg(),
        ]);
    }

    /**
     * The meta description of an anime's page.
     *
     * @param Anime $anime
     * @param bool  $hasUpcomingBroadcast
     *
     * @return string
     */
    protected function metaDescription(Anime $anime, bool $hasUpcomingBroadcast): string
    {
        if ($hasUpcomingBroadcast) {
            return __('Find the next :x episode release date, count down to air time, and track your progress. Plus, read the synopsis, check the cast, and browse reviews.', ['x' => $anime->title]);
        }

        $facts = [];

        if ($anime->episode_count > 0) {
            $facts[] = trans_choice('{1} :x episode|[2,*] :x episodes', $anime->episode_count, ['x' => $anime->episode_count]);
        }

        if ($year = $anime->started_at?->year) {
            $facts[] = $year;
        }

        if ($anime->mediaStat?->rating_average > 0) {
            $facts[] = __('Rated :x/5', ['x' => number_format($anime->mediaStat->rating_average, 1)]);
        }

        $summary = array_filter([implode(' · ', $facts), $anime->synopsis]);

        return implode(' — ', $summary) ?: __('A community for anime fans with an extensive library of anime, manga, music, games, movies, specials, OVA, and ONA. Only on :x, the largest, free online anime, manga, game & music database in the world. Track, share and discover anime with friends.', ['x' => config('app.name')]);
    }
}
