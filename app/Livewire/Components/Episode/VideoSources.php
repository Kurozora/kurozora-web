<?php

namespace App\Livewire\Components\Episode;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Component;

class VideoSources extends Component
{
    /**
     * The object containing the model data.
     *
     * @var Model $model
     */
    public Model $model;

    /**
     * The user's preferred video source.
     *
     * @var string
     */
    public $preferredVideoSource = 'Default';

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Model $model
     * @return void
     */
    public function mount(Model $model): void
    {
        $this->model = $model;
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
     * Select the preferred video source.
     *
     * @param string $source
     * @return void
     */
    public function selectPreferredSource(string $source): void
    {
        $this->dispatch('preferredVideoSourceChanged', source: $source);
        $this->preferredVideoSource = $source;
    }

    /**
     * A list of videos.
     *
     * @return Collection
     */
    public function getVideosProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->model->videos;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.episode.video-sources');
    }
}
