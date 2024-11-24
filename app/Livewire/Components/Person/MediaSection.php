<?php

namespace App\Livewire\Components\Person;

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
     * The object containing the person data.
     *
     * @var Person $person
     */
    public Person $person;

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
     * @param Person $person
     * @param string $type
     * @return void
     */
    public function mount(Person $person, string $type): void
    {
        $this->person = $person;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('Anime'),
            Character::class => __('Characters'),
            Manga::class => __('Manga'),
            Game::class => __('Games'),
        };
        $this->seeAllURL = match ($type) {
            Anime::class => route('people.anime', $person),
            Character::class => route('people.characters', $person),
            Manga::class => route('people.manga', $person),
            Game::class => route('people.games', $person),
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
            Anime::class => $this->person->anime()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
            Character::class => $this->person->characters()
                ->with(['media', 'translation'])
                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
            Manga::class => $this->person->manga()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
            Game::class => $this->person->games()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT)
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
        return view('livewire.components.person.media-section');
    }
}
