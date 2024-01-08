<?php

namespace App\Http\Livewire\Sections;

use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

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

        $dateKey = match ($this->class) {
            Game::class, Manga::class => 'publication_day',
            default => 'air_day'
        };

        return $this->class::where([
            [$dateKey, '=', $this->date->dayOfWeek],
        ])
            ->whereHas('episodes', function($query) {
                $query->where([
                    [Episode::TABLE_NAME . '.started_at', '>=', $this->date->startOfDay()->toDateTimeString()],
                    [Episode::TABLE_NAME . '.started_at', '<=', $this->date->endOfDay()->toDateTimeString()],
                ]);
            })
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderBy('air_time', 'desc')
            ->get();
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
