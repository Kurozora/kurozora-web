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
    public static string $model = 'App\Models\StoreProduct';

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
        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path fill="var(--sidebar-icon)" d="M5.19807974,24 L19.0180353,24 C21.1197876,24 22.3526752,22.7788666 22.3526752,20.3600816 L22.3526752,8.2074435 C22.3526752,5.78865609 21.1080527,4.56752747 18.6657955,4.56752747 L16.7871268,4.56752747 C16.7284042,2.10177635 14.5796881,0 11.9260581,0 C9.27245217,0 7.12371211,2.10177635 7.06501349,4.56752747 L5.19807974,4.56752747 C2.74406354,4.56752747 1.5112,5.77692117 1.5112,8.2074435 L1.5112,20.3600816 C1.5112,22.7906087 2.74406354,24 5.19807974,24 Z M11.9260581,1.78474124 C13.5816432,1.78474124 14.8380006,3.02936374 14.8966992,4.56752747 L8.95541707,4.56752747 C9.01411569,3.02936374 10.2704972,1.78474124 11.9260581,1.78474124 Z M5.22157362,22.1095916 C4.04738466,22.1095916 3.40160358,21.4872818 3.40160358,20.2661469 L3.40160358,8.30137092 C3.40160358,7.0802423 4.04738466,6.45793105 5.22157362,6.45793105 L18.6305667,6.45793105 C19.7929967,6.45793105 20.4622717,7.0802423 20.4622717,8.30137092 L20.4622717,20.2661469 C20.4622717,21.4872818 19.7929967,22.1095916 18.9828066,22.1095916 L5.22157362,22.1095916 Z" id="Shape"/>
        </svg>
    ';
}
