<?php

namespace App\Http\Livewire\Browse\Manga\Seasons;

use App\Enums\SeasonOfYear;
use App\Models\Manga;
use App\Models\MediaType;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
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
    public function mount(int $year, string $season): void
    {
        if (!is_numeric($year)) {
            to_route('manga.seasons.index');
            return;
        }

        if ($year < 1917) {
            to_route('manga.seasons.index');
            return;
        }

        $this->year = $year;
        $this->season = str($season)->ucfirst();

        try {
            $this->seasonOfYear = SeasonOfYear::fromKey(str($season)->ucfirst());
        } catch (InvalidEnumKeyException $e) {
            to_route('manga.seasons.index');
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.manga.seasons.index', [
            'seasonOfYear' => $this->seasonOfYear,
            'mediaTypes' => MediaType::select(MediaType::TABLE_NAME . '.*')
                ->join(Manga::TABLE_NAME, function ($join) {
                    $join->on(Manga::TABLE_NAME . '.media_type_id', '=', MediaType::TABLE_NAME . '.id')
                        ->where('publication_season', '=', $this->seasonOfYear->value)
                        ->whereYear('started_at', '=', $this->year);
                })
                ->groupBy('id', 'name', 'description')
                ->get()
        ]);
    }
}
