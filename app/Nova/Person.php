<?php

namespace App\Nova;

use Chaseconey\ExternalImage\ExternalImage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
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
     * The underlying model resource instance.
     *
     * @var \App\Models\Person|null
     */
    public $resource;

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

            BelongsToMany::make('Anime'),

            BelongsToMany::make('Characters'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $person = $this->resource;

        return $person->full_name . ' (ID: ' . $person->id . ')';
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
            <path fill="var(--sidebar-icon)" d="M50.1922388,50.0540199 C63.0431053,50.0540199 73.5183142,38.6068957 73.5183142,24.6760743 C73.5183142,10.8531821 63.0971805,0 50.1922388,0 C37.3952263,0 26.8660529,11.069151 26.8660529,24.7840035 C26.9200174,38.6609709 37.3412618,50.0540199 50.1922388,50.0540199 Z M50.1922388,41.9007251 C42.3087669,41.9007251 35.5593251,34.3412618 35.5593251,24.7840035 C35.5593251,15.3887495 42.2008378,8.15340535 50.1922388,8.15340535 C58.2374939,8.15340535 64.825042,15.2808203 64.825042,24.6760743 C64.825042,34.2333326 58.1295647,41.9007251 50.1922388,41.9007251 Z M16.2289502,100 L84.1014523,100 C93.0647685,100 97.3304025,97.3002343 97.3304025,91.3607145 C97.3304025,77.2138468 79.4578452,56.7494971 50.1922388,56.7494971 C20.8725574,56.7494971 3,77.2138468 3,91.3607145 C3,97.3002343 7.26563397,100 16.2289502,100 Z M13.6910672,91.8466721 C12.2872143,91.8466721 11.6932722,91.4686989 11.6932722,90.3347905 C11.6932722,81.4795029 25.4081248,64.9027919 50.1922388,64.9027919 C74.9221672,64.9027919 88.6371303,81.4795029 88.6371303,90.3347905 C88.6371303,91.4686989 88.0971528,91.8466721 86.6932999,91.8466721 L13.6910672,91.8466721 Z"/>
        </svg>
    ';
}
