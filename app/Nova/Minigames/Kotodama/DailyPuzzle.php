<?php

namespace App\Nova\Minigames\Kotodama;

use App\Nova\Actions\Minigames\Kotodama\RegeneratePuzzle;
use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class DailyPuzzle extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Minigames\Kotodama\DailyPuzzle::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Minigames\Kotodama\DailyPuzzle|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'puzzle_number';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id', 'puzzle_number'];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Minigames';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification'),
            ID::make()->sortable(),

            Heading::make('Schedule'),
            BelongsTo::make('Word', 'word', Word::class)
                ->searchable()
                ->required(),

            Date::make('Puzzle Date', 'puzzle_date')
                ->sortable()
                ->required(),

            Number::make('Puzzle Number', 'puzzle_number')
                ->sortable()
                ->required(),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     *
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [
            new RegeneratePuzzle,
        ];
    }
}
