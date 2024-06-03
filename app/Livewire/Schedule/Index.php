<?php

namespace App\Livewire\Schedule;

use App\Enums\ScheduleKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    /**
     * The selected schedule type.
     *
     * @var string $type
     */
    public string $type = '';

    /**
     * The selected date.
     *
     * @var string $date
     */
    public string $date = '';

    /**
     * The selected schedule type's class.
     *
     * @var string $class
     */
    public string $class = Anime::class;

    /**
     * The query strings of the component.
     *
     * @return array
     */
    protected function queryString(): array
    {
        return [
            'type' => ['except' => ScheduleKind::Anime()->key],
            'date' => ['except' => today()->toDateString()],
        ];
    }

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        if (empty($this->type)) {
            $this->type = ScheduleKind::Anime()->key;
        }

        $this->class = match ($this->type) {
            strtolower(ScheduleKind::Game()->key) => Game::class,
            strtolower(ScheduleKind::Manga()->key) => Manga::class,
            default => Anime::class
        };

        if (empty($this->date)) {
            $this->date = today()->toDateString();
        }
    }

    /**
     * Get a week's worth of dates.
     *
     * @return array
     */
    public function getDatesProperty(): array
    {
        $date = Carbon::createFromFormat('Y-m-d', $this->date) ?? now();

        // Get the previous day's date
        $previousDay = $date->copy()->subDay();
        $dateCollection[] = $previousDay;

        // Add the given date to the collection
        $dateCollection[] = $date;

        // Get the next 5 days' dates
        for ($i = 1; $i <= 5; $i++) {
            $nextDay = $date->copy()->addDays($i);
            $dateCollection[] = $nextDay;
        }

        return $dateCollection;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.schedule.index');
    }
}
