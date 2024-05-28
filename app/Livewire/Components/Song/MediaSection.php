<?php

namespace App\Livewire\Components\Song;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Song;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class MediaSection extends Component
{
    /**
     * The object containing the song data.
     *
     * @var Song $song
     */
    public Song $song;

    /**
     * The type of the favorite section.
     *
     * @var string $type
     */
    public string $type;

    /**
     * The title of the section.
     *
     * @var string $title
     */
    public string $title;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param Song $song
     * @param string $type
     * @return void
     */
    public function mount(Song $song, string $type): void
    {
        $this->song = $song;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('As Heard On Shows'),
            Game::class => __('As Heard On Games'),
        };
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
     * Returns the user's feed messages.
     *
     * @return Collection
     */
    public function getModelsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return match ($this->type) {
            Anime::class => $this->song->anime()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->get(),
            Game::class => $this->song->games()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->get()
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.song.media-section');
    }
}
