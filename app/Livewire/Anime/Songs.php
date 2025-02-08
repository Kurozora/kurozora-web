<?php

namespace App\Livewire\Anime;

use App\Enums\SongType;
use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Songs extends Component
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
     * Get the list of media songs.
     *
     * @return Collection
     */
    public function getMediaSongsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $sort = SongType::asSelectArray();

        return $this->anime->mediaSongs()
            ->with([
                'song' => function ($query) {
                    $query->with(['media']);
                }
            ])
            ->get()
            ->sortBy(['position'])
            ->groupBy('type.description')
            ->sortKeysUsing(function ($key1, $key2) use ($sort) {
                $key1 = array_search($key1, $sort);
                $key2 = array_search($key2, $sort);

                return $key1 < $key2 ? -1 : 1;
            });
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.songs');
    }
}
