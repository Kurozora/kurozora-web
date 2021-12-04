<?php

namespace App\Nova;

use App\Scopes\TvRatingScope;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ramsey\Uuid\Uuid;
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
     * The underlying model resource instance.
     *
     * @var \App\Models\Genre|null
     */
    public $resource;

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
        'id', 'name', 'slug'
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
            Heading::make('Identification')
                ->exceptOnForms(),

            ID::make()->sortable(),

            Heading::make('Media'),

            Images::make('Symbol')
                ->showStatistics()
                ->setFileName(function($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function($originalFilename, $model) {
                    return $this->resource->name;
                })
                ->customPropertiesFields([
                    Heading::make('Colors (automatically generated if empty)'),

                    Color::make('Background Color')
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

                    Heading::make('Dimensions (automatically generated if empty)'),

                    Number::make('Width')
                        ->help('The maximum width available for the image.'),

                    Number::make('Height')
                        ->help('The maximum height available for the image.'),
                ]),

            Heading::make('Meta information'),

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the genre in a URL: https://kurozora.app/genre/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from name.'),

            Text::make('Name')
                ->rules('required')
                ->sortable(),

            Color::make('Color')
                ->rules('required'),

            Boolean::make('Is NSFW')
                ->rules('required')
                ->sortable(),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->help('The TV rating of the genre. For example NR, G, PG-12, etc.')
                ->required(),

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
        $genre = $this->resource;
        $genreName = $genre->name;

        if (!is_string($genreName) || !strlen($genreName)) {
            $genreName = 'No genre title';
        }

        return $genreName . ' (ID: ' . $genre->id . ')';
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
     * Build an "index" query for the given resource.
     *
     * @param NovaRequest $request
     * @param  Builder  $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return parent::indexQuery($request, $query)->withoutGlobalScope(new TvRatingScope);
    }

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M42.8980057,10.5980344 C46.2764521,9.67286016 49.044912,9.82113678 51.2033853,11.0428642 C53.2680119,12.2114266 54.7530657,14.3621254 55.6585467,17.4949608 L55.778429,17.9282532 L62.3945373,42.8367575 C64.0136085,48.9571518 64.3010878,54.4903912 63.2569753,59.4364755 C62.2128146,64.3826085 59.9428428,68.5696132 56.4470598,71.9974898 C52.9512768,75.4253707 48.3058648,77.9340099 42.5108239,79.5234075 C36.7158311,81.0890862 31.4428464,81.267006 26.69187,80.0571669 C21.9408936,78.847323 17.8879062,76.3624021 14.5329078,72.6024044 C11.3069478,68.9870407 8.89441958,64.2914683 7.29532307,58.5156873 L7.10733597,57.8174085 L0.561499806,32.9089771 C-0.330007988,29.4929307 -0.15991326,26.7055294 1.07178399,24.5467734 C2.24997515,22.4819229 4.42095979,20.9922189 7.5847379,20.0776615 L8.02231587,19.9565141 L42.8980057,10.5980344 Z M48.1416081,16.4515114 C47.2000201,15.8869266 46.0234087,15.7717968 44.6117741,16.1061221 L44.3056837,16.1846426 L9.60592662,25.5787583 C8.057488,26.0294667 7.00172348,26.7529954 6.43863305,27.7493443 C5.91308198,28.67927 5.81674664,29.8674842 6.14962705,31.3139868 L6.22752819,31.6279051 L12.7732923,56.2873221 C14.0636578,61.1978434 16.0461503,65.1713361 18.7207698,68.2078003 C21.3953893,71.2442887 24.6272372,73.254773 28.4163135,74.239253 C32.2053417,75.2237281 36.3990897,75.0991845 40.9975574,73.8656221 C45.6195213,72.6083365 49.3205952,70.6038037 52.1007791,67.8520238 C54.8809631,65.10021 56.6816368,61.7375567 57.5028003,57.7640639 C58.2882611,53.9633318 58.0970185,49.7230719 56.9290725,45.0432842 L56.7637531,44.402407 L50.2531612,19.7429899 C49.8543515,18.1536317 49.1505005,17.0564722 48.1416081,16.4515114 Z M48.5287898,50.4516266 C49.2795611,49.8585687 49.971664,49.7043649 50.6050987,49.9890152 C51.2385815,50.2737141 51.5435748,50.8667963 51.5200786,51.7682618 C51.4027901,55.3028518 50.2297131,58.3926849 48.0008475,61.037761 C45.77203,63.6827885 42.8041412,65.5272097 39.0971813,66.5710246 C35.3667733,67.5436163 31.8827383,67.4427814 28.6450764,66.2685199 C25.5545352,65.147634 23.0946302,63.1891919 21.2653617,60.3931935 L21.0083277,59.9880675 C20.5156295,59.2763689 20.4745714,58.635845 20.8851531,58.0664958 C21.2957349,57.4971952 21.9702518,57.271841 22.9087038,57.3904331 C25.8648445,57.6750834 28.5336019,57.5802 30.9149762,57.1057828 L32.2726867,56.8296443 L33.4882269,56.5705878 L34.2196035,56.4073734 L35.198193,56.1767867 L35.7716026,56.0325522 L36.2818252,55.8959097 L36.7895276,55.7507282 L37.6601771,55.4866996 L38.661742,55.1671635 L39.7942221,54.7921199 L40.6219396,54.5112532 L41.5078416,54.2057166 C43.7953755,53.4109889 46.1356915,52.1596256 48.5287898,50.4516266 Z M19.9245071,37.6222581 L20.2340363,37.5347995 C21.7825229,37.0603338 23.2782034,37.2382463 24.7210775,38.0685371 C26.1639998,38.8988278 27.0731417,40.1086766 27.4485033,41.6980834 C27.612736,42.3386073 27.62446,42.8842235 27.4836754,43.3349319 C27.3429388,43.7856404 27.0848657,44.0584363 26.7094561,44.1533198 C26.2298167,44.2798526 25.6852763,44.2236254 25.0758349,43.9846381 L24.8442521,43.886451 C24.1404251,43.5661891 23.1550529,43.5721162 21.8881354,43.9042325 C20.7334853,44.2126263 19.9227275,44.6539814 19.4558621,45.228298 L19.3542285,45.3631928 C18.9084746,46.0037167 18.4275245,46.4070078 17.9113783,46.5730659 C17.5359687,46.6679494 17.1723071,46.5552601 16.8203936,46.2349981 C16.4684801,45.9147362 16.198683,45.4224889 16.0110022,44.7582563 C15.5652002,43.1926068 15.7646051,41.6981077 16.6092167,40.2747591 C17.3975657,38.9463003 18.5026625,38.0621333 19.9245071,37.6222581 Z M41.1031459,31.8414292 C42.6516326,31.3669635 44.141451,31.5389488 45.5726011,32.3573852 C46.9083412,33.1212592 47.7842477,34.2209336 48.2003206,35.6564085 L48.2824408,35.96915 C48.4701216,36.6096253 48.4877076,37.1552415 48.335199,37.6059986 C48.1826903,38.056707 47.9187552,38.3413573 47.5433936,38.4599494 C47.0637115,38.5654079 46.5191664,38.4974726 45.9097581,38.2561438 L45.6781896,38.1575176 C44.9743145,37.8372557 43.9889183,37.8431828 42.7220009,38.1752991 C41.5673508,38.4836929 40.7616682,38.925048 40.3049531,39.4993646 L40.2057521,39.6342595 C39.7717222,40.2747348 39.2848861,40.6780015 38.7452438,40.8440597 C38.3698822,40.9627004 38.0062446,40.8559626 37.6543311,40.5238463 C37.3023696,40.19173 37.0325484,39.6935555 36.8448676,39.0293229 C36.3991137,37.4873821 36.5985426,35.9988102 37.4431542,34.5636072 C38.2877658,33.1284042 39.5077631,32.2210115 41.1031459,31.8414292 Z M56.6355897,19.5958984 L57.0727181,19.7074269 L91.9133078,29.1371056 C95.3152024,30.0622798 97.6439146,31.6101478 98.8994443,33.7807096 C100.099926,35.8569455 100.318297,38.4812132 99.5545578,41.6535126 L99.444324,42.0894958 L92.8286481,66.962437 C91.2098652,73.0590712 88.7288366,77.9873861 85.3855623,81.7473819 C82.0422879,85.5073747 78.0008564,87.9922834 73.2612677,89.2021079 C68.5221595,90.411981 63.2667609,90.2459228 57.495072,88.7039335 C52.2634578,87.2568761 47.9349314,85.0388459 44.5094926,82.0498429 C43.6366602,81.2882251 42.831616,80.4827118 42.0943601,79.6333028 L42.5108239,79.5234075 C44.6154556,78.9461732 46.5684533,78.2476924 48.3698168,77.4279653 C51.101444,79.9735746 54.6246647,81.8462905 58.9379942,83.0461845 C63.5834062,84.2797274 67.7949324,84.4102006 71.572573,83.4376042 C75.349733,82.4649834 78.5755267,80.4663725 81.249954,77.4417714 C83.8085614,74.5486719 85.7336767,70.7819684 87.0252997,66.1416608 L87.1975036,65.5034767 L93.7432676,40.8796227 C94.1656215,39.2665072 94.0952292,37.9499206 93.5320907,36.929863 C93.0069433,35.9778092 92.0523499,35.2840651 90.6683105,34.8486306 L90.3651575,34.759277 L57.925,26.009 L56.1938198,19.4939224 L56.6355897,19.5958984 L56.6355897,19.5958984 Z M64.4285944,61.8383673 C67.8069448,62.7161241 70.5517644,64.3588998 72.6630532,66.7666944 C74.7748225,69.174523 75.8893273,71.9441136 76.0065678,75.075466 C76.0536561,76.0243601 75.7252388,76.6589333 75.0213157,76.9791855 C74.3173926,77.2994373 73.6134695,77.1393113 72.9095464,76.4988072 C71.0798268,74.8382388 69.3260252,73.5275786 67.6481416,72.5668268 C65.9707385,71.6060701 64.1583167,70.8647453 62.2108762,70.3428524 C61.0010505,70.0038108 59.7865865,69.7678185 58.5674841,69.6348755 C60.4668128,67.2221044 61.8608445,64.4958928 62.7499336,61.4569771 C63.3039016,61.5562015 63.863703,61.6836916 64.4285944,61.8383673 Z M79.5612593,47.0711675 C80.8749287,47.4270411 81.8719529,48.1980358 82.5523318,49.3841516 C83.2327108,50.5702674 83.3970396,51.8275579 83.0453183,53.1560231 C82.6931165,54.4844397 81.9305731,55.4926187 80.7576883,56.18056 C79.584323,56.8685499 78.3408056,57.0464867 77.0271361,56.7143704 C75.7134667,56.3347881 74.7164425,55.5460119 74.0360636,54.3480418 C73.3556847,53.1500716 73.2031279,51.8868539 73.5783934,50.5583887 C73.9065705,49.2299235 74.6573418,48.2276474 75.8307071,47.5515604 C77.0035919,46.8754735 78.2471093,46.7153425 79.5612593,47.0711675 Z"/>
        </svg>
    ';
}
