<?php

namespace App\Http\Controllers\Web;

use App\Enums\ScheduleKind;
use App\Enums\SeasonOfYear;
use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\FeedMessage;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaType;
use App\Models\Person;
use App\Models\Platform;
use App\Models\Song;
use App\Models\Studio;
use App\Models\User;
use App\View\Components\AnimeSeasonsSection;
use App\View\Components\Browse\SeasonsSection;
use App\View\Components\CastSection;
use App\View\Components\Character\MediaSection as CharacterMediaSection;
use App\View\Components\Chart\Section as ChartSection;
use App\View\Components\Episode\PastEpisodesSection;
use App\View\Components\Episode\UpNextEpisodes;
use App\View\Components\Feed\MessageList as FeedMessageList;
use App\View\Components\MoreByStudioSection;
use App\View\Components\Person\MediaSection as PersonMediaSection;
use App\View\Components\Platform\MediaSection as PlatformMediaSection;
use App\View\Components\RelationsSection;
use App\View\Components\Sections\Reviews as ReviewsSection;
use App\View\Components\Sections\Schedule as ScheduleSection;
use App\View\Components\Song\MediaSection as SongMediaSection;
use App\View\Components\SongsSection;
use App\View\Components\StaffSection;
use App\View\Components\Studio\MediaSection as StudioMediaSection;
use App\View\Components\StudiosSection;
use App\View\Components\User\BannerImage;
use App\View\Components\User\FavoritesSection;
use App\View\Components\User\FeedMessagesSection;
use App\View\Components\User\LibrarySection;
use App\View\Components\User\ProfileImage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

class SectionController extends Controller
{
    /**
     * Render a section of an anime page.
     *
     * @param Request $request
     * @param Anime   $anime
     * @param string  $section
     *
     * @return Response
     */
    public function anime(Request $request, Anime $anime, string $section): Response
    {
        return $this->render(match ($section) {
            'seasons' => new AnimeSeasonsSection($anime),
            'cast' => new CastSection(UserLibraryKind::Anime, $anime),
            'staff' => new StaffSection(UserLibraryKind::Anime, $anime),
            'songs' => new SongsSection(UserLibraryKind::Anime, $anime),
            'studios' => new StudiosSection(UserLibraryKind::Anime, $anime),
            'more-by-studio' => new MoreByStudioSection(UserLibraryKind::Anime, $anime, $this->requestedStudio($request)),
            'related-anime' => new RelationsSection(UserLibraryKind::Anime, UserLibraryKind::Anime, $anime),
            'related-manga' => new RelationsSection(UserLibraryKind::Anime, UserLibraryKind::Manga, $anime),
            'related-games' => new RelationsSection(UserLibraryKind::Anime, UserLibraryKind::Game, $anime),
            'reviews' => new ReviewsSection($anime, $this->requestedReviewBox($request)),
        });
    }

    /**
     * Render a section of a manga page.
     *
     * @param Request $request
     * @param Manga   $manga
     * @param string  $section
     *
     * @return Response
     */
    public function manga(Request $request, Manga $manga, string $section): Response
    {
        return $this->render(match ($section) {
            'cast' => new CastSection(UserLibraryKind::Manga, $manga),
            'staff' => new StaffSection(UserLibraryKind::Manga, $manga),
            'studios' => new StudiosSection(UserLibraryKind::Manga, $manga),
            'more-by-studio' => new MoreByStudioSection(UserLibraryKind::Manga, $manga, $this->requestedStudio($request)),
            'related-manga' => new RelationsSection(UserLibraryKind::Manga, UserLibraryKind::Manga, $manga),
            'related-anime' => new RelationsSection(UserLibraryKind::Manga, UserLibraryKind::Anime, $manga),
            'related-games' => new RelationsSection(UserLibraryKind::Manga, UserLibraryKind::Game, $manga),
            'reviews' => new ReviewsSection($manga, $this->requestedReviewBox($request)),
        });
    }

    /**
     * Render a section of a game page.
     *
     * @param Request $request
     * @param Game    $game
     * @param string  $section
     *
     * @return Response
     */
    public function game(Request $request, Game $game, string $section): Response
    {
        return $this->render(match ($section) {
            'cast' => new CastSection(UserLibraryKind::Game, $game),
            'staff' => new StaffSection(UserLibraryKind::Game, $game),
            'songs' => new SongsSection(UserLibraryKind::Game, $game),
            'studios' => new StudiosSection(UserLibraryKind::Game, $game),
            'more-by-studio' => new MoreByStudioSection(UserLibraryKind::Game, $game, $this->requestedStudio($request)),
            'related-games' => new RelationsSection(UserLibraryKind::Game, UserLibraryKind::Game, $game),
            'related-anime' => new RelationsSection(UserLibraryKind::Game, UserLibraryKind::Anime, $game),
            'related-manga' => new RelationsSection(UserLibraryKind::Game, UserLibraryKind::Manga, $game),
            'reviews' => new ReviewsSection($game, $this->requestedReviewBox($request)),
        });
    }

    /**
     * Render a section of a character page.
     *
     * @param Request   $request
     * @param Character $character
     * @param string    $section
     *
     * @return Response
     */
    public function character(Request $request, Character $character, string $section): Response
    {
        return $this->render(match ($section) {
            'anime' => new CharacterMediaSection($character, Anime::class),
            'people' => new CharacterMediaSection($character, Person::class),
            'manga' => new CharacterMediaSection($character, Manga::class),
            'games' => new CharacterMediaSection($character, Game::class),
            'reviews' => new ReviewsSection($character, $this->requestedReviewBox($request)),
        });
    }

