<?php

namespace App\Http\Livewire\Browse\Anime;

use App\Enums\SeasonOfYear;
use App\Models\Anime;
use App\Models\MediaType;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Seasons extends Component
{
    /**
     * Thes selected year.
     *
     * @var $year
     */
    public $year;

    /**
     * The selected season.
     *
     * @var $season
     */
    public $season;

    /**
     * The selected season of year type.
     *
     * @var SeasonOfYear $seasonOfYear
     */
    protected SeasonOfYear $seasonOfYear;

    /**
     * Prepare the component.
     *
     * @param int $year
     * @param string $season
     *
     * @return void
     */
    public function mount($year, $season)
    {
        if (!is_numeric($year)) {
            to_route('anime.index.seasons.index');
        }

        if ($year < 1917) {
            to_route('anime.index.seasons.index');
        }

        $this->year = $year;
        $this->season = str($season)->ucfirst();

        try {
            $this->seasonOfYear = SeasonOfYear::fromKey(str($season)->ucfirst());
        } catch (InvalidEnumKeyException $e) {
            to_route('anime.index.seasons.index');
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.anime.seasons', [
            'seasonOfYear' => $this->seasonOfYear,
            'mediaTypes' => MediaType::select(MediaType::TABLE_NAME . '.*')
                ->join(Anime::TABLE_NAME, function ($join) {
                    $join->on(Anime::TABLE_NAME . '.media_type_id', '=', MediaType::TABLE_NAME . '.id')
                        ->where('air_season', '=', $this->seasonOfYear->value)
                        ->whereYear('first_aired', '=', $this->year);
                })
                ->groupBy('id', 'name', 'description')
                ->get()
        ]);
    }
}
