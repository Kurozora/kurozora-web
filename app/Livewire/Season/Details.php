<?php

namespace App\Livewire\Season;

use App\Models\Anime;
use App\Models\Season;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Details extends Component
{
    use WithPagination;

    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime): void
    {
        $this->anime = $anime->load(['media', 'translation']);
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
     * Get the season property.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getSeasonsProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $seasons = $this->anime->seasons()
            ->withoutGlobalScopes()
            ->with([
                'media',
                'translation',
            ])
            ->withCount([
                'episodes' => function ($query) {
                    $query->withoutGlobalScopes();
                },
            ])
            ->withAvg([
                'episodesMediaStats as rating_average' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->where('rating_average', '!=', 0);
                },
            ], 'rating_average')
            ->paginate(25);

        $seasons->each(function (Season $season) {
            return $season->setRelation('anime', $this->anime);
        });

        return $seasons;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.season.details');
    }
}
