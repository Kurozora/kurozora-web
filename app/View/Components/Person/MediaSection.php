<?php

namespace App\View\Components\Person;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class MediaSection extends Component
{
    /**
     * The person whose related models are shown.
     *
     * @var Person $person
     */
    public Person $person;

    /**
     * The class of the related models.
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
     * The URL of the full list.
     *
     * @var string $seeAllUrl
     */
    public string $seeAllUrl;

    /**
     * The URL that re-renders the section.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * The related models.
     *
     * @var Collection $models
     */
    public Collection $models;

    /**
     * Create a new component instance.
     *
     * @param Person $person
     * @param string $type
     */
    public function __construct(Person $person, string $type)
    {
        $this->person = $person;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('Anime'),
            Character::class => __('Characters'),
            Manga::class => __('Manga'),
            Game::class => __('Games'),
        };
        $this->seeAllUrl = match ($type) {
            Anime::class => route('people.anime', $person),
            Character::class => route('people.characters', $person),
            Manga::class => route('people.manga', $person),
            Game::class => route('people.games', $person),
        };
        $this->refreshUrl = match ($type) {
            Anime::class => route('people.section', [$person, 'anime'], false),
            Character::class => route('people.section', [$person, 'characters'], false),
            Manga::class => route('people.section', [$person, 'manga'], false),
            Game::class => route('people.section', [$person, 'games'], false),
        };
        $this->models = $this->loadModels();
    }

    /**
     * Whether the component should be rendered.
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
        return view('components.person.media-section');
    }

    /**
     * Load the related models.
     *
     * @return Collection
     */
    protected function loadModels(): Collection
    {
        return match ($this->type) {
            Anime::class => $this->person->anime()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
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
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
            Game::class => $this->person->games()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
        };
    }
}
