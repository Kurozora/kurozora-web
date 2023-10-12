<?php

namespace App\Http\Livewire\Studio;

use App\Events\StudioViewed;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the studio data.
     *
     * @var Studio $studio
     */
    public Studio $studio;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Studio $studio
     *
     * @return void
     */
    public function mount(Studio $studio): void
    {
        // Call the StudioViewed event
        StudioViewed::dispatch($studio);

        $this->studio = $studio->load(['media']);
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * The studio's animes.
     *
     * @return Collection
     */
    public function getAnimesProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->studio->anime()
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }

    /**
     * The studio's mangas.
     *
     * @return Collection
     */
    public function getMangasProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->studio->manga()
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }

    /**
     * The studio's games.
     *
     * @return Collection
     */
    public function getGamesProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->studio->games()
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.studio.details');
    }
}
