<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;

class Actor extends Person
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = 'App\Models\Actor';

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
    public static $group = 'Anime';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        $parentFields = parent::fields($request);

        return array_merge($parentFields, [
            HasMany::make('Cast'),

            HasMany::make('Anime'),

            HasMany::make('Characters'),
        ]);
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
        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
            <path fill="var(--sidebar-icon)" d="M50.0401999,100.9414 C77.3931405,100.9414 100.040601,78.2453091 100.040601,50.9414 C100.040601,23.5884594 77.3441491,0.9414 49.9912085,0.9414 C22.6872593,0.9414 0.04023,23.5884594 0.04023,50.9414 C0.04023,78.2453091 22.736351,100.9414 50.0401999,100.9414 Z M50.0401999,92.6080499 C26.9029257,92.6080499 8.42254131,74.0786742 8.42254131,50.9414 C8.42254131,27.8041258 26.8539343,9.27475007 49.9912085,9.27475007 C73.1284827,9.27475007 91.6578585,27.8041258 91.7071481,50.9414 C91.7562427,74.0786742 73.1774741,92.6080499 50.0401999,92.6080499 Z M49.9912085,60.4021537 C54.7950724,60.4021537 58.1284928,56.6767021 58.1284928,51.7256633 L58.1284928,30.6963227 C58.1284928,25.6962926 54.7950724,22.0198324 49.9912085,22.0198324 C45.1872442,22.0198324 41.8539243,25.6962926 41.8539243,30.6963227 L41.8539243,51.7256633 C41.8539243,56.6767021 45.1872442,60.4021537 49.9912085,60.4021537 Z M37.5402752,80.4021738 L62.4912336,80.4021738 C63.8637948,80.4021738 65.0892816,79.176677 65.0892816,77.8041358 C65.0892816,76.3825632 63.9127862,75.1080951 62.4912336,75.1080951 L52.6872392,75.1080951 L52.6872392,70.2060477 C62.3931505,69.0786441 68.9618077,61.6276405 68.9618077,51.676672 L68.9618077,45.4021637 C68.9618077,44.0296025 67.7363209,42.8041158 66.3637597,42.8041158 C64.9422071,42.8041158 63.7657117,44.0296025 63.7657117,45.4021637 L63.7657117,51.676672 C63.7657117,59.7158731 58.079401,65.3531925 49.9912085,65.3531925 C41.903016,65.3531925 36.2656967,59.7158731 36.2656967,51.676672 L36.2656967,45.4021637 C36.2656967,44.0296025 35.0402099,42.8041158 33.6186573,42.8041158 C32.2460961,42.8041158 31.0696007,44.0296025 31.0696007,45.4021637 L31.0696007,51.676672 C31.0696007,61.6276405 37.6382579,69.1276354 47.3931605,70.2060477 L47.3931605,75.1080951 L37.5402752,75.1080951 C36.1186222,75.1080951 34.8931354,76.3825632 34.8931354,77.8041358 C34.8931354,79.2256985 36.1186222,80.4021738 37.5402752,80.4021738 Z"/>
        </svg>
    ';
}
