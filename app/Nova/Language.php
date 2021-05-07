<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class Language extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Language::class;

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
        'name',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Localisation';

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

            Text::make('Name')
                ->sortable()
                ->help('The official name of the language according to ISO 639-1.')
                ->required(),

            Text::make('Code')
                ->sortable()
                ->help('The code of the language according to ISO 639-1.')
                ->rules(['string', 'alpha', 'size:2'])
                ->required(),
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
        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 263 25">
            <path fill="var(--sidebar-icon)" d="M222.573363,99.6630738 C239.757788,99.6630738 253.23401,92.2506798 259.837566,79.3801219 C261.185878,76.8194328 261.99459,74.1240504 261.99459,71.6981925 C261.99459,68.3962766 259.636078,66.1724585 256.33361,66.1724585 C253.638366,66.1724585 252.156189,67.2506942 250.606389,70.6199567 C246.024611,82.0755043 236.320075,88.4097012 222.573363,88.4097012 C202.964184,88.4097012 190.430538,73.5848635 190.430538,49.5956706 C190.430538,26.0108333 202.964184,11.2533698 222.573363,11.2533698 C235.511364,11.2533698 245.956989,18.800606 250.337278,30.4581935 C251.887078,33.8274559 253.705989,35.2425627 256.604101,35.2425627 C259.972811,35.2425627 262.196077,33.0188826 262.196077,29.7169668 C262.196077,27.9649889 261.859344,26.0108333 261.050633,23.9218464 C255.727767,9.09703628 240.296009,0 222.573363,0 C195.147561,0 177.695405,19.0701305 177.695405,49.6631552 C177.695405,80.5255663 195.013696,99.6630738 222.573363,99.6630738 Z M5.66028919,100 C9.23172949,100 10.9838453,98.4501365 12.2641209,94.7439395 L20.9568015,70.8894812 L60.7142091,70.8894812 L69.4068897,94.7439395 C70.6873033,98.4501365 72.4392812,100 75.9433749,100 C79.5148152,100 81.8058419,97.6415107 81.8058419,94.2722372 C81.8058419,93.1266825 81.6036641,92.1159037 81.0646152,90.6334227 L49.4608393,6.53648512 C47.9110393,2.35851133 45.1481723,0.0674846133 40.8355053,0.0674846133 C36.6576695,0.0674846133 33.8274559,2.35851133 32.3450025,6.46900051 L0.741226704,90.7008107 C0.202177829,92.1832917 0,93.1940705 0,94.3396252 C0,97.708896 2.1563335,100 5.66028919,100 Z M100.067261,98.0458236 L132.816067,98.0458236 C154.177914,98.0458236 167.048293,87.5336985 167.048293,71.5633612 C167.048293,58.153658 157.345137,48.7870973 143.530802,47.2371592 L143.530802,46.765457 C154.177914,45.0134791 161.994537,35.714265 161.994537,24.8652509 C161.994537,11.3208544 150.20198,1.61728463 132.479334,1.61728463 L100.067261,1.61728463 C96.2937809,1.61728463 93.867923,4.04314257 93.867923,7.95145392 L93.867923,91.7115895 C93.867923,95.552568 96.2937809,98.0458236 100.067261,98.0458236 Z M106.603746,43.3288479 L106.603746,12.3989521 L129.582602,12.3989521 C141.91338,12.3989521 149.325647,18.1267259 149.325647,26.6847133 C149.325647,37.1968564 141.037046,43.3288479 126.819735,43.3288479 L106.603746,43.3288479 Z M24.2587174,60.7143471 L40.6333275,15.363859 L40.9703366,15.363859 L57.3449466,60.7143471 L24.2587174,60.7143471 Z M106.603746,87.3315483 L106.603746,53.3692887 L129.312112,53.3692887 C145.349713,53.3692887 154.042669,59.5014182 154.042669,70.8221345 C154.042669,81.0647532 145.620203,87.3315483 130.457556,87.3315483 L106.603746,87.3315483 Z"/>
        </svg>
    ';
}
