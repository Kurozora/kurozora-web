<?php

namespace App\View\Components\Sections;

use App\Enums\ScheduleKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Schedule extends Component
{
    /**
     * The class of the scheduled models.
     *
     * @var string $type
     */
    public string $type;

    /**
     * The date of the schedule.
     *
     * @var Carbon $date
     */
    public Carbon $date;

    /**
     * The models scheduled on the date.
     *
     * @var Collection $models
     */
    public Collection $models;

    /**
     * The URL that renders the section again.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create the component.
     *
     * @param string $type
     * @param Carbon $date
     */
    public function __construct(string $type, Carbon $date)
    {
        $this->type = $type;
        $this->date = $date;
        $this->models = $this->loadModels();
        $this->refreshUrl = route('schedule.section', [
            'type' => ScheduleKind::typeFor($type),
            'date' => $date->toDateString(),
        ], false);
    }

    /**
     * Whether anything is scheduled on the date.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->models->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.sections.schedule');
    }

    /**
     * Loads the models scheduled on the date.
     *
     * @return Collection
     */
    protected function loadModels(): Collection
    {
        return match ($this->type) {
            Anime::class => $this->queryAnimeSchedule()
                ->get(),
            Manga::class => $this->queryMangaSchedule()
                ->get(),
            Game::class => $this->queryGameSchedule()
                ->get(),
            default => collect(),
        };
    }

    /**
     * The anime airing on the date.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function queryAnimeSchedule()
    {
        return Anime::withSchedule([
            [
                'start' => $this->date->startOfDay()->toDateTimeString(),
                'end' => $this->date->endOfDay()->toDateTimeString()
            ]
        ])
            ->select(Anime::TABLE_NAME . '.*')
            ->with(['genres', 'latestAiredEpisode', 'media', 'mediaStat', 'nextEpisode', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });
    }

    /**
     * The manga publishing on the date's weekday.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function queryMangaSchedule()
    {
        return Manga::withSchedule([$this->date->dayOfWeek])
            ->select(Manga::TABLE_NAME . '.*')
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });
    }

    /**
     * The games releasing on the date.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function queryGameSchedule()
    {
        return Game::withSchedule([$this->date->startOfDay()->toDateString()])
            ->select(Game::TABLE_NAME . '.*')
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });
    }
}
