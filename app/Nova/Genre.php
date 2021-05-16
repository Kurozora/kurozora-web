<?php

namespace App\Nova;

use Chaseconey\ExternalImage\ExternalImage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Timothyasp\Color\Color;

class Genre extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Genre::class;

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
        'id', 'name'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Genre';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            ExternalImage::make('Symbol image URL', 'symbol')
                ->width(100)
                ->rules('max:255'),

            Text::make('Name')
                ->rules('required')
                ->sortable(),

            Color::make('Color')
                ->rules('required'),

            Boolean::make('Is NSFW')
                ->rules('required')
                ->sortable(),

            BelongsToMany::make('Animes')
                ->searchable(),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $genreName = $this->name;

        if (!is_string($genreName) || !strlen($genreName)) {
            $genreName = 'No genre title';
        }

        return $genreName . ' (ID: ' . $this->id . ')';
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
            <path fill="var(--sidebar-icon)" d="M32.3216889,48.1977747 C26.7155362,49.8352463 22.9546639,54.6507877 23.3327824,59.8053858 C25.1140015,57.8116704 27.8342668,56.0929502 31.1295221,54.9617198 L32.3216889,48.1977747 Z M94.8112296,28.7356124 C87.8597877,24.8669295 80.3817925,22.2279128 72.6788011,20.8701239 C64.9758098,19.5123349 57.0462599,19.4342113 49.1917087,20.692002 C44.9245952,21.3748026 41.5230917,24.8044306 40.7481052,29.2059167 L35.7903787,57.3241788 C33.3857329,70.9598797 50.6776204,88.0673954 60.525887,89.8048652 C70.3741536,91.5407726 92.4737702,81.3784489 94.8784159,67.742748 L99.8361424,39.624486 C100.611129,35.2229998 98.5877266,30.8371384 94.8112296,28.7356124 L94.8112296,28.7356124 Z M87.4894816,66.4412081 C85.8926343,75.4957385 68.0835684,83.5221617 61.8258645,82.4190559 C55.5665981,81.31595 41.5777783,67.6818116 43.1730631,58.6272812 L48.1307896,30.5090191 C48.3495358,29.2652907 49.2729573,28.2746829 50.3745007,28.0981235 C57.3665668,26.9793929 64.4305068,27.032517 71.3741363,28.2574957 C78.3177658,29.480912 84.9754628,31.8480584 91.1612929,35.2901862 C92.136276,35.8323643 92.6643918,37.0807801 92.4456456,38.3245085 L87.4894816,66.4412081 Z M12.5079702,56.508568 L7.55024375,28.4715545 C7.33149754,27.2325135 7.8580509,25.9887851 8.83459646,25.446607 C18.2297459,20.2326349 28.8623739,17.4779952 39.5825004,17.4779952 C40.90279,17.4779952 42.2293295,17.5529939 43.5543065,17.635805 C45.0417807,16.8139443 46.66519,16.2170796 48.4026599,15.9389594 C51.3541712,15.4686551 54.3463067,15.2295968 57.3368798,15.1077239 C55.7759694,12.9280742 53.5135087,11.3234145 50.8104306,10.8906095 C47.0729954,10.2953074 43.3183731,10 39.5809379,10 C27.6030208,10 15.7844759,13.0327599 5.18778474,18.9123454 C1.41128777,21.0076215 -0.612114621,25.3809831 0.164434405,29.7699695 L5.12216088,57.8085454 C7.34712227,70.3833272 26.4233538,79.9987854 37.0669191,79.9987854 C37.6434716,79.9987854 38.1528377,79.9269116 38.6747037,79.8706626 C36.7669243,77.4394548 35.0638288,74.8488747 33.7372893,72.1551715 C25.4749327,70.514575 13.759511,63.5850077 12.5079702,56.508568 Z M30.2123505,34.4761378 C30.09829,33.8308365 29.8639191,33.2433467 29.576424,32.6917938 C28.2873839,34.0886445 26.3467926,35.1589384 24.0577698,35.5620564 C21.768747,35.9651745 19.57816,35.6245554 17.8891268,34.7526955 C17.8078783,35.3698723 17.7891286,36.0026738 17.9031891,36.6479751 C18.5031787,40.0479161 21.7437475,42.3166267 25.1436885,41.7181996 C28.5436295,41.1197725 30.8123401,37.8745163 30.2123505,34.4761378 L30.2123505,34.4761378 Z M79.0536905,42.3025645 C75.655312,41.7025749 72.4131807,43.972848 71.8131912,47.372789 C71.6991306,48.0180903 71.7178803,48.6508918 71.7991289,49.2680686 C73.4881621,48.3962087 75.6787491,48.0540272 77.9677719,48.4587077 C80.2567946,48.8618257 82.1989484,49.9321196 83.4879886,51.3289704 C83.7754836,50.7774174 84.0098545,50.1899276 84.123915,49.5430638 C84.7223422,46.1431228 82.4536315,42.9009916 79.0536905,42.3025645 L79.0536905,42.3025645 Z M58.2728011,44.983768 C60.5618239,45.386886 62.5039777,46.4571799 63.7914553,47.8540307 C64.0789504,47.3024777 64.3133213,46.7149879 64.4273818,46.0681241 C65.0273714,42.6681831 62.7570983,39.4276144 59.3571573,38.8276248 C55.9587788,38.2276352 52.7166475,40.4979083 52.1166579,43.8978493 C52.0025974,44.5431506 52.0213471,45.1759521 52.1025957,45.7931289 C53.7931913,44.9228315 55.9837783,44.58065 58.2728011,44.983768 Z M65.1961185,63.3115749 C58.4337358,62.1194081 52.8697699,58.8866517 49.6510757,54.8867211 C48.9510879,63.2881378 54.8650477,71.0364409 63.5633343,72.5707893 C72.2631833,74.1051376 80.4708534,68.8458539 82.6848775,60.7116201 C78.2927662,63.3693864 71.9585011,64.5037418 65.1961185,63.3115749 L65.1961185,63.3115749 Z"/>
        </svg>
    ';
}
