<?php

namespace App\Http\Livewire\Browse\Game\Seasons;

use App\Models\Game;
use App\Models\MediaType;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class SeasonsSection extends Component
{
    /**
     * The array containing the cast data.
     *
     * @var Collection $games
     */
    public Collection $games;

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
     * Prepare the component.
     *
     * @param MediaType $mediaType
     * @param int $seasonOfYear
     * @param int $year
     *
     * @return void
     */
    public function mount(MediaType $mediaType, int $seasonOfYear, int $year): void
    {
        $this->mediaType = $mediaType;
        $this->seasonOfYear = $seasonOfYear;
        $this->year = $year;
        $this->games = collect();
    }

    /**
     * Get the game with the given Media Type ID.
     */
    public function getGameForMediaType()
    {
        $this->games = Game::where([
            ['publication_season', '=', $this->seasonOfYear],
            ['media_type_id', '=', $this->mediaType->id]
        ])
            ->whereYear('published_at', '=', $this->year)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.game.seasons.seasons-section');
    }
}
