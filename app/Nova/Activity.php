<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;

class Activity extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \Spatie\Activitylog\Models\Activity::class;

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param Request $request
     * @return bool
     */
    public static function authorizedToViewAny(Request $request): bool
    {
        return $request->user()?->can('viewActivity') ?? false;
    }

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
        'id', 'log_name', 'description'
    ];

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

            Heading::make('Meta information'),

            Text::make('Log name')
                ->sortable(),

            Text::make('Description')
                ->rules('required'),

            MorphTo::make('Subject'),

            MorphTo::make('Causer')
                ->hideFromIndex(),

            Code::make('Properties')
                ->json()
                ->hideFromIndex(),

            DateTime::make('When', 'created_at')
                ->sortable()
        ];
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
