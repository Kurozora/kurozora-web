<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class Relation extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Relation::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Relation|null
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
    public static $group = 'Relations';

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
                ->sortable()
                ->help('The name of the relation.')
                ->required(),

            Text::make('Description')
                ->sortable()
                ->help('An explanation of what the relation means.')
                ->required(),

            HasMany::make('Anime', 'anime', MediaRelation::class),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $relation = $this->resource;

        return $relation->name . ' (ID: ' . $relation->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewRelation');
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
            <path fill="var(--sidebar-icon)" d="M49.4105764,69.3819151 L55.682003,63.0300912 C49.4577989,62.5595679 45.4026459,60.677571 42.3376216,57.6192176 C34.085841,49.3853967 34.132967,37.7168424 42.2904956,29.5771647 L57.6624535,14.2386634 C65.9142341,6.05186599 77.5138915,6.00484257 85.7658652,14.2386634 C94.0177423,22.4724842 93.9231041,34.0940152 85.7658652,42.2336928 L76.5236662,51.4555837 C77.8439666,54.4668173 78.1269157,57.9485742 77.655366,61.0068313 L91.5185196,47.2210665 C102.788199,35.9289887 102.881871,19.9787974 91.4712005,8.54555286 C80.0130176,-2.88769165 63.9809096,-2.7936448 52.6642012,8.49852944 L36.5849672,24.589791 C25.2681623,35.8818689 25.1739103,51.8790836 36.6320932,63.2653047 C39.6027689,66.2295149 43.3749729,68.3467253 49.4105764,69.3819151 Z M50.5894022,30.6122582 L44.3180721,36.9640822 C50.5422762,37.4816289 54.5974292,39.3166024 57.6624535,42.3748594 C65.9142341,50.6086803 65.8671081,62.2772346 57.7095795,70.4170086 L42.2904956,85.7554521 C34.085841,93.9422495 22.4861836,93.9893308 14.2344031,85.8025045 C5.98252593,77.521612 6.02974849,65.9471816 14.2344031,57.7603842 L23.4764089,48.5384933 C22.1561085,45.5742831 21.8260335,42.0455027 22.3447091,38.9872457 L8.48165207,52.7730105 C-2.7879206,64.0651847 -2.88223053,80.0623416 8.52887463,91.4485723 C19.9870575,102.881865 36.0191656,102.787722 47.2887479,91.5426673 L63.4151079,75.4043438 C74.7318163,64.1122081 74.8261648,48.1149934 63.367982,36.7287723 C60.3973062,33.7646585 56.6250057,31.6473517 50.5894022,30.6122582 Z"/>
        </svg>
    ';
}
