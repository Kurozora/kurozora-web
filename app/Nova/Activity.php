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

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M67.6875,46.875 L45.8125,46.875 C44.953125,46.875 44.25,47.578125 44.25,48.4375 L44.25,51.5625 C44.25,52.421875 44.953125,53.125 45.8125,53.125 L67.6875,53.125 C68.546875,53.125 69.25,52.421875 69.25,51.5625 L69.25,48.4375 C69.25,47.578125 68.546875,46.875 67.6875,46.875 Z M67.6875,65.625 L45.8125,65.625 C44.953125,65.625 44.25,66.328125 44.25,67.1875 L44.25,70.3125 C44.25,71.171875 44.953125,71.875 45.8125,71.875 L67.6875,71.875 C68.546875,71.875 69.25,71.171875 69.25,70.3125 L69.25,67.1875 C69.25,66.328125 68.546875,65.625 67.6875,65.625 Z M34.875,45.3125 C32.2773438,45.3125 30.1875,47.4023438 30.1875,50 C30.1875,52.5976562 32.2773438,54.6875 34.875,54.6875 C37.4726562,54.6875 39.5625,52.5976562 39.5625,50 C39.5625,47.4023438 37.4726562,45.3125 34.875,45.3125 Z M34.875,64.0625 C32.2773438,64.0625 30.1875,66.1523438 30.1875,68.75 C30.1875,71.3476562 32.2773438,73.4375 34.875,73.4375 C37.4726562,73.4375 39.5625,71.3476562 39.5625,68.75 C39.5625,66.1523438 37.4726562,64.0625 34.875,64.0625 Z M78.625,12.5 L63,12.5 C63,5.60546875 57.3945312,0 50.5,0 C43.6054688,0 38,5.60546875 38,12.5 L22.375,12.5 C17.1992188,12.5 13,16.6992188 13,21.875 L13,90.625 C13,95.8007812 17.1992188,100 22.375,100 L78.625,100 C83.8007812,100 88,95.8007812 88,90.625 L88,21.875 C88,16.6992188 83.8007812,12.5 78.625,12.5 Z M50.5,9.375 C52.21875,9.375 53.625,10.78125 53.625,12.5 C53.625,14.21875 52.21875,15.625 50.5,15.625 C48.78125,15.625 47.375,14.21875 47.375,12.5 C47.375,10.78125 48.78125,9.375 50.5,9.375 Z M78.625,89.0625 C78.625,89.921875 77.921875,90.625 77.0625,90.625 L23.9375,90.625 C23.078125,90.625 22.375,89.921875 22.375,89.0625 L22.375,23.4375 C22.375,22.578125 23.078125,21.875 23.9375,21.875 L31.75,21.875 L31.75,28.125 C31.75,29.84375 33.15625,31.25 34.875,31.25 L66.125,31.25 C67.84375,31.25 69.25,29.84375 69.25,28.125 L69.25,21.875 L77.0625,21.875 C77.921875,21.875 78.625,22.578125 78.625,23.4375 L78.625,89.0625 Z"/>
        </svg>
    ';
}
