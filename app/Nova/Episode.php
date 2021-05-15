<?php

namespace App\Nova;

use Chaseconey\ExternalImage\ExternalImage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Episode extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\AnimeEpisode::class;

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
        'id', 'title'
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

            BelongsTo::make('Season')
                ->searchable()
                ->sortable(),

            ExternalImage::make('Preview Image')
                ->width(240)
                ->help('A link to a preview image of the episode.'),

            Number::make('Number')
                ->rules('required')
                ->help('The episode number of the episode.'),

            Text::make('Title')
                ->rules('required', 'max:255'),

            Textarea::make('Overview')
                ->hideFromIndex()
                ->help('A short description of the Episode.'),

            DateTime::make('First Aired')
                ->sortable()
                ->help('The air date of the of the episode. Leave empty if not announced yet.'),

            Number::make('Duration')
                ->rules('required')
                ->sortable()
                ->help('The duration of the episode in minutes.'),

            Boolean::make('Verified')
                ->help('Check the box if the information is correct.'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->name . ' (ID: ' . $this->id . ')';
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
            <path fill="var(--sidebar-icon)" d="M95.3125,13 L93.75,13 L93.75,16.90625 C93.75,18.1953125 92.6953125,19.25 91.40625,19.25 L83.59375,19.25 C82.3046875,19.25 81.25,18.1953125 81.25,16.90625 L81.25,13 L18.75,13 L18.75,16.90625 C18.75,18.1953125 17.6953125,19.25 16.40625,19.25 L8.59375,19.25 C7.3046875,19.25 6.25,18.1953125 6.25,16.90625 L6.25,13 L4.6875,13 C2.08984375,13 0,15.0898438 0,17.6875 L0,83.3125 C0,85.9101562 2.08984375,88 4.6875,88 L6.25,88 L6.25,84.09375 C6.25,82.8046875 7.3046875,81.75 8.59375,81.75 L16.40625,81.75 C17.6953125,81.75 18.75,82.8046875 18.75,84.09375 L18.75,88 L81.25,88 L81.25,84.09375 C81.25,82.8046875 82.3046875,81.75 83.59375,81.75 L91.40625,81.75 C92.6953125,81.75 93.75,82.8046875 93.75,84.09375 L93.75,88 L95.3125,88 C97.9101562,88 100,85.9101562 100,83.3125 L100,17.6875 C100,15.0898438 97.9101562,13 95.3125,13 Z M18.75,73.15625 C18.75,74.4453125 17.6953125,75.5 16.40625,75.5 L8.59375,75.5 C7.3046875,75.5 6.25,74.4453125 6.25,73.15625 L6.25,65.34375 C6.25,64.0546875 7.3046875,63 8.59375,63 L16.40625,63 C17.6953125,63 18.75,64.0546875 18.75,65.34375 L18.75,73.15625 Z M18.75,54.40625 C18.75,55.6953125 17.6953125,56.75 16.40625,56.75 L8.59375,56.75 C7.3046875,56.75 6.25,55.6953125 6.25,54.40625 L6.25,46.59375 C6.25,45.3046875 7.3046875,44.25 8.59375,44.25 L16.40625,44.25 C17.6953125,44.25 18.75,45.3046875 18.75,46.59375 L18.75,54.40625 Z M18.75,35.65625 C18.75,36.9453125 17.6953125,38 16.40625,38 L8.59375,38 C7.3046875,38 6.25,36.9453125 6.25,35.65625 L6.25,27.84375 C6.25,26.5546875 7.3046875,25.5 8.59375,25.5 L16.40625,25.5 C17.6953125,25.5 18.75,26.5546875 18.75,27.84375 L18.75,35.65625 Z M75,76.28125 C75,77.5703125 73.9453125,78.625 72.65625,78.625 L27.34375,78.625 C26.0546875,78.625 25,77.5703125 25,76.28125 L25,57.53125 C25,56.2421875 26.0546875,55.1875 27.34375,55.1875 L72.65625,55.1875 C73.9453125,55.1875 75,56.2421875 75,57.53125 L75,76.28125 Z M75,43.46875 C75,44.7578125 73.9453125,45.8125 72.65625,45.8125 L27.34375,45.8125 C26.0546875,45.8125 25,44.7578125 25,43.46875 L25,24.71875 C25,23.4296875 26.0546875,22.375 27.34375,22.375 L72.65625,22.375 C73.9453125,22.375 75,23.4296875 75,24.71875 L75,43.46875 Z M93.75,73.15625 C93.75,74.4453125 92.6953125,75.5 91.40625,75.5 L83.59375,75.5 C82.3046875,75.5 81.25,74.4453125 81.25,73.15625 L81.25,65.34375 C81.25,64.0546875 82.3046875,63 83.59375,63 L91.40625,63 C92.6953125,63 93.75,64.0546875 93.75,65.34375 L93.75,73.15625 Z M93.75,54.40625 C93.75,55.6953125 92.6953125,56.75 91.40625,56.75 L83.59375,56.75 C82.3046875,56.75 81.25,55.6953125 81.25,54.40625 L81.25,46.59375 C81.25,45.3046875 82.3046875,44.25 83.59375,44.25 L91.40625,44.25 C92.6953125,44.25 93.75,45.3046875 93.75,46.59375 L93.75,54.40625 Z M93.75,35.65625 C93.75,36.9453125 92.6953125,38 91.40625,38 L83.59375,38 C82.3046875,38 81.25,36.9453125 81.25,35.65625 L81.25,27.84375 C81.25,26.5546875 82.3046875,25.5 83.59375,25.5 L91.40625,25.5 C92.6953125,25.5 93.75,26.5546875 93.75,27.84375 L93.75,35.65625 Z"/>
        </svg>
    ';
}
