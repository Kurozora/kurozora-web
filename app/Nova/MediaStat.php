<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class MediaStat extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\MediaStat::class;

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
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification'),

            ID::make(__('ID'), 'id')->sortable(),

            Heading::make('Model'),

            MorphTo::make('Model')
                ->types([
                    Anime::class,
                ])
                ->searchable()
                ->sortable(),

            Text::make('Model Type')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->sortable(),

            Heading::make('Stats'),

            Number::make('Planning', 'planning_count')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable(),

            Number::make('Watching', 'watching_count')
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
                ->step(0.1)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->help('The average of all ratings.'),

            Number::make('Rating Count')
                ->default(0)
                ->rules(['required', 'numeric'])
                ->sortable()
                ->help('The total count of all ratings.'),
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
}
