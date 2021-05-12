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
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M49.9999699,100 C77.3529105,100 100.000371,77.3039091 100.000371,50 C100.000371,22.6470594 77.3039191,0 49.9509785,0 C22.6470293,0 0,22.6470594 0,50 C0,77.3039091 22.696121,100 49.9999699,100 Z M49.9999699,91.6666499 C26.8626957,91.6666499 8.38231131,73.1372742 8.38231131,50 C8.38231131,26.8627258 26.8137043,8.33335007 49.9509785,8.33335007 C73.0882527,8.33335007 91.6176285,26.8627258 91.6669181,50 C91.7160127,73.1372742 73.1372441,91.6666499 49.9999699,91.6666499 Z M20.5797348,62 C22.076889,62 22.8945736,61.3134297 23.3091708,59.7572115 L24.3456876,56.6218862 L32.1770195,56.6218862 L33.2250227,59.7572115 C33.63962,61.3134297 34.4573045,62 35.9544824,62 C37.5898514,62 38.6493175,61.0044742 38.6493175,59.5054698 C38.6493175,58.9104439 38.5573316,58.4298461 38.3500093,57.8348216 L32.6837442,42.0323268 C31.9812309,39.9955117 30.5992242,39 28.3074055,39 C26.142268,39 24.771795,40.0069479 24.0692816,42.0208906 L18.2879161,58.0407927 C18.1036375,58.6014884 18,59.1049708 18,59.5741245 C18,61.061688 18.9904295,62 20.5797348,62 Z M73.1994844,61.9542318 C77.4261218,61.9542318 80.7312486,60.1233807 82.2516116,57.4457659 C82.6660202,56.7248706 82.8964568,55.9353229 82.8964568,55.1572161 C82.8964568,53.8412799 81.8945173,52.8915365 80.512605,52.8915365 C79.3493362,52.8915365 78.6929338,53.2920378 77.9787453,54.5164141 C76.9539272,56.5646724 75.3875712,57.6059664 73.2108057,57.6059664 C69.8825646,57.6059664 67.7980209,54.9283516 67.7980209,50.4656563 C67.7980209,46.0487526 69.8941218,43.3940312 73.1994844,43.3940312 C75.422007,43.3940312 76.988363,44.5955117 78.1631889,46.8382956 C78.8311486,47.9482396 79.4644366,48.3373047 80.6392626,48.3373047 C82.0442893,48.3373047 83,47.4447656 83,46.1288529 C83,45.499487 82.8617852,44.8587083 82.5740342,44.2293424 C81.2725506,41.1626836 77.6103296,39.0343086 73.1994844,39.0343086 C66.289451,39.0343086 62.1088065,43.3368034 62.1088065,50.4885521 C62.1088065,57.651737 66.289451,61.9542318 73.1994844,61.9542318 Z M44.0736595,61.5880625 L52.1469617,61.5880625 C57.0875518,61.5880625 60.2200279,59.0592003 60.2200279,55.271625 C60.2200279,52.2736302 57.7095185,50.0422826 54.5539279,49.882082 L54.5539279,49.7791094 C57.1797736,49.4243763 59.1376596,47.4333294 59.1376596,44.8701445 C59.1376596,41.5631849 56.3851563,39.4348099 51.8589748,39.4348099 L44.0736595,39.4348099 C42.3808818,39.4348099 41.2867205,40.4188854 41.2867205,42.1238633 L41.2867205,58.8990011 C41.2867205,60.6039776 42.3808818,61.5880625 44.0736595,61.5880625 Z M46.883713,48.520401 L46.883713,43.3024714 L50.3732834,43.3024714 C52.4349485,43.3024714 53.678646,44.2980065 53.678646,45.8084518 C53.678646,47.4790977 52.3311695,48.520401 50.1315254,48.520401 L46.883713,48.520401 Z M25.4282446,52.7084635 L28.203768,43.9089414 L28.3074055,43.9089414 L31.0829289,52.7084635 L25.4282446,52.7084635 Z M46.883713,57.7318372 L46.883713,51.8273607 L50.5114982,51.8273607 C53.0451221,51.8273607 54.5079349,52.9029727 54.5079349,54.8024831 C54.5079349,56.6562135 53.0797937,57.7318372 50.5577271,57.7318372 L46.883713,57.7318372 Z"/>
        </svg>
    ';
}
