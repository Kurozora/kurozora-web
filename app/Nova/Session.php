<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Text;

class Session extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Session::class;

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param Request $request
     * @return bool
     */
    public static function authorizedToViewAny(Request $request): bool
    {
        return $request->user()?->can('viewSession') ?? false;
    }

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Session|null
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
        'id'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Sessions';

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

            Text::make('IP Address', 'ip_address')
                ->sortable(),

            Text::make('User Agent', 'user_agent')
                ->hideFromIndex(),

            Code::make('Payload'),

            BelongsTo::make('User')
                ->searchable(),

            MorphOne::make('Session Attribute', 'session_attribute'),
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
     * Determine if the given resource is authorizable.
     *
     * @return bool
     */
    public static function authorizable(): bool
    {
        return false;
    }
}
