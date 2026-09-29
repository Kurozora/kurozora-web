<?php

namespace App\View\Components\Song;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Song;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class MediaSection extends Component
{
    /**
     * The song whose media is shown.
     *
     * @var Song $song
     */
    public Song $song;

    /**
     * The class of the media shown.
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
     * The media shown in the section.
     *
     * @var Collection $models
     */
    public Collection $models;

    /**
     * The URL that renders the section again.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create the component.
     *
     * @param Song   $song
     * @param string $type
     */
    public function __construct(Song $song, string $type)
    {
        $this->song = $song;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('As Heard On Shows'),
            Game::class => __('As Heard On Games'),
        };
        $this->models = $this->loadModels();
        $this->refreshUrl = match ($type) {
            Anime::class => route('songs.section', [$song, 'anime'], false),
            Game::class => route('songs.section', [$song, 'games'], false),
        };
    }

    /**
     * Whether the song has media of the type to show.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->models->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.song.media-section');
    }

    /**
     * Loads the media of the type the song appears in.
     *
     * @return Collection
     */
    protected function loadModels(): Collection
    {
        return match ($this->type) {
            Anime::class => $this->song->anime()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->get(),
            Game::class => $this->song->games()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->get(),
        };
    }
}
