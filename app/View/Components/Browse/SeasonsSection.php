<?php

namespace App\View\Components\Browse;

use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class SeasonsSection extends Component
{
    /**
     * The class of the browsed titles.
     *
     * @var string $class
     */
    public string $class;

    /**
     * The media type of the section.
     *
     * @var MediaType $mediaType
     */
    public MediaType $mediaType;

    /**
     * The titles of the season.
     *
     * @var Collection $models
     */
    public Collection $models;

    /**
     * Create a new component instance.
     *
     * @param string    $class
     * @param MediaType $mediaType
     * @param int       $seasonOfYear
     * @param int       $year
     */
    public function __construct(string $class, MediaType $mediaType, int $seasonOfYear, int $year)
    {
        $this->class = $class;
        $this->mediaType = $mediaType;
        $this->models = $this->loadModels($seasonOfYear, $year);
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
        return view('components.browse.seasons-section');
    }

    /**
     * Load the titles of the season.
     *
     * @param int $seasonOfYear
     * @param int $year
     *
     * @return Collection
     */
    protected function loadModels(int $seasonOfYear, int $year): Collection
    {
        $seasonOfYearKey = match ($this->class) {
            Game::class, Manga::class => 'publication_season',
            default => 'air_season'
        };
        $startedAtKey = match ($this->class) {
            Game::class => 'published_at',
            default => 'started_at'
        };

        return $this->class::where([
            [$seasonOfYearKey, '=', $seasonOfYear],
            ['media_type_id', '=', $this->mediaType->id],
            [$startedAtKey, '>=', $year . '-01-01'],
            [$startedAtKey, '<=', $year . '-12-31'],
        ])
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->get();
    }
}
