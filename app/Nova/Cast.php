<?php

namespace App\Nova;

use App\Models\AnimeCast;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;

class Cast extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = 'App\Models\AnimeCast';

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
            ID::make()->sortable(),

            BelongsTo::make('Anime')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Character')
                ->searchable()
                ->sortable(),

            BelongsTo::make('Person')
                ->searchable()
                ->sortable(),

            BelongsTo::make('Cast Role')
                ->rules('required')
                ->help('If you’re not sure what role the character has, choose "Supporting character".')
                ->sortable(),

            BelongsTo::make('Language')
                ->rules('required')
                ->sortable(),
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
        $personName = $animeCast->person->full_name;

        return $personName . ' as ' . $characterName . ' in ' . $animeTitle . ' (ID: ' . $animeCast->id . ')';
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
        <svg class="sidebar-icon" viewBox="0 0 512 530" xmlns="http://www.w3.org/2000/svg">
            <g transform="translate(0.000000, 18.000000)" fill="var(--sidebar-icon)">
                <path d="M384,336 C343.4,336 336.4,334.5 311.8,342.8 C294.3,348.7 275.5,352 256,352 C236.5,352 217.7,348.7 200.2,342.8 C175.6,334.5 168.7,336 128,336 C57.3,336 0,393.3 0,464 L0,480 C0,497.7 14.3,512 32,512 L480,512 C497.7,512 512,497.7 512,480 L512,464 C512,393.3 454.7,336 384,336 Z M464,464 L48,464 C48,442.6 56.3,422.5 71.4,407.4 C86.5,392.3 106.6,384 128,384 C169.1,384 169,382.9 184.8,388.2 C207.8,396 231.8,400 256,400 C280.2,400 304.2,396 327.2,388.2 C343,382.8 342.9,384 384,384 C428.1,384 464,419.9 464,464 Z"></path>
                <path d="M96.00625,160 C107.05,160 116,152.15 116,142.4625 L116,0 L76.60625,138.20625 C73.45,149.275 83,160 96.00625,160 Z M435.39375,138.20625 L396,0 L396,142.4625 C396,152.15 404.95,160 415.99375,160 C429,160 438.55,149.275 435.39375,138.20625 Z M376,131.7625 C376,56.46875 256,0 256,0 C256,0 136,56.46875 136,131.7625 C136,183.4875 121.7125,222.95 116.54375,244.70625 C114.4,253.725 118.7875,263.0625 126.74375,266.73125 L227.70625,315.95625 C233.167181,318.61775 239.162523,320 245.2375,320 L266.775,320 C272.85,320 278.84375,318.61875 284.30625,315.95625 L385.26875,266.73125 C393.225,263.0625 397.6125,253.725 395.46875,244.70625 C390.2875,222.95 376,183.4875 376,131.7625 L376,131.7625 Z M271,289.0625 L271,180 L326,160 L326,140 L186,140 L186,160 L241,180 L241,289.0625 L147.8,243.63125 C148.28125,241.86875 148.7875,240.025 149.31875,238.09375 C155.9625,213.975 166,177.53125 166,131.7625 C166,92.1125 223.425,51.94375 256,33.7875 C288.575,51.94375 346,92.1125 346,131.7625 C346,177.53125 356.0375,213.975 362.68125,238.0875 C363.2125,240.0125 363.71875,241.8625 364.2,243.61875 L271,289.0625 Z" stroke="var(--sidebar-icon)" stroke-width="10"></path>
            </g>
        </svg>
    ';
}