    /**
     * Render a section of a person page.
     *
     * @param Request $request
     * @param Person  $person
     * @param string  $section
     *
     * @return Response
     */
    public function person(Request $request, Person $person, string $section): Response
    {
        return $this->render(match ($section) {
            'anime' => new PersonMediaSection($person, Anime::class),
            'characters' => new PersonMediaSection($person, Character::class),
            'manga' => new PersonMediaSection($person, Manga::class),
            'games' => new PersonMediaSection($person, Game::class),
            'reviews' => new ReviewsSection($person, $this->requestedReviewBox($request)),
        });
    }

    /**
     * Render a section of a studio page.
     *
     * @param Request $request
     * @param Studio  $studio
     * @param string  $section
     *
     * @return Response
     */
    public function studio(Request $request, Studio $studio, string $section): Response
    {
        return $this->render(match ($section) {
            'anime' => new StudioMediaSection($studio, Anime::class),
            'manga' => new StudioMediaSection($studio, Manga::class),
            'games' => new StudioMediaSection($studio, Game::class),
            'reviews' => new ReviewsSection($studio, $this->requestedReviewBox($request)),
        });
    }

    /**
     * Render a section of a platform page.
     *
     * @param Platform $platform
     * @param string   $section
     *
     * @return Response
     */
    public function platform(Platform $platform, string $section): Response
    {
        return $this->render(match ($section) {
            'games' => new PlatformMediaSection($platform, Game::class),
        });
    }

    /**
     * Render a section of a profile page.
     *
     * @param User   $user
     * @param string $section
     *
     * @return Response
     */
    public function profile(User $user, string $section): Response
    {
        return $this->render(match ($section) {
            'banner-image' => new BannerImage($user, true),
            'profile-image' => new ProfileImage($user, true),
            'anime-library' => new LibrarySection($user, Anime::class),
            'manga-library' => new LibrarySection($user, Manga::class),
            'games-library' => new LibrarySection($user, Game::class),
            'anime-favorites' => new FavoritesSection($user, Anime::class),
            'manga-favorites' => new FavoritesSection($user, Manga::class),
            'games-favorites' => new FavoritesSection($user, Game::class),
            'feed-messages' => new FeedMessagesSection($user),
        });
    }

    /**
     * Render a section of the up-next page.
     *
     * @param string $section
     *
     * @return Response
     */
    public function upNext(string $section): Response
    {
        return $this->render(match ($section) {
            'episodes' => new UpNextEpisodes,
            'past-episodes' => new PastEpisodesSection,
        });
    }

    /**
     * Render a page of feed messages, or count the messages newer than the given id.
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function feed(Request $request): JsonResponse|Response
    {
        $after = $request->integer('after') ?: null;

        if ($after !== null && $request->wantsJson()) {
            return response()->json([
                'count' => FeedMessage::where('is_reply', '=', false)
                    ->where('id', '>', $after)
                    ->count(),
            ]);
        }

        return $this->render(new FeedMessageList(
            cursor: $request->integer('cursor') ?: null,
            after: $after,
            fragment: true
        ));
    }

    /**
     * Render a section of a song page.
     *
     * @param Request $request
     * @param Song    $song
     * @param string  $section
     *
     * @return Response
     */
    public function song(Request $request, Song $song, string $section): Response
    {
        return $this->render(match ($section) {
            'anime' => new SongMediaSection($song, Anime::class),
            'games' => new SongMediaSection($song, Game::class),
            'reviews' => new ReviewsSection($song, $this->requestedReviewBox($request)),
        });
    }

    /**
     * Render a section of an episode page.
     *
     * @param Request $request
     * @param Episode $episode
     * @param string  $section
     *
     * @return Response
     */
    public function episode(Request $request, Episode $episode, string $section): Response
    {
        return $this->render(match ($section) {
            'reviews' => new ReviewsSection($episode, $this->requestedReviewBox($request)),
        });
    }

    /**
     * Render a day of the schedule page.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function schedule(Request $request): Response
    {
        $type = ScheduleKind::modelClass(strtolower($request->string('type', ScheduleKind::Anime()->key)->toString()));
        $date = Carbon::createFromFormat('Y-m-d', $request->string('date', today()->toDateString())->toString()) ?? now();

        return $this->render(new ScheduleSection($type, $date));
    }

    /**
     * Render a chart of the charts page.
     *
     * @param string $chart
     *
     * @return Response
     */
    public function chart(string $chart): Response
    {
        return $this->render(new ChartSection($chart));
    }

    /**
     * Render a media type's section of a season browse page.
     *
     * @param Request $request
     * @param string  $year
     * @param string  $season
     * @param int     $kind
     *
     * @return Response
     */
    public function browseSeason(Request $request, string $year, string $season, int $kind): Response
    {
        $modelClass = match ($kind) {
            UserLibraryKind::Anime => Anime::class,
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
        };

        return $this->render(new SeasonsSection(
            $modelClass,
            MediaType::findOrFail($request->integer('mediaType')),
            SeasonOfYear::fromKey(str($season)->ucfirst())->value,
            (int) $year,
        ));
    }

    /**
     * The studio named in the request.
     *
     * @param Request $request
     *
     * @return Studio
     */
    protected function requestedStudio(Request $request): Studio
    {
        return Studio::findOrFail($request->integer('studio'));
    }

    /**
     * The id of the review box named in the request.
     *
     * @param Request $request
     *
     * @return string|null
     */
    protected function requestedReviewBox(Request $request): ?string
    {
        $reviewBox = $request->string('reviewBox')->value();

        return $reviewBox === '' ? null : $reviewBox;
    }

    /**
     * Render the component as a page fragment.
     *
     * @param Component $component
     *
     * @return Response
     */
    protected function render(Component $component): Response
    {
        return response($component->shouldRender() ? Blade::renderComponent($component) : '');
    }
}
