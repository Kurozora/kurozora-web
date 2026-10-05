<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class Franchise extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Franchise::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Franchise|null
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
        'id', 'name', 'slug',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Franchise';

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

            Number::make('IGDB ID')
                ->hideFromIndex()
                ->nullable()
                ->help('Used to identify the Franchise on <a target="_blank" href="https://igdb.com/franchises/' . ($this->resource->slug ?? 'slug-identifier') . '">IGDB</a>'),

            Heading::make('Meta information'),

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the franchise in a URL: ' . config('app.url') . '/franchise/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from name.'),

            Text::make('Name')
                ->sortable()
                ->help('The name of the franchise. For example Final Fantasy.')
                ->required(),

            MorphToMany::make('Animes', 'animes', Anime::class)
                ->searchable(),

            MorphToMany::make('Mangas', 'mangas', Manga::class)
                ->searchable(),

            MorphToMany::make('Games', 'games', Game::class)
                ->searchable(),
        ];
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewFranchise');
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
