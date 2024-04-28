<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class TvRating extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\TvRating::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\TvRating|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
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

            Heading::make('Meta information'),

            Text::make('Name')
                ->help('A short name for the rating. E.g. N, G, PG...')
                ->required(),

            Text::make('Description')
                ->help('A very short description of the rating. E.g. Not Rated, All Ages, Children...')
                ->required(),

            Number::make('Weight')
                ->help('The priority of the rating. E.g: if a TV rating with a weight of 5 is selected, all ratings that are less than, and equal to, 5 are accessible to the user.')
                ->rules(['min:0', 'max:255'])
                ->required(),

            HasMany::make('Anime'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $tvRating = $this->resource;

        return $tvRating->name . ' (ID: ' . $tvRating->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewTvRating');
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
