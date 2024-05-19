<?php

namespace App\Livewire\Browse\Game\Seasons;

use App\Enums\SeasonOfYear;
use App\Models\Game;
use App\Models\MediaType;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    /**
     * Thes selected year.
     *
     * @var int $year
     */
    public int $year;

    /**
     * The selected season.
     *
     * @var string $season
     */
    public string $season;

    /**
     * Prepare the component.
     *
     * @param string $season
     * @param int $year
     *
     * @return void
     */
    public function mount(string $season, int $year): void
    {
        if ($year < 1917) {
            to_route('games.seasons.index');
            return;
        }

        if (!is_numeric($year)) {
            to_route('games.seasons.index');
            return;
        }

        try {
            $this->season = SeasonOfYear::fromKey(str($season)->ucfirst())->key;
        } catch (InvalidEnumKeyException $e) {
            to_route('manga.seasons.index');
            return;
        }
        $this->year = $year;
    }

    /**
     * Get the SeasonOfYear object.
     *
     * @return SeasonOfYear
     * @throws InvalidEnumKeyException
     */
    public function getSeasonOfYearProperty(): SeasonOfYear
    {
        return SeasonOfYear::fromKey(str($this->season)->ucfirst());
    }

    /**
     * Get the available Media Types.
     *
     * @return Collection
     */
    public function getMediaTypesProperty(): Collection
    {
        return MediaType::select(MediaType::TABLE_NAME . '.*')
            ->join(Game::TABLE_NAME, function ($join) {
                $join->on(Game::TABLE_NAME . '.media_type_id', '=', MediaType::TABLE_NAME . '.id')
                    ->where([
                        ['publication_season', '=', $this->seasonOfYear->value],
                        ['published_at', '>=', $this->year . '-01-01'],
                        ['published_at', '<=', $this->year . '-12-31'],
                    ]);
            })
            ->groupBy('id', 'name', 'description')
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.game.seasons.index');
    }
}
