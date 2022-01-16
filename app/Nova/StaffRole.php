<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class StaffRole extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\StaffRole::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\StaffRole|null
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
            Heading::make('Identification'),

            ID::make()->sortable(),

            Heading::make('Meta information'),

            Text::make('Name')
                ->sortable()
                ->help('The name of the role. For example, Color Design, Sound Effect, Titling, etc.')
                ->rules('unique:' . \App\Models\StaffRole::TABLE_NAME . ',name')
                ->required(),

            Text::make('Description')
                ->sortable()
                ->help('A short description of the role.')
                ->required(),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $staffRole = $this->resource;

        return $staffRole->name . ' (ID: ' . $staffRole->id . ')';
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
            <path fill="var(--sidebar-icon)" d="M49.9999699,100 C77.3529105,100 100.000371,77.3039091 100.000371,50 C100.000371,22.6470594 77.3039191,0 49.9509785,0 C22.6470293,0 0,22.6470594 0,50 C0,77.3039091 22.696121,100 49.9999699,100 Z M49.9999699,91.6666499 C26.8626957,91.6666499 8.38231131,73.1372742 8.38231131,50 C8.38231131,26.8627258 26.8137043,8.33335007 49.9509785,8.33335007 C73.0882527,8.33335007 91.6176285,26.8627258 91.6669181,50 C91.7160127,73.1372742 73.1372441,91.6666499 49.9999699,91.6666499 Z M62.499995,47.5000351 C67.6469992,47.5000351 72.0098404,42.8921367 72.0098404,36.9117774 C72.0098404,31.0294008 67.5980078,26.66666 62.499995,26.66666 C57.3528904,26.66666 52.9411582,31.1764753 52.9411582,36.9607688 C52.9901495,42.8921367 57.3528904,47.5000351 62.499995,47.5000351 Z M34.9999799,47.9901495 C39.4608039,47.9901495 43.2843386,44.0196407 43.2843386,38.7745534 C43.2843386,33.6764402 39.4608039,29.9019972 34.9999799,29.9019972 C30.5882477,29.9019972 26.6666299,33.7745233 26.6666299,38.8235448 C26.7157216,44.0196407 30.5392563,47.9901495 34.9999799,47.9901495 Z M21.6667001,69.5587967 L39.8529356,69.5587967 C37.3529707,65.9804195 40.3921417,58.7254817 45.5392463,54.8038639 C42.8431152,52.9901796 39.4117121,51.6666098 34.9999799,51.6666098 C24.2156564,51.6666098 17.5000251,59.6078282 17.5000251,66.1764854 C17.5000251,68.3333099 18.6765205,69.5587967 21.6667001,69.5587967 Z M47.500005,69.5587967 L77.4509936,69.5587967 C81.2254366,69.5587967 82.5490064,68.4803844 82.5490064,66.3725512 C82.5490064,60.2451174 74.8529456,51.7646929 62.499995,51.7646929 C50.098053,51.7646929 42.3529004,60.2451174 42.3529004,66.3725512 C42.3529004,68.4803844 43.7254617,69.5587967 47.500005,69.5587967 Z"/>
        </svg>
    ';
}
