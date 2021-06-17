<?php

namespace App\Nova;

use App\Models\AnimeCast;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Validator;

class Cast extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = AnimeCast::class;

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
    public static $group = 'Anime Cast';

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

            BelongsTo::make('Anime')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Character')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Person')
                ->searchable()
                ->sortable()
                ->nullable()
                ->help('Sometimes unknown if the anime is a hentai. Leave empty in that case.'),

            BelongsTo::make('Cast Role')
                ->sortable()
                ->help('If you’re not sure what role the character has, choose "Supporting Character".'),

            BelongsTo::make('Language')
                ->sortable()
                ->nullable()
                ->help('Usually Japanese or English. Leave empty if unknown.'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        /** @var AnimeCast $animeCast */
        $animeCast = $this->resource;

        $animeTitle = $animeCast->anime->title;
        $characterName = $animeCast->character->name;
        $personName = $animeCast->person?->full_name ?? 'Unknown';

        return $personName . ' as ' . $characterName . ' in ' . $animeTitle . ' (ID: ' . $animeCast->id . ')';
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
        $character = $request->post('character');
        $person = $request->post('person');
        $castRole = $request->post('cast_role');
        $language = $request->post('language');

        $unique = Rule::unique(AnimeCast::TABLE_NAME, 'language_id')->where(function ($query) use ($resourceID, $anime, $character, $person, $castRole, $language) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query->where([
                ['anime_id', $anime],
                ['character_id', $character],
                ['person_id', $person],
                ['cast_role_id', $castRole],
                ['language_id', $language],
            ]);
        });

        $uniqueValidator = Validator::make($request->only('language'), [
            'language'  => [$unique],
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
            <path fill="var(--sidebar-icon)" d="M75,65.625 C67.0703125,65.625 65.703125,65.3320313 60.8984375,66.953125 C57.4804688,68.1054688 53.8085938,68.75 50,68.75 C46.1914062,68.75 42.5195312,68.1054688 39.1015625,66.953125 C34.296875,65.3320313 32.9492188,65.625 25,65.625 C11.1914062,65.625 0,76.8164063 0,90.625 L0,93.75 C0,97.2070313 2.79296875,100 6.25,100 L93.75,100 C97.2070312,100 100,97.2070313 100,93.75 L100,90.625 C100,76.8164063 88.8085938,65.625 75,65.625 Z M92,92.3277805 L8,92.3277805 C8,88.05385 9.67596154,80.9488372 12.725,77.9331199 C15.7740385,74.9174025 19.8326923,73.2597566 24.1538462,73.2597566 C32.4528846,73.2597566 32.4326923,73.0400686 35.6230769,74.0985654 C40.2673077,75.6563531 45.1134615,76.4552187 50,76.4552187 C54.8865385,76.4552187 59.7326923,75.6563531 64.3769231,74.0985654 C67.5673077,73.0200969 67.5471154,73.2597566 75.8461538,73.2597566 C84.7509615,73.2597566 92,83.5202882 92,92.3277805 Z M18.7512207,31.25 C20.9082031,31.25 22.65625,29.7167969 22.65625,27.824707 L22.65625,0 L14.9621582,26.9934082 C14.3457031,29.1552734 16.2109375,31.25 18.7512207,31.25 Z M85.0378418,26.9934082 L77.34375,0 L77.34375,27.824707 C77.34375,29.7167969 79.0917969,31.25 81.2487793,31.25 C83.7890625,31.25 85.6542969,29.1552734 85.0378418,26.9934082 Z M73.4375,25.7348633 C73.4375,11.0290527 50,0 50,0 C50,0 26.5625,11.0290527 26.5625,25.7348633 C26.5625,35.8374023 23.7719727,43.5449219 22.7624512,47.7941895 C22.34375,49.5556641 23.2006836,51.3793945 24.7546387,52.0959473 L44.473877,61.7102051 C45.540465,62.2300293 46.7114303,62.5 47.8979492,62.5 L52.1044922,62.5 C53.2910156,62.5 54.4616699,62.2302246 55.5285645,61.7102051 L75.2478027,52.0959473 C76.8017578,51.3793945 77.6586914,49.5556641 77.2399902,47.7941895 C76.2280273,43.5449219 73.4375,35.8374023 73.4375,25.7348633 L73.4375,25.7348633 Z M52.9296875,56.4575195 L52.9296875,35.15625 L63.671875,31.25 L63.671875,27.34375 L36.328125,27.34375 L36.328125,31.25 L47.0703125,35.15625 L47.0703125,56.4575195 L28.8671875,47.5842285 C28.9611816,47.2399902 29.0600586,46.8798828 29.1638184,46.5026855 C30.4614258,41.7919922 32.421875,34.6740723 32.421875,25.7348633 C32.421875,17.9907227 43.6376953,10.1452637 50,6.59912109 C56.3623047,10.1452637 67.578125,17.9907227 67.578125,25.7348633 C67.578125,34.6740723 69.5385742,41.7919922 70.8361816,46.5014648 C70.9399414,46.8774414 71.0388184,47.2387695 71.1328125,47.5817871 L52.9296875,56.4575195 Z"/>
        </svg>
    ';
}
