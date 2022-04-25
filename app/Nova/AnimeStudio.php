<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Validator;

class AnimeStudio extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\AnimeStudio::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\AnimeStudio|null
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
    public static $group = 'Anime';

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

            BelongsTo::make('Anime')
                ->sortable()
                ->searchable()
                ->required(),

            BelongsTo::make('Studio')
                ->searchable()
                ->sortable(),

            Boolean::make('Is Licensor')
                ->sortable()
                ->help('The studio is responsible for licensing the anime.')
                ->required(),

            Boolean::make('Is Producer')
                ->sortable()
                ->help('The studio is responsible for producing the anime. Usually sponsors.')
                ->required(),

            Boolean::make('Is Studio')
                ->sortable()
                ->help('The studio responsible for creating (drawing) the anime.')
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
        return $request->user()->can('viewAnimeStudio');
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
        $anime = $request->post('anime');
        $studio = $request->post('studio');

        $unique = Rule::unique(\App\Models\AnimeStudio::TABLE_NAME, 'studio_id')->where(function ($query) use($resourceID, $anime, $studio) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query->where([
                ['anime_id', $anime],
                ['studio_id', $studio]
            ]);
        });

        $uniqueValidator = Validator::make($request->only('studio'), [
            'studio' => [$unique],
        ], [
            'studio' => __('validation.unique')
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
            <path fill="var(--sidebar-icon)" d="M24.3421053,38.5425806 L17.7631579,38.5425806 C16.6731222,38.5425806 15.7894737,39.4252115 15.7894737,40.5139919 L15.7894737,47.0853629 C15.7894737,48.1741433 16.6731222,49.0567742 17.7631579,49.0567742 L24.3421053,49.0567742 C25.432141,49.0567742 26.3157895,48.1741433 26.3157895,47.0853629 L26.3157895,40.5139919 C26.3157895,39.4252115 25.432141,38.5425806 24.3421053,38.5425806 Z M24.3421053,54.3138709 L17.7631579,54.3138709 C16.6731222,54.3138709 15.7894737,55.1965018 15.7894737,56.2852822 L15.7894737,62.8566532 C15.7894737,63.9454336 16.6731222,64.8280645 17.7631579,64.8280645 L24.3421053,64.8280645 C25.432141,64.8280645 26.3157895,63.9454336 26.3157895,62.8566532 L26.3157895,56.2852822 C26.3157895,55.1965018 25.432141,54.3138709 24.3421053,54.3138709 Z M24.3421053,22.7712903 L17.7631579,22.7712903 C16.6731222,22.7712903 15.7894737,23.6539212 15.7894737,24.7427016 L15.7894737,31.3140726 C15.7894737,32.402853 16.6731222,33.2854839 17.7631579,33.2854839 L24.3421053,33.2854839 C25.432141,33.2854839 26.3157895,32.402853 26.3157895,31.3140726 L26.3157895,24.7427016 C26.3157895,23.6539212 25.432141,22.7712903 24.3421053,22.7712903 Z M50,33.3056253 L57.8947368,33.3056253 L57.8947368,12.2570968 C57.8947368,9.35368239 55.5383408,7 52.6315789,7 L5.26315789,7 C2.35639605,7 0,9.35368239 0,12.2570968 L0,77.9708064 C0,79.4225136 1.17819803,80.5993548 2.63157895,80.5993548 L5.26315789,80.5993548 C6.71653882,80.5993548 7.89473684,79.4225136 7.89473684,77.9708064 L7.89473684,14.8856452 L50,14.8856452 L50,33.3056253 Z M88.3557839,87.9509676 C89.3029742,87.9509676 90.0708235,88.7044773 90.0708235,89.6339784 L90.0708235,89.6339784 L90.0708235,91.3169893 C90.0708235,92.2464903 89.3029742,93 88.3557839,93 L88.3557839,93 L43.7647549,93 C42.8175646,93 42.0497153,92.2464903 42.0497153,91.3169893 L42.0497153,91.3169893 L42.0497153,89.6339784 C42.0497153,88.7044773 42.8175646,87.9509676 43.7647549,87.9509676 L43.7647549,87.9509676 L88.3557839,87.9509676 Z M94.8684211,38.5425806 C97.7025138,38.5425806 100,40.8341606 100,43.6609677 L100,43.6609677 L100,77.7835483 C100,80.6103554 97.7025138,82.9019354 94.8684211,82.9019354 L94.8684211,82.9019354 L36.7105263,82.9019354 C33.8764336,82.9019354 31.5789474,80.6103554 31.5789474,77.7835483 L31.5789474,77.7835483 L31.5789474,43.6609677 C31.5789474,40.8341606 33.8764336,38.5425806 36.7105263,38.5425806 L36.7105263,38.5425806 L94.8684211,38.5425806 Z M93.108162,44.6063608 L38.8318462,44.6063608 L38.8318462,76.47751 L93.108162,76.47751 L93.108162,44.6063608 Z M40.1315789,22.7712903 C41.2216146,22.7712903 42.1052632,23.6539212 42.1052632,24.7427016 L42.1052632,24.7427016 L42.1052632,31.3140726 C42.1052632,32.402853 41.2216146,33.2854839 40.1315789,33.2854839 L40.1315789,33.2854839 L33.5526316,33.2854839 C32.4625959,33.2854839 31.5789474,32.402853 31.5789474,31.3140726 L31.5789474,31.3140726 L31.5789474,24.7427016 C31.5789474,23.6539212 32.4625959,22.7712903 33.5526316,22.7712903 L33.5526316,22.7712903 L40.1315789,22.7712903 Z"/>
        </svg>
    ';
}
