<?php

namespace App\Nova;

use Chaseconey\ExternalImage\ExternalImage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Person extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Person::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'full_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'first_name', 'last_name',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'People';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            ExternalImage::make('Image')
                ->height(100),

            Text::make('First name')
                ->help('The first name of the person as known in the industry. Usually in English.')
                ->rules(['required', 'max:255'])
                ->sortable(),

            Text::make('Last name')
                ->help('The last name of the person as known in the industry. Usually in English.')
                ->rules(['nullable', 'max:255'])
                ->sortable(),

            Text::make('Family name')
                ->help('The person’s official last name if the name they go by in the industry is different. Usually in Japanese.')
                ->rules(['nullable', 'max:255'])
                ->sortable(),

            Text::make('Given name')
                ->help('The person’s official first name if the name they go by in the industry is different. Usually in Japanese.')
                ->rules(['nullable', 'max:255'])
                ->sortable(),

            Code::make('AKA', 'alternative_names')
                ->json()
                ->sortable()
                ->help('Other names the person is known by. For example ["Nakamura Hiroaki", "中村 博昭"]')
                ->rules(['nullable', 'json']),

            Date::make('Birth Date')
                ->rules(['nullable', 'date'])
                ->sortable(),

            Textarea::make('About')
                ->onlyOnForms()
                ->help('A short description of the person.')
                ->rules(['nullable']),

            Text::make('Website URL')
                ->hideFromIndex()
                ->help('The URL to the official website of the studio.')
                ->rules(['nullable'])
                ->sortable(),

            HasMany::make('Cast'),

            HasMany::make('Anime'),

            HasMany::make('Characters'),
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
        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path fill="var(--sidebar-icon)" d="M12 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-2a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm9 11a1 1 0 0 1-2 0v-2a3 3 0 0 0-3-3H8a3 3 0 0 0-3 3v2a1 1 0 0 1-2 0v-2a5 5 0 0 1 5-5h8a5 5 0 0 1 5 5v2z"/>
        </svg>
    ';
}
