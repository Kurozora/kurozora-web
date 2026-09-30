<?php

namespace App\Http\Controllers\Web;

use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Show the reviews of an anime.
     *
     * @param Request $request
     * @param Anime   $anime
     *
     * @return Application|Factory|View
     */
    public function anime(Request $request, Anime $anime): Application|Factory|View
    {
        $anime->loadMissing(['media', 'translation']);

        return $this->page($request, $anime, [
            'kind' => 'anime',
            'name' => $anime->title,
            'backUrl' => $anime->schemaUrl(),
            'canonicalUrl' => route('anime.reviews', $anime),
            'appArgument' => 'anime/' . $anime->id . '/reviews',
            'ogType' => 'video.tv_show',
            'ogImage' => $anime->getFirstMediaFullUrl(MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp'),
        ]);
    }

    /**
     * Show the reviews of a manga.
     *
     * @param Request $request
     * @param Manga   $manga
     *
     * @return Application|Factory|View
     */
    public function manga(Request $request, Manga $manga): Application|Factory|View
    {
        $manga->loadMissing(['media', 'translation']);

        return $this->page($request, $manga, [
            'kind' => 'manga',
            'name' => $manga->title,
            'backUrl' => $manga->schemaUrl(),
            'canonicalUrl' => route('manga.reviews', $manga),
            'appArgument' => 'manga/' . $manga->id . '/reviews',
            'ogType' => 'book',
            'ogImage' => $manga->getFirstMediaFullUrl(MediaCollection::Poster()) ?? asset('images/static/placeholders/manga_poster.webp'),
        ]);
    }

    /**
     * Show the reviews of a game.
     *
     * @param Request $request
     * @param Game    $game
     *
     * @return Application|Factory|View
     */
    public function game(Request $request, Game $game): Application|Factory|View
    {
        $game->loadMissing(['media', 'translation']);

        return $this->page($request, $game, [
            'kind' => 'game',
            'name' => $game->title,
            'backUrl' => $game->schemaUrl(),
            'canonicalUrl' => route('games.reviews', $game),
            'appArgument' => 'games/' . $game->id . '/reviews',
            'ogType' => 'video.tv_show',
            'ogImage' => $game->getFirstMediaFullUrl(MediaCollection::Poster()) ?? asset('images/static/placeholders/game_poster.webp'),
        ]);
    }

    /**
     * Show the reviews of an episode.
     *
     * @param Request $request
     * @param Episode $episode
     *
     * @return Application|Factory|View
     */
    public function episode(Request $request, Episode $episode): Application|Factory|View
    {
        $episode->loadMissing(['media', 'translation']);

        return $this->page($request, $episode, [
            'kind' => 'episode',
            'name' => $episode->title,
            'backUrl' => $episode->schemaUrl(),
            'canonicalUrl' => route('episodes.reviews', $episode),
            'appArgument' => 'episodes/' . $episode->id . '/reviews',
            'ogType' => 'video.episode',
            'ogImage' => $episode->getFirstMediaFullUrl(MediaCollection::Banner()) ?? asset('images/static/placeholders/episode_banner.webp'),
        ]);
    }

    /**
     * Show the reviews of a character.
     *
     * @param Request   $request
     * @param Character $character
     *
     * @return Application|Factory|View
     */
    public function character(Request $request, Character $character): Application|Factory|View
    {
        $character->loadMissing(['media', 'translation']);

        return $this->page($request, $character, [
            'kind' => 'character',
            'name' => $character->name,
            'backUrl' => route('characters.details', $character),
            'canonicalUrl' => route('characters.reviews', $character),
            'appArgument' => 'characters/' . $character->id . '/reviews',
            'ogType' => 'profile',
            'ogImage' => $character->getFirstMediaFullUrl(MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp'),
        ]);
    }

    /**
     * Show the reviews of a person.
     *
     * @param Request $request
     * @param Person  $person
     *
     * @return Application|Factory|View
     */
    public function person(Request $request, Person $person): Application|Factory|View
    {
        $person->loadMissing(['media']);

        return $this->page($request, $person, [
            'kind' => 'person',
            'name' => $person->full_name,
            'backUrl' => route('people.details', $person),
            'canonicalUrl' => route('people.reviews', $person),
            'appArgument' => 'people/' . $person->id . '/reviews',
            'ogType' => 'profile',
            'ogImage' => $person->getFirstMediaFullUrl(MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp'),
        ]);
    }

    /**
     * Show the reviews of a song.
     *
     * @param Request $request
     * @param Song    $song
     *
     * @return Application|Factory|View
     */
    public function song(Request $request, Song $song): Application|Factory|View
    {
        $song->loadMissing(['media']);

        return $this->page($request, $song, [
            'kind' => 'song',
            'name' => $song->original_title,
            'backUrl' => route('songs.details', $song),
            'canonicalUrl' => route('songs.reviews', $song),
            'appArgument' => 'songs/' . $song->id . '/reviews',
            'ogType' => 'music.song',
            'ogImage' => $song->getFirstMediaFullUrl(MediaCollection::Artwork()) ?? asset('images/static/placeholders/song_banner.webp'),
        ]);
    }

    /**
     * Show the reviews of a studio.
     *
     * @param Request $request
     * @param Studio  $studio
     *
     * @return Application|Factory|View
     */
    public function studio(Request $request, Studio $studio): Application|Factory|View
    {
        $studio->loadMissing(['media']);

        return $this->page($request, $studio, [
            'kind' => 'studio',
            'name' => $studio->name,
            'backUrl' => route('studios.details', $studio),
            'canonicalUrl' => route('studios.reviews', $studio),
            'appArgument' => 'studios/' . $studio->id . '/reviews',
            'ogType' => 'profile',
            'ogImage' => $studio->getFirstMediaFullUrl(MediaCollection::Profile()) ?? asset('images/static/placeholders/person_poster.webp'),
        ]);
    }

    /**
     * Render the reviews page of a reviewable model.
     *
     * @param Request $request
     * @param Model   $reviewable
     * @param array   $copy
     *
     * @return Application|Factory|View
     */
    protected function page(Request $request, Model $reviewable, array $copy): Application|Factory|View
    {
        $user = $request->user();

        $reviewable->loadMissing(['mediaStat']);

        return view('reviews.index', $copy + [
            'reviewable' => $reviewable,
            'mediaStat' => $reviewable->mediaStat,
            'userRating' => $user === null ? null : $reviewable->mediaRatings()->firstWhere('user_id', $user->id),
            'editorial' => $reviewable->editorial()
                ->published()
                ->first(),
            'reviews' => $reviewable->mediaRatings()
                ->with(array_merge(['user.media', 'revisions'], MediaRating::lockupEagerLoads($user)))
                ->where('description', '!=', null)
                ->forReading()
                ->cursorPaginate()
                ->withQueryString(),
            'reviewBoxID' => str()->random(20),
        ]);
    }
}
