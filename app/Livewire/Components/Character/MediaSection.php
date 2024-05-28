<?php

namespace App\Livewire\Components\Character;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
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
     * The object containing the character data.
     *
     * @var Character $character
     */
    public Character $character;

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
     * @param Character $character
     * @param string $type
     * @return void
     */
    public function mount(Character $character, string $type): void
    {
        $this->character = $character;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('Anime'),
            Person::class => __('People'),
            Manga::class => __('Manga'),
            Game::class => __('Games'),
        };
        $this->seeAllURL = match ($type) {
            Anime::class => route('characters.anime', $character),
            Person::class => route('characters.people', $character),
            Manga::class => route('characters.manga', $character),
            Game::class => route('characters.games', $character),
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
            Anime::class => $this->character->anime()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
            Person::class => $this->character->people()
                ->with(['media'])
                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
            Manga::class => $this->character->manga()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
            Game::class => $this->character->games()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.character.media-section');
    }
}
