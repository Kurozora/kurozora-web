<?php

namespace App\Livewire\Components\Studio;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Studio;
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
     * The object containing the studio data.
     *
     * @var Studio $studio
     */
    public Studio $studio;

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
     * The 'See All' url string.
     *
     * @var string $seeAllURL
     */
    public string $seeAllURL;

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
     * @param string $type
     * @return void
     */
    public function mount(Studio $studio, string $type): void
    {
        $this->studio = $studio;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('Anime'),
            Manga::class => __('Manga'),
            Game::class => __('Games'),
        };
        $this->seeAllURL = match ($type) {
            Anime::class => route('studios.anime', $studio),
            Manga::class => route('studios.manga', $studio),
            Game::class => route('studios.games', $studio),
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

        $query = match ($this->type) {
            Anime::class => $this->studio->anime(),
            Manga::class => $this->studio->manga(),
            Game::class => $this->studio->games(),
        };

        return $query->when($this->studio->tv_rating_id > config('app.tv_rating'), function ($query) {
            $query->withoutGlobalScopes();
        })
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
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
        return view('livewire.components.studio.media-section');
    }
}
