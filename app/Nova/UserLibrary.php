<?php

namespace App\Nova;

use App\Enums\UserLibraryStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;

class UserLibrary extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\UserLibrary::class;

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
    public static $group = 'Users';

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

            BelongsTo::make('Anime')
                ->sortable()
                ->searchable(),

            BelongsTo::make('User')
                ->sortable()
                ->searchable(),

            Select::make('Status')
                ->options(UserLibraryStatus::asSelectArray())
                ->displayUsingLabels()
                ->required()
                ->sortable()
                ->help('The status of the anime in the library. For example: Watching'),

            Date::make('Start Date')
                ->default(now())
                ->format('DD-MM-YYYY')
                ->required()
                ->sortable()
                ->help('The date on which the user started tracking. For example: 2015-12-03'),

            Date::make('End Date')
                ->format('DD-MM-YYYY')
                ->sortable()
                ->help('The date on which the user finished tracking. For example: 2015-12-03'),
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
