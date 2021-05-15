<?php

namespace App\Nova;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Validator;

class AnimeStaff extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\AnimeStaff::class;

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

            BelongsTo::make('Anime')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Person')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Staff Role')
                ->searchable()
                ->sortable(),
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
        $resourceID = $request->resourceId;
        $anime = $request->post('anime');
        $person = $request->post('person');
        $staffRole = $request->post('staff_role');

        $unique = Rule::unique(\App\Models\AnimeStaff::TABLE_NAME, 'staff_role_id')->where(function ($query) use($resourceID, $anime, $person, $staffRole) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query
                ->where([
                    ['anime_id', $anime],
                    ['person_id', $person],
                    ['staff_role_id', $staffRole]
                ]);
        });

        $uniqueValidator = Validator::make($request->only('staff_role'), [
            'staff_role' => [$unique],
        ], [
            'staff_role' => __('validation.unique')
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
            <path fill="var(--sidebar-icon)" d="M38.2978723,0 C50.0486117,0 59.5744681,9.51394907 59.5744681,21.25 C59.5744681,22.5224534 59.4624872,23.7687849 59.247831,24.9797007 C56.2381286,24.4937259 53.4089318,24.1693522 50.7601258,24.0061304 C51.4421749,20.9383268 50.9726536,17.6759231 49.353516,14.8749999 C47.0730973,10.9301362 42.8587097,8.5 38.2978723,8.5 C31.2474288,8.5 25.5319151,14.2083695 25.5319151,21.25 C25.5319151,24.2507774 26.5698649,27.0094317 28.3067537,29.1873757 C27.4567382,29.9002456 26.6881111,30.6854487 26,31.5425806 C24.8632683,32.9585279 23.928552,34.5171778 23.1958513,36.2185303 C19.3790873,32.3776147 17.0212766,27.0888113 17.0212766,21.25 C17.0212766,9.51394907 26.547133,0 38.2978723,0 Z M26.3157895,59.5 L21.2765957,59.5 C14.2261521,59.5 8.5106383,65.2083694 8.5106383,72.25 L8.5106383,80.75 C8.5106383,83.0972102 6.60546702,85 4.25531915,85 C1.90517128,85 0,83.0972102 0,80.75 L0,72.25 C0,60.5139491 9.52585639,51 21.2765957,51 L26.3157895,51 L26.3157895,59.5 Z M88.3557839,80.9509676 C89.3029742,80.9509676 90.0708235,81.7044773 90.0708235,82.6339784 L90.0708235,84.3169893 C90.0708235,85.2464903 89.3029742,86 88.3557839,86 L43.7647549,86 C42.8175646,86 42.0497153,85.2464903 42.0497153,84.3169893 L42.0497153,82.6339784 C42.0497153,81.7044773 42.8175646,80.9509676 43.7647549,80.9509676 L88.3557839,80.9509676 Z M94.8684211,31.5425806 C97.7025138,31.5425806 100,33.8341606 100,36.6609677 L100,70.7835483 C100,73.6103554 97.7025138,75.9019354 94.8684211,75.9019354 L36.7105263,75.9019354 C33.8764336,75.9019354 31.5789474,73.6103554 31.5789474,70.7835483 L31.5789474,36.6609677 C31.5789474,33.8341606 33.8764336,31.5425806 36.7105263,31.5425806 L94.8684211,31.5425806 Z M93.108162,37.6063608 L38.8318462,37.6063608 L38.8318462,69.47751 L93.108162,69.47751 L93.108162,37.6063608 Z"/>
        </svg>
    ';
}
