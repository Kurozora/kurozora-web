<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
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
            ID::make(__('ID'), 'id')->sortable(),

            Select::make('Type')
                ->options([
                    'anime' => 'anime',
                    'manga' => 'manga'
                ])
                ->sortable()
                ->required(),

            BelongsTo::make('Genre')
                ->sortable()
                ->required(),

            BelongsTo::make('Media', 'anime', Anime::class)
                ->sortable()
                ->searchable()
                ->required(),
        ];
    }

    /**
     * Handle any post-validation processing.
     *
     * @param NovaRequest $request
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     * @throws ValidationException
     */
    protected static function afterValidation(NovaRequest $request, $validator)
    {
        $anime = $request->post('anime');
        $genre = $request->post('genre');

        $unique = Rule::unique(\App\Models\MediaGenre::TABLE_NAME, 'genre_id')->where(function ($query) use($anime, $genre) {
            return $query->where('media_id', $anime)->where('genre_id', $genre);
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
            <path fill="var(--sidebar-icon)" d="M50.4388433,8.16403644 L51.000033,8.20195477 C54.6139062,8.46202189 57.6018131,11.1184463 58.2831074,14.6770348 L58.947413,18.1834879 L59.554115,21.4622649 L59.9713393,23.7719352 L60.3561616,25.9535378 L61.0499772,29.9932634 L53.2283166,29.9946632 L50.522651,17.9185751 C50.2169681,16.5467752 48.9999094,15.5710593 47.5944639,15.5710593 L39.5825004,15.5710593 C28.8623739,15.5710593 18.2297459,18.325699 8.83459646,23.539671 C7.90944804,24.0533134 7.38817073,25.1966021 7.52185544,26.3690445 L7.55024375,26.5646185 L12.5079702,54.601632 C13.3082192,59.1263856 18.3865889,63.5910909 24.1048982,66.6514958 L24.8227567,67.0266351 C25.5434219,67.3942658 26.2717175,67.7390792 27.0005377,68.0584498 L27,75.8766123 L27.0043703,76.0864592 C17.4152654,72.6818149 7.02976446,65.2584631 5.1936913,56.276743 L5.12216088,55.9016095 L0.164434405,27.8630335 C-0.612114621,23.4740471 1.41128777,19.1006855 5.18778474,17.0054094 C15.7844759,11.1258239 27.5734377,8.50507153 39.5809379,8.09306403 C40.3700788,8.06598662 41.1384968,8.04481794 41.886192,8.02955797 L43.353952,8.00691635 C45.9941894,7.97985023 48.3558198,8.0322236 50.4388433,8.16403644 Z M26.9999807,45.7485613 L26.9998513,52.0997052 C23.7629015,53.2312544 21.0905835,54.9309461 19.3327824,56.8984498 C18.985219,52.1603849 22.1347458,47.7087936 26.9999807,45.7485613 Z M27.2123505,31.5692018 C27.09829,30.9239005 26.8639191,30.3364107 26.576424,29.7848578 C25.2873839,31.1817085 23.3467926,32.2520025 21.0577698,32.6551205 C18.768747,33.0582385 16.57816,32.7176194 14.8891268,31.8457595 C14.8078783,32.4629363 14.7891286,33.0957378 14.9031891,33.7410391 C15.5031787,37.1409801 18.7437475,39.4096908 22.1436885,38.8112637 C25.4680752,38.2261349 27.7109499,35.1105069 27.248119,31.7955062 L27.2123505,31.5692018 Z M48.7660853,25.7407806 C48.5633263,25.1176335 48.2494734,24.5684792 47.888015,24.0623056 C46.8059237,25.624962 45.0331742,26.954918 42.8225313,27.6726833 C40.6118883,28.3904486 38.3952149,28.3580151 36.6012798,27.7297081 C36.6067164,28.3521862 36.6762181,28.9814388 36.8789772,29.6045859 C37.946308,32.8879365 41.4710833,34.6835682 44.7546514,33.6177846 C47.9635929,32.5762234 49.7508399,29.1817395 48.8344241,25.9648918 L48.7660853,25.7407806 Z M88.3557839,86.0440316 C89.2531221,86.0440316 89.9895005,86.7203118 90.0645283,87.5818258 L90.0708235,87.7270424 L90.0708235,89.4100533 C90.0708235,90.2906332 89.3816734,91.0132597 88.5037641,91.0868864 L88.3557839,91.093064 L43.7647549,91.093064 C42.8674168,91.093064 42.1310383,90.4167839 42.0560105,89.5552699 L42.0497153,89.4100533 L42.0497153,87.7270424 C42.0497153,86.8464625 42.7388654,86.123836 43.6167747,86.0502093 L43.7647549,86.0440316 L88.3557839,86.0440316 Z M94.8684211,36.6356446 C97.6298448,36.6356446 99.8818274,38.8112143 99.9954982,41.5376703 L100,41.7540318 L100,75.8766123 C100,78.6309372 97.8188231,80.8771306 95.0853401,80.9905092 L94.8684211,80.9949994 L36.7105263,80.9949994 C33.9491026,80.9949994 31.69712,78.8194297 31.5834492,76.0929737 L31.5789474,75.8766123 L31.5789474,41.7540318 C31.5789474,38.9997069 33.7601243,36.7535135 36.4936073,36.6401349 L36.7105263,36.6356446 L94.8684211,36.6356446 Z M93.108162,42.6994248 L38.8318462,42.6994248 L38.8318462,74.570574 L93.108162,74.570574 L93.108162,42.6994248 Z"/>
        </svg>
    ';
}
