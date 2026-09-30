<?php

namespace App\View\Components\Sections;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Editorial;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Reviews extends Component
{
    /**
     * The model the reviews belong to.
     *
     * @var Model $model
     */
    public Model $model;

    /**
     * The id of the page's review box.
     *
     * @var string|null $reviewBoxId
     */
    public ?string $reviewBoxId;

    /**
     * The published editorial endorsement of the model.
     *
     * @var Editorial|null $editorial
     */
    public ?Editorial $editorial;

    /**
     * The reviews shown in the section.
     *
     * @var Collection $reviews
     */
    public Collection $reviews;

    /**
     * The URL that renders the section again.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create a new component instance.
     *
     * @param Model       $model
     * @param string|null $reviewBoxId
     */
    public function __construct(Model $model, ?string $reviewBoxId = null)
    {
        $this->model = $model;
        $this->reviewBoxId = $reviewBoxId;
        $this->editorial = $model->editorial()
            ->published()
            ->first();
        $this->reviews = $model->mediaRatings()
            ->with(array_merge(['user.media', 'revisions'], MediaRating::lockupEagerLoads(auth()->user())))
            ->where('description', '!=', null)
            ->forReading()
            ->limit(6)
            ->get();

        $parameters = [$model, 'reviews', 'reviewBox' => $reviewBoxId];
        $this->refreshUrl = match ($model::class) {
            Anime::class => route('anime.section', $parameters, false),
            Manga::class => route('manga.section', $parameters, false),
            Game::class => route('games.section', $parameters, false),
            Character::class => route('characters.section', $parameters, false),
            Person::class => route('people.section', $parameters, false),
            Studio::class => route('studios.section', $parameters, false),
            Song::class => route('songs.section', $parameters, false),
            Episode::class => route('episodes.section', $parameters, false),
        };
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.sections.reviews');
    }
}
