<?php

namespace App\Http\Livewire\Schedule;

use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    /**
     * The selected date.
     *
     * @var string $date
     */
    public string $date = '';

    /**
     * The query strings of the component.
     *
     * @return array
     */
    public function getQueryString(): array
    {
        return [
            'date' => ['except' => today()->toDateString()],
        ];
    }

    /**
     * Prepare the component.
     *
     * @param null|string $date
     *
     * @return void
     */
    public function mount(?string $date = null): void
    {
        $this->date = $date ?? today()->toDateString();
    }

    /**
     * Get a week's worth of dates.
     *
     * @return array
     */
    public function getDatesProperty(): array
    {
        $date = Carbon::createFromFormat('Y-m-d', $this->date);

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
