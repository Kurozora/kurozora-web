<?php

namespace App\Nova;

use Chaseconey\ExternalImage\ExternalImage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Studio extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = 'App\Models\Studio';

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
        'name'
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

            ExternalImage::make('Logo URL')
                ->width(100),

            Text::make('Name')
                ->rules('required')
                ->sortable(),

            Text::make('Logo URL')
                ->rules('max:255')
                ->hideFromIndex(),

            Textarea::make('About')
                ->help('A description of the studio.'),

            Date::make('Founded')
                ->format('YYYY-MM-DD')
                ->help('The date on which the studio was founded. For example: 2015-12-03'),

            Text::make('Website URL')
                ->rules('max:255')
                ->help('The URL to the official website of the studio.')
                ->hideFromIndex(),

            BelongsToMany::make('Anime')
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
        $studioName = $this->name;

        if (!is_string($studioName) || !strlen($studioName))
            $studioName = 'No Studio name';

        return $studioName . ' (ID: ' . $this->id . ')';
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
            <path fill="var(--sidebar-icon)" d="M31,28.90625 L31,21.09375 C31,19.8046875 32.0546875,18.75 33.34375,18.75 L41.15625,18.75 C42.4453125,18.75 43.5,19.8046875 43.5,21.09375 L43.5,28.90625 C43.5,30.1953125 42.4453125,31.25 41.15625,31.25 L33.34375,31.25 C32.0546875,31.25 31,30.1953125 31,28.90625 Z M58.34375,31.25 L66.15625,31.25 C67.4453125,31.25 68.5,30.1953125 68.5,28.90625 L68.5,21.09375 C68.5,19.8046875 67.4453125,18.75 66.15625,18.75 L58.34375,18.75 C57.0546875,18.75 56,19.8046875 56,21.09375 L56,28.90625 C56,30.1953125 57.0546875,31.25 58.34375,31.25 Z M33.34375,50 L41.15625,50 C42.4453125,50 43.5,48.9453125 43.5,47.65625 L43.5,39.84375 C43.5,38.5546875 42.4453125,37.5 41.15625,37.5 L33.34375,37.5 C32.0546875,37.5 31,38.5546875 31,39.84375 L31,47.65625 C31,48.9453125 32.0546875,50 33.34375,50 Z M58.34375,50 L66.15625,50 C67.4453125,50 68.5,48.9453125 68.5,47.65625 L68.5,39.84375 C68.5,38.5546875 67.4453125,37.5 66.15625,37.5 L58.34375,37.5 C57.0546875,37.5 56,38.5546875 56,39.84375 L56,47.65625 C56,48.9453125 57.0546875,50 58.34375,50 Z M43.5,66.40625 L43.5,58.59375 C43.5,57.3046875 42.4453125,56.25 41.15625,56.25 L33.34375,56.25 C32.0546875,56.25 31,57.3046875 31,58.59375 L31,66.40625 C31,67.6953125 32.0546875,68.75 33.34375,68.75 L41.15625,68.75 C42.4453125,68.75 43.5,67.6953125 43.5,66.40625 Z M58.34375,68.75 L66.15625,68.75 C67.4453125,68.75 68.5,67.6953125 68.5,66.40625 L68.5,58.59375 C68.5,57.3046875 67.4453125,56.25 66.15625,56.25 L58.34375,56.25 C57.0546875,56.25 56,57.3046875 56,58.59375 L56,66.40625 C56,67.6953125 57.0546875,68.75 58.34375,68.75 Z M93.5,92.96875 L93.5,100 L6,100 L6,92.96875 C6,91.6796875 7.0546875,90.625 8.34375,90.625 L12.1523438,90.625 L12.1523438,4.6875 C12.1523438,2.08984375 14.2421875,0 16.8398438,0 L82.6601562,0 C85.2578125,0 87.3476562,2.08984375 87.3476562,4.6875 L87.3476562,90.625 L91.15625,90.625 C92.4453125,90.625 93.5,91.6796875 93.5,92.96875 Z M21.5273438,90.4296875 L43.5,90.4296875 L43.5,77.34375 C43.5,76.0546875 44.5546875,75 45.84375,75 L53.65625,75 C54.9453125,75 56,76.0546875 56,77.34375 L56,90.4296875 L77.9726562,90.4296875 L77.9726562,9.5703125 L21.625,9.375 L21.5273438,90.4296875 Z"/>
        </svg>
    ';
}
