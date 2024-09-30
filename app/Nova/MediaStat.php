<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class MediaStat extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MediaStat::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\MediaStat|null
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
    public static $search = [
        'id',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Media';

    /**
     * Determine if this resource uses Laravel Scout.
     *
     * @return bool
     */
    public static function usesScout(): bool
    {
        return false;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            Heading::make('Model')
                ->onlyOnDetail(),

            MorphTo::make('Model')
                ->types([
                    Anime::class,
                    Character::class,
                    Episode::class,
                    Game::class,
                    Manga::class,
                    Person::class,
                    Song::class,
                    Studio::class,
                ])
                ->searchable(),

            Text::make('Model Type')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->sortable(),

            Heading::make('Stats')
                ->onlyOnDetail(),

            Number::make('In Progress', 'in_progress_count')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable(),

            Number::make('Planning', 'planning_count')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable(),

            Number::make('Completed', 'completed_count')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable(),

            Number::make('On-Hold', 'on_hold_count')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable(),

            Number::make('Dropped', 'dropped_count')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable(),

            Number::make('Interested', 'interested_count')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable(),

            Number::make('Ignored', 'ignored_count')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable(),

            Number::make('Rating 1')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->hideFromIndex()
                ->help('Indicates a rating of <b>0.5</b>'),

            Number::make('Rating 2')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->hideFromIndex()
                ->help('Indicates a rating of <b>1.0</b>'),

            Number::make('Rating 3')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->hideFromIndex()
                ->help('Indicates a rating of <b>1.5</b>'),

            Number::make('Rating 4')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->hideFromIndex()
                ->help('Indicates a rating of <b>2.0</b>'),

            Number::make('Rating 5')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->hideFromIndex()
                ->help('Indicates a rating of <b>2.5</b>'),

            Number::make('Rating 6')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->hideFromIndex()
                ->help('Indicates a rating of <b>3.0</b>'),

            Number::make('Rating 7')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->hideFromIndex()
                ->help('Indicates a rating of <b>3.5</b>'),

            Number::make('Rating 8')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->hideFromIndex()
                ->help('Indicates a rating of <b>4.0</b>'),

            Number::make('Rating 9')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->hideFromIndex()
                ->help('Indicates a rating of <b>4.5</b>'),

            Number::make('Rating 10')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->hideFromIndex()
                ->help('Indicates a rating of <b>5.0</b>'),

            Number::make('Rating Average')
                ->default(0.0)
                ->readonly()
                ->sortable()
                ->help('The average of all ratings.'),

            Number::make('Rating Count')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->help('The total count of all ratings.'),

            Number::make('Global Rank', 'rank_global')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->help('The rank compared to all model types, i.e. anime, manga, game and music.'),

            Number::make('Total Rank', 'rank_total')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->help('The rank within a model type, e.g. within the anime type.'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $mediaStats = $this->resource;

        return ($mediaStats->model->title ?? '') . ' Stats (ID: ' . $mediaStats->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewMediaStats');
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request): array
    {
        return [];
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return parent::indexQuery($request, $query)
            ->with([
                'model' => function (\Illuminate\Database\Eloquent\Relations\MorphTo $morphTo) {
                    $morphTo->constrain([
                        \App\Models\Anime::class => function (Builder $query) {
                            $query->with(['translations']);
                        },
                        \App\Models\Episode::class => function (Builder $query) {
                            $query->with(['translations']);
                        },
                        \App\Models\Game::class => function (Builder $query) {
                            $query->with(['translations']);
                        },
                        \App\Models\Manga::class => function (Builder $query) {
                            $query->with(['translations']);
                        },
                        \App\Models\Song::class => function (Builder $query) {
                            $query->with(['translations']);
                        },
                    ]);
                }
            ]);
    }
}
