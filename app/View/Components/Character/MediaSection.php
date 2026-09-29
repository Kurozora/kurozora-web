<?php

namespace App\View\Components\Character;

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
     * The character whose related models are shown.
     *
     * @var Character $character
     */
    public Character $character;

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
     * @param Character $character
     * @param string    $type
     */
    public function __construct(Character $character, string $type)
    {
        $this->character = $character;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('Anime'),
            Person::class => __('People'),
            Manga::class => __('Manga'),
            Game::class => __('Games'),
        };
        $this->seeAllUrl = match ($type) {
            Anime::class => route('characters.anime', $character),
            Person::class => route('characters.people', $character),
            Manga::class => route('characters.manga', $character),
            Game::class => route('characters.games', $character),
        };
        $this->refreshUrl = match ($type) {
            Anime::class => route('characters.section', [$character, 'anime'], false),
            Person::class => route('characters.section', [$character, 'people'], false),
            Manga::class => route('characters.section', [$character, 'manga'], false),
            Game::class => route('characters.section', [$character, 'games'], false),
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
        return view('components.character.media-section');
    }

    /**
     * Load the related models.
     *
     * @return Collection
     */
    protected function loadModels(): Collection
    {
        return match ($this->type) {
            Anime::class => $this->character->anime()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
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
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
            Game::class => $this->character->games()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT)
                ->get(),
        };
    }
}
