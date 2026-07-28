<?php

namespace App\Nova\Minigames\Kotodama;

use App\Enums\Minigames\Kotodama\Difficulty;
use App\Nova\Actions\Minigames\Kotodama\ScheduleForDate;
use App\Nova\Anime;
use App\Nova\Character;
use App\Nova\Game;
use App\Nova\Manga;
use App\Nova\Person;
use App\Nova\Resource;
use App\Nova\Song;
use App\Nova\Studio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Word extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Minigames\Kotodama\Word::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Minigames\Kotodama\Word|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'answer';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id', 'answer', 'hint_text'];

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

            Heading::make('Word'),
            Text::make('Answer')
                ->sortable()
                ->help('Lowercase [a-z] letters only, 4–8 characters.')
                ->required()
                ->rules('string', 'min:4', 'max:8', 'regex:/^[a-z]+$/')
                ->creationRules(function (NovaRequest $request) {
                    return [
                        Rule::unique(\App\Models\Minigames\Kotodama\Word::TABLE_NAME, 'answer'),
                    ];
                })
                ->updateRules(function (NovaRequest $request) {
                    return [
                        Rule::unique(\App\Models\Minigames\Kotodama\Word::TABLE_NAME, 'answer')
                            ->ignore($request->resourceId),
                    ];
                }),

            Select::make('Difficulty')
                ->options([
                    Difficulty::Easy => 'Easy',
                    Difficulty::Normal => 'Normal',
                    Difficulty::Hard => 'Hard',
                ])
                ->displayUsingLabels()
                ->default(Difficulty::Normal)
                ->sortable()
                ->required(),

            Heading::make('Hints'),
            Textarea::make('Hint Text', 'hint_text')
                ->alwaysShow()
                ->help('Curator-authored hint shown once the player has made 3 wrong guesses.')
                ->rules('nullable', 'string', 'max:500'),

            MorphTo::make('Subject', 'subject')
                ->types([
                    Anime::class,
                    Manga::class,
                    Game::class,
                    Song::class,
                    Character::class,
                    Person::class,
                    Studio::class,
                ])
                ->searchable()
                ->nullable(),

            Heading::make('Availability'),
            Boolean::make('Is NSFW', 'is_nsfw')->default(false),
            Boolean::make('Is Active', 'is_active')->default(true),
            DateTime::make('Released At', 'released_at')
                ->nullable()
                ->help('Words are only eligible for scheduling after this date.'),
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
            new ScheduleForDate,
        ];
    }
}
