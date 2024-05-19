<?php

namespace App\Livewire\Profile;

use App\Contracts\Web\Profile\UpdatesUserPreferredTimezone;
use DateTimeZone;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class SelectPreferredTimezoneForm extends Component
{
    /**
     * The component's state.
     *
     * @var array
     */
    public array $state = [];

    /**
     * Determines whether to load the section.
     *
     * @var bool $readyToLoad
     */
    public $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->state = [
            'timezone' => auth()->user()->timezone
        ];
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
     * Update the user's preferred timezone.
     *
     * @param UpdatesUserPreferredTimezone $updater
     */
    public function updatePreferredTimezone(UpdatesUserPreferredTimezone $updater): void
    {
        $this->resetErrorBag();

        $updater->update(auth()->user(), $this->state);

        $this->dispatch('saved');
    }

    /**
     * Get the tv ratings.
     *
     * @return Collection
     */
    public function getTimezonesProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $timestamp = time();
        $timezone = [];

        foreach (timezone_identifiers_list(DateTimeZone::ALL) as $key => $value) {
            date_default_timezone_set($value);
            $timezone[$value] = $value . ' (UTC ' . date('P', $timestamp) . ')';
        }

        return collect($timezone)->sortKeys();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.select-preferred-timezone-form');
    }
}
