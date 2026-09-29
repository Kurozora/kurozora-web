<?php

namespace App\Http\Controllers\Web;

use App\Enums\ScheduleKind;
use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Song;
use App\Models\Studio;
use App\View\Components\AnimeSeasonsSection;
use App\View\Components\CastSection;
use App\View\Components\Chart\Section as ChartSection;
use App\View\Components\MoreByStudioSection;
use App\View\Components\RelationsSection;
use App\View\Components\Sections\Schedule as ScheduleSection;
use App\View\Components\Song\MediaSection as SongMediaSection;
use App\View\Components\SongsSection;
use App\View\Components\StaffSection;
use App\View\Components\StudiosSection;
use Carbon\Carbon;
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
            'more-by-studio' => new MoreByStudioSection(UserLibraryKind::Anime, $anime, $this->studio($request)),
            'related-anime' => new RelationsSection(UserLibraryKind::Anime, UserLibraryKind::Anime, $anime),
            'related-manga' => new RelationsSection(UserLibraryKind::Anime, UserLibraryKind::Manga, $anime),
            'related-games' => new RelationsSection(UserLibraryKind::Anime, UserLibraryKind::Game, $anime),
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
            'more-by-studio' => new MoreByStudioSection(UserLibraryKind::Manga, $manga, $this->studio($request)),
            'related-manga' => new RelationsSection(UserLibraryKind::Manga, UserLibraryKind::Manga, $manga),
            'related-anime' => new RelationsSection(UserLibraryKind::Manga, UserLibraryKind::Anime, $manga),
            'related-games' => new RelationsSection(UserLibraryKind::Manga, UserLibraryKind::Game, $manga),
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
            'more-by-studio' => new MoreByStudioSection(UserLibraryKind::Game, $game, $this->studio($request)),
            'related-games' => new RelationsSection(UserLibraryKind::Game, UserLibraryKind::Game, $game),
            'related-anime' => new RelationsSection(UserLibraryKind::Game, UserLibraryKind::Anime, $game),
            'related-manga' => new RelationsSection(UserLibraryKind::Game, UserLibraryKind::Manga, $game),
        });
    }

    /**
     * Render a section of a song page.
     *
     * @param Song   $song
     * @param string $section
     *
     * @return Response
     */
    public function song(Song $song, string $section): Response
    {
        return $this->render(match ($section) {
            'anime' => new SongMediaSection($song, Anime::class),
            'games' => new SongMediaSection($song, Game::class),
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
     * The studio named in the request.
     *
     * @param Request $request
     *
     * @return Studio
     */
    protected function studio(Request $request): Studio
    {
        return Studio::findOrFail($request->integer('studio'));
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
