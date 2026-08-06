<?php

namespace App\Nova\Minigames\Kotodama;

use App\Nova\Resource;
use App\Nova\User;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class Game extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Minigames\Kotodama\Game::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Minigames\Kotodama\Game|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id', 'versus_seed'];

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

            Heading::make('Players'),
            BelongsTo::make('User', 'user', User::class)
                ->nullable()
                ->searchable(),

            Text::make('Guest Token', 'guest_token')
                ->onlyOnDetail(),

            Heading::make('Game'),
            BelongsTo::make('Word', 'word', Word::class)
                ->searchable(),

            BelongsTo::make('Daily Puzzle', 'dailyPuzzle', DailyPuzzle::class)
                ->nullable()
                ->searchable(),

            Number::make('Mode'),
            Number::make('Status'),
            Number::make('Guess Count', 'guess_count'),
            Text::make('Versus Seed', 'versus_seed')
                ->onlyOnDetail(),

            Heading::make('Timing'),
            DateTime::make('Started At', 'started_at'),
            DateTime::make('Finished At', 'finished_at'),
            Number::make('Duration (ms)', 'duration_ms'),
        ];
    }

    /**
     * Determine whether the resource supports creation via Nova.
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }
}
