<?php

namespace App\Nova;

use App\Enums\StoreProductType;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class StoreProduct extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\StoreProduct::class;

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
    public static $group = 'Store';

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

            Select::make('Type')
                ->options(StoreProductType::asSelectArray())
                ->displayUsingLabels()
                ->required()
                ->help('The type of the purchasable product.'),

            Text::make('Title')
                ->required()
                ->help('The name of the purchasable product.'),

            Text::make('Identifier')
                ->required()
                ->help('The identifier of the purchasable product as found on the developer page.'),
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
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M22.3619989,100 L79.9451471,100 C88.7024483,100 93.83948,94.9119442 93.83948,84.8336733 L93.83948,34.1976813 C93.83948,24.1194004 88.6535529,19.0313645 78.4774812,19.0313645 L70.649695,19.0313645 C70.4050175,8.75740146 61.4520337,0 50.3952421,0 C39.3385507,0 30.3854671,8.75740146 30.1408895,19.0313645 L22.3619989,19.0313645 C12.1369314,19.0313645 7,24.0705049 7,34.1976813 L7,84.8336733 C7,94.9608696 12.1369314,100 22.3619989,100 Z M50.3952421,7.43642183 C57.2935133,7.43642183 62.5283358,12.6223489 62.7729133,19.0313645 L38.0175711,19.0313645 C38.2621487,12.6223489 43.4970717,7.43642183 50.3952421,7.43642183 Z M22.4598901,92.1232983 C17.5674361,92.1232983 14.8766816,89.5303408 14.8766816,84.4422787 L14.8766816,34.5890455 C14.8766816,29.5010096 17.5674361,26.908046 22.4598901,26.908046 L78.3306946,26.908046 C83.1741529,26.908046 85.9627987,29.5010096 85.9627987,34.5890455 L85.9627987,84.4422787 C85.9627987,89.5303408 83.1741529,92.1232983 79.7983608,92.1232983 L22.4598901,92.1232983 Z"/>
        </svg>
    ';
}
