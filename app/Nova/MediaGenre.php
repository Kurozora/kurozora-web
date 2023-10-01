<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Validator;

class MediaGenre extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MediaGenre::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\MediaGenre|null
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
        'id',
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
                ->onlyOnDetail(),

            ID::make()->sortable(),

            Heading::make('Meta information'),

            MorphTo::make('Model')
                ->types([
                    Anime::class,
                    Game::class,
                    Manga::class,
                ])
                ->searchable(),

            Text::make('Model Type')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->sortable(),

            BelongsTo::make('Genre')
                ->sortable()
                ->required(),
        ];
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewMediaGenre');
    }

    /**
     * Handle any post-validation processing.
     *
     * @param NovaRequest $request
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     * @throws ValidationException
     */
    protected static function afterValidation(NovaRequest $request, $validator): void
    {
        $resourceID = $request->resourceId;
        $modelType = Nova::modelInstanceForKey($request->post('model_type'))->getMorphClass();
        $model = $request->post('model');
        $genre = $request->post('genre');

        $unique = Rule::unique(\App\Models\MediaGenre::TABLE_NAME, 'genre_id')->where(function ($query) use($request, $resourceID, $modelType, $model, $genre) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            $query->where([
                ['model_type', '=', $modelType],
                ['model_id', '=', $model],
                ['genre_id', '=', $genre]
            ]);
        });

        $uniqueValidator = Validator::make($request->only('genre'), [
            'genre' => [$unique],
        ], [
            'genre' => __('validation.unique')
        ]);

        $uniqueValidator->validate();
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
            <path fill="var(--sidebar-icon)" d="M88.3568982,86.0440316 C89.2542364,86.0440316 89.9906148,86.7203118 90.0656427,87.5818258 L90.0719379,87.7270424 L90.0719379,89.4100533 C90.0719379,90.2906332 89.3827878,91.0132597 88.5048784,91.0868864 L88.3568982,91.093064 L43.7658693,91.093064 C42.8685311,91.093064 42.1321527,90.4167839 42.0571249,89.5552699 L42.0508296,89.4100533 L42.0508296,87.7270424 C42.0508296,86.8464625 42.7399798,86.123836 43.6178891,86.0502093 L43.7658693,86.0440316 L88.3568982,86.0440316 Z M94.8695354,36.6356446 C97.6309591,36.6356446 99.8829417,38.8112143 99.9966125,41.5376703 L100.001114,41.7540318 L100.001114,75.8766123 C100.001114,78.6309372 97.8199375,80.8771306 95.0864545,80.9905092 L94.8695354,80.9949994 L36.7116407,80.9949994 C33.950217,80.9949994 31.6982343,78.8194297 31.5845636,76.0929737 L31.5800617,75.8766123 L31.5800617,41.7540318 C31.5800617,38.9997069 33.7612386,36.7535135 36.4947216,36.6401349 L36.7116407,36.6356446 L94.8695354,36.6356446 Z M93.1092764,42.6994248 L38.8329606,42.6994248 L38.8329606,74.570574 L93.1092764,74.570574 L93.1092764,42.6994248 Z M51.2033853,8.04286421 C53.2680119,9.21142659 54.7530657,11.3621254 55.6585467,14.4949608 L55.778429,14.9282532 L61.544,36.635 L55.505,36.635 L50.2531612,16.7429899 C49.8543515,15.1536317 49.1505005,14.0564722 48.1416081,13.4515114 C47.2000201,12.8869266 46.0234087,12.7717968 44.6117741,13.1061221 L44.3056837,13.1846426 L9.60592662,22.5787583 C8.057488,23.0294667 7.00172348,23.7529954 6.43863305,24.7493443 C5.91308198,25.67927 5.81674664,26.8674842 6.14962705,28.3139868 L6.22752819,28.6279051 L12.7732923,53.2873221 C14.0636578,58.1978434 16.0461503,62.1713361 18.7207698,65.2078003 C21.3953893,68.2442887 24.6272372,70.254773 28.4163135,71.239253 C29.4412456,71.5055535 30.4957912,71.6907068 31.5799502,71.794713 L31.5800617,75.8766123 L31.5845636,76.0929737 C31.6099803,76.702609 31.7423061,77.2847016 31.9633312,77.8210888 C30.1400531,77.7425962 28.3828955,77.4877876 26.69187,77.0571669 C21.9408936,75.847323 17.8879062,73.3624021 14.5329078,69.6024044 C11.3069478,65.9870407 8.89441958,61.2914683 7.29532307,55.5156873 L7.10733597,54.8174085 L0.561499806,29.9089771 C-0.330007988,26.4929307 -0.15991326,23.7055294 1.07178399,21.5467734 C2.24997515,19.4819229 4.42095979,17.9922189 7.5847379,17.0776615 L8.02231587,16.9565141 L42.8980057,7.59803436 C46.2764521,6.67286016 49.044912,6.82113678 51.2033853,8.04286421 Z M31.58,53.97 L31.5807932,64.0284649 C30.5806934,63.8689436 29.6021211,63.6156286 28.6450764,63.2685199 C25.5545352,62.147634 23.0946302,60.1891919 21.2653617,57.3931935 L21.0083277,56.9880675 C20.5156295,56.2763689 20.4745714,55.635845 20.8851531,55.0664958 C21.2957349,54.4971952 21.9702518,54.271841 22.9087038,54.3904331 C25.8648445,54.6750834 28.5336019,54.5802 30.9149762,54.1057828 L31.58,53.97 Z M24.7210775,35.0685371 C26.1639998,35.8988278 27.0731417,37.1086766 27.4485033,38.6980834 C27.612736,39.3386073 27.62446,39.8842235 27.4836754,40.3349319 C27.3429388,40.7856404 27.0848657,41.0584363 26.7094561,41.1533198 C26.1698618,41.2956692 25.5481271,41.206713 24.8442521,40.886451 C24.1404251,40.5661891 23.1550529,40.5721162 21.8881354,40.9042325 C20.6446661,41.2363488 19.8000304,41.7226689 19.3542285,42.3631928 C18.9084746,43.0037167 18.4275245,43.4070078 17.9113783,43.5730659 C17.5359687,43.6679494 17.1723071,43.5552601 16.8203936,43.2349981 C16.4684801,42.9147362 16.198683,42.4224889 16.0110022,41.7582563 C15.5652002,40.1926068 15.7646051,38.6981077 16.6092167,37.2747591 C17.3975657,35.9463003 18.5026625,35.0621333 19.9245071,34.6222581 L20.2340363,34.5347995 C21.7825229,34.0603338 23.2782034,34.2382463 24.7210775,35.0685371 Z M45.5726011,29.3573852 C46.9083412,30.1212592 47.7842477,31.2209336 48.2003206,32.6564085 L48.2824408,32.96915 C48.4701216,33.6096253 48.4877076,34.1552415 48.335199,34.6059986 C48.1826903,35.056707 47.9187552,35.3413573 47.5433936,35.4599494 C47.0037513,35.5785902 46.3820166,35.4777796 45.6781896,35.1575176 C44.9743145,34.8372557 43.9889183,34.8431828 42.7220009,35.1752991 C41.4785315,35.5074155 40.6397819,35.9937356 40.2057521,36.6342595 L40.204,36.635 L37.0594957,36.6372142 C36.9788589,36.4529688 36.9073162,36.2503384 36.8448676,36.0293229 C36.3991137,34.4873821 36.5985426,32.9988102 37.4431542,31.5636072 C38.2877658,30.1284042 39.5077631,29.2210115 41.1031459,28.8414292 C42.6516326,28.3669635 44.141451,28.5389488 45.5726011,29.3573852 Z"/>
        </svg>
    ';
}
