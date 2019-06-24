<?php

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Session extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Session';

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
        'id', 'device'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('User')
                ->searchable(),

            Text::make('Device')
                ->rules('required', 'max:50')
                ->sortable(),

            Text::make('IP Address', 'ip')
                ->rules('required', 'max:255')
                ->hideFromIndex(),

            Text::make('Secret Token', 'secret')
                ->rules('required', 'max:128')
                ->hideFromIndex(),

            Text::make('City')
                ->rules('max:255')
                ->hideFromIndex(),

            Text::make('Region')
                ->rules('max:255')
                ->hideFromIndex(),

            Text::make('Country')
                ->rules('max:255')
                ->hideFromIndex(),

            Number::make('Latitude (coordinates)', 'latitude')
                ->step(0.001)
                ->hideFromIndex(),

            Number::make('Longitude (coordinates)', 'longitude')
                ->step(0.001)
                ->hideFromIndex(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Determine if the given resource is authorizable.
     *
     * @return bool
     */
    public static function authorizable()
    {
        return false;
    }
}
