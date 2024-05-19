<?php

namespace App\Livewire\Components\Browse;

use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class SeasonsSection extends Component
{
    /**
     * The morphable class of a model.
     *
     * @var string $class
     */
    public string $class;

    /**
     * The object containing the media type data.
     *
     * @var MediaType $mediaType
     */
    public MediaType $mediaType;

    /**
     * The selected season of year.
     *
     * @var int $seasonOfYear
     */
    public int $seasonOfYear;

    /**
     * The selected year.
     *
     * @var int $year
     */
    public int $year;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param string $class
     * @param MediaType $mediaType
     * @param int $seasonOfYear
     * @param int $year
     *
     * @return void
     */
    public function mount(string $class, MediaType $mediaType, int $seasonOfYear, int $year): void
    {
        $this->class = $class;
        $this->mediaType = $mediaType;
        $this->seasonOfYear = $seasonOfYear;
        $this->year = $year;
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
     * Get the anime with the given Media Type ID.
     *
     * @return Collection
     */
    public function getModelsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $seasonOfYearKey = match ($this->class) {
           Game::class, Manga::class => 'publication_season',
            default => 'air_season'
        };
        $startedAtKey = match ($this->class) {
           Game::class => 'published_at',
            default => 'started_at'
        };

        return $this->class::where([
            [$seasonOfYearKey, '=', $this->seasonOfYear],
            ['media_type_id', '=', $this->mediaType->id],
            [$startedAtKey, '>=', $this->year . '-01-01'],
            [$startedAtKey, '<=', $this->year . '-12-31'],
        ])
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.browse.seasons-section');
    }
}
