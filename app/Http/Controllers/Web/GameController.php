<?php

namespace App\Http\Controllers\Web;

use App\Enums\MediaCollection;
use App\Enums\UserLibraryStatus;
use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\UserLibrary;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    /**
     * Show a game's page.
     *
     * @param Request $request
     * @param Game    $game
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Game $game): Application|Factory|View
    {
        ModelViewed::dispatch($game, $request->ip());

        $user = $request->user();

        $game->loadMissing([
            'audioLanguages',
            'genres',
            'interfaceLanguages',
            'media',
            'mediaStat',
            'mediaLanguages.language',
            'subtitleLanguages',
            'mediaType',
            'themes',
            'translation',
            'status',
            'tvRating',
            'countryOfOrigin',
            'studios' => function (BelongsToMany $query) {
                $query->withoutGlobalScopes()
                    ->orderByRaw('CASE WHEN is_studio = true THEN 0 ELSE 1 END')
                    ->limit(1);
            },
        ])
            ->loadCount(['supportedLanguages as supported_languages_count' => fn (Builder $query) => $query->select(DB::raw('count(distinct languages.id)'))])
            ->when($user, function ($query, $user) use ($game) {
                return $game->loadMissing(['mediaRatings' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }])
                    ->loadExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
//                        'reminderers as isReminded' => fn ($query) => $query->where('user_id', '=', $user->id),
                    ]);
            });

        if ($user !== null) {
            $game->setRelation('library', UserLibrary::where([
                ['trackable_type', '=', $game->getMorphClass()],
                ['trackable_id', '=', $game->id],
                ['user_id', '=', $user->id],
            ])->get());
        } else {
            $game->setRelation('library', collect());
            $game->setRelation('mediaRatings', collect());
        }

        return view('game.details', [
            'game' => $game,
            'studio' => $game->studios->first(),
            'userRating' => $game->mediaRatings,
            'isTracking' => $game->library->isNotEmpty(),
            'isFavorited' => (bool) $game->isFavorited,
//            'isReminded' => (bool) $game->isReminded,
            'addToLibraryStatus' => $user !== null && $game->library->isEmpty()
                ? UserLibraryStatus::fromSlug($request->string('add_to_library')->toString())
                : null,
            'reviewBoxID' => str()->random(20),
            'bannerUrl' => $game->getFirstMediaFullUrl(MediaCollection::Banner())
                ?? $game->getFirstMediaFullUrl(MediaCollection::Poster())
                ?? asset('images/static/placeholders/game_banner.webp'),
            'metaDescription' => $this->metaDescription($game),
            'schema' => $game->toSchemaOrg(),
        ]);
    }

    /**
     * The meta description of a game's page.
     *
     * @param Game $game
     *
     * @return string
     */
    protected function metaDescription(Game $game): string
    {
        $facts = [];

        if ($year = $game->published_at?->year) {
            $facts[] = $year;
        }

        if ($game->mediaStat?->rating_average > 0) {
            $facts[] = __('Rated :x/5', ['x' => number_format($game->mediaStat->rating_average, 1)]);
        }

        $summary = array_filter([implode(' · ', $facts), $game->synopsis]);

        return implode(' — ', $summary) ?: __('A community for anime fans with an extensive library of anime, manga, music, games, movies, specials, OVA, and ONA. Only on :x, the largest, free online anime, manga, game & music database in the world. Track, share and discover anime with friends.', ['x' => config('app.name')]);
    }
}
