<?php

namespace App\Http\Controllers\Web;

use App\Enums\MediaCollection;
use App\Enums\UserLibraryStatus;
use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Models\Manga;
use App\Models\UserLibrary;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MangaController extends Controller
{
    /**
     * Show a manga's page.
     *
     * @param Request $request
     * @param Manga   $manga
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Manga $manga): Application|Factory|View
    {
        ModelViewed::dispatch($manga, $request->ip());

        $user = $request->user();

        $manga->loadMissing([
            'genres',
            'media',
            'mediaStat',
            'mediaType',
            'mediaLanguages.language',
            'textLanguages',
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
            ->when($user, function ($query, $user) use ($manga) {
                return $manga->loadMissing(['mediaRatings' => function ($query) use ($user) {
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
            $manga->setRelation('library', UserLibrary::where([
                ['trackable_type', '=', $manga->getMorphClass()],
                ['trackable_id', '=', $manga->id],
                ['user_id', '=', $user->id],
            ])->get());
        } else {
            $manga->setRelation('library', collect());
            $manga->setRelation('mediaRatings', collect());
        }

        return view('manga.details', [
            'manga' => $manga,
            'studio' => $manga->studios->first(),
            'userRating' => $manga->mediaRatings,
            'isTracking' => $manga->library->isNotEmpty(),
            'isFavorited' => (bool) $manga->isFavorited,
//            'isReminded' => (bool) $manga->isReminded,
            'addToLibraryStatus' => $user !== null && $manga->library->isEmpty()
                ? UserLibraryStatus::fromSlug($request->string('add_to_library')->toString())
                : null,
            'reviewBoxID' => str()->random(20),
            'bannerUrl' => $manga->getFirstMediaFullUrl(MediaCollection::Banner())
                ?? $manga->getFirstMediaFullUrl(MediaCollection::Poster())
                ?? asset('images/static/placeholders/anime_banner.webp'),
            'metaDescription' => $this->metaDescription($manga),
            'schema' => $manga->toSchemaOrg(),
        ]);
    }

    /**
     * The meta description of a manga's page.
     *
     * @param Manga $manga
     *
     * @return string
     */
    protected function metaDescription(Manga $manga): string
    {
        $facts = [];

        if ($manga->chapter_count > 0) {
            $facts[] = trans_choice('{1} :x chapter|[2,*] :x chapters', $manga->chapter_count, ['x' => $manga->chapter_count]);
        }

        if ($manga->volume_count > 0) {
            $facts[] = trans_choice('{1} :x volume|[2,*] :x volumes', $manga->volume_count, ['x' => $manga->volume_count]);
        }

        if ($year = $manga->started_at?->year) {
            $facts[] = $year;
        }

        if ($manga->mediaStat?->rating_average > 0) {
            $facts[] = __('Rated :x/5', ['x' => number_format($manga->mediaStat->rating_average, 1)]);
        }

        $summary = array_filter([implode(' · ', $facts), $manga->synopsis]);

        return implode(' — ', $summary) ?: __('A community for anime fans with an extensive library of anime, manga, music, games, movies, specials, OVA, and ONA. Only on :x, the largest, free online anime, manga, game & music database in the world. Track, share and discover anime with friends.', ['x' => config('app.name')]);
    }
}
