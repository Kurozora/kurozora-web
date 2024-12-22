<?php

namespace App\Livewire\Sections;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class Schedule extends Component
{
    /**
     * The morphable class of a model.
     *
     * @var string $class
     */
    public string $class;

    /**
     * The selected date.
     *
     * @var Carbon $date
     */
    public Carbon $date;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param string $class
     * @param Carbon $date
     *
     * @return void
     */
    public function mount(string $class, Carbon $date): void
    {
        $this->class = $class;
        $this->date = $date;
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Get the anime with episodes on the given date.
     *
     * @return Collection
     */
    public function getModelsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return match ($this->class) {
            Anime::class => $this->queryAnimeSchedule()
                ->get(),
            Manga::class => $this->queryMangaSchedule()
                ->get(),
            Game::class => $this->queryGameSchedule()
                ->get(),
            default => collect(),
        };
    }

    private function queryAnimeSchedule()
    {
        return Anime::withSchedule([
            [
                'start' => $this->date->startOfDay()->toDateTimeString(),
                'end' => $this->date->endOfDay()->toDateTimeString()
            ]
        ])
            ->select(Anime::TABLE_NAME . '.*')
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });
    }

    private function queryMangaSchedule()
    {
        return Manga::withSchedule([$this->date->dayOfWeek])
            ->select(Manga::TABLE_NAME . '.*')
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });
    }

    private function queryGameSchedule()
    {
        return Game::withSchedule([$this->date->startOfDay()->toDateString()])
            ->select(Game::TABLE_NAME . '.*')
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });

    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.sections.schedule');
    }
}
