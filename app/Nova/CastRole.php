<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class CastRole extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\CastRole::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\CastRole|null
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
        'id', 'name',
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

            ID::make()->sortable(),

            Heading::make('Meta information'),

            Text::make('Name')
                ->sortable()
                ->help('The name of the role.')
                ->required(),

            Text::make('Description')
                ->sortable()
                ->help('A short description of the role.')
                ->required(),

            HasMany::make('Cast', 'cast', AnimeCast::class),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $castRole = $this->resource;

        return $castRole->name . ' (ID: ' . $castRole->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewCastRole');
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
