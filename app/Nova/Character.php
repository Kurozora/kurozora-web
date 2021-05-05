<?php

namespace App\Nova;

use App\Enums\AstrologicalSign;
use Chaseconey\ExternalImage\ExternalImage;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Character extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = 'App\Models\Character';

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
        'id', 'name',
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

            Heading::make('Personal Information')
                ->onlyOnForms(),

            ExternalImage::make('Image'),

            Text::make('Name')
                ->rules('required')
                ->sortable(),

            Textarea::make('About')
                ->onlyOnForms()
                ->help('A short description of the character.'),

            Text::make('Debut')
                ->rules( 'max:255')
                ->help('When did the character first appear? E.g. Episode 1')
                ->sortable(),

            Select::make('Status')
                ->options([
                    'Alive' => 'Alive',
                    'Deceased' => 'Deceased'
                ])
                ->nullable(true)
                ->sortable(),

            Text::make('Blood Type')
                ->rules('nullable', 'max:3', 'string')
                ->help('The official blood type of the character. E.g. O, AB, or even fictional ones like F.')
                ->hideFromIndex(),

            Text::make('Favorite Food')
                ->rules('max:255')
                ->help('The official favorite food of the character. E.g. Almond Jelly.')
                ->hideFromIndex(),

            Heading::make('Body Information')
                ->onlyOnForms(),

            Text::make('Height')
                ->help('The official height of the character in cm. E.g. 10.25 or 277. If it\'s an abnormally tall height, then write it including the unit. E.g. 250ly (Light Years).')
                ->hideFromIndex(),

            Number::make('Bust')
                ->help('The official bust size of the character if it applies. E.g. 50 or 60.4')
                ->step(0.001)
                ->hideFromIndex(),

            Number::make('Waist')
                ->help('The official waist size of the character if it applies. E.g. 50 or 60.4')
                ->step(0.001)
                ->hideFromIndex(),

            Number::make('Hip')
                ->help('The official hip size of the character if it applies. E.g. 50 or 60.4')
                ->step(0.001)
            ->hideFromIndex(),

            Heading::make('Birth Information')
                ->onlyOnForms(),

            Number::make('Age')
                ->help('The official age of the character in years. E.g. 17 or 25. If the age is something crazy like 3 and a half trillion years old, then write it out as a real number.')
                ->step(0.01)
                ->sortable(),

            Text::make('Birth Day')
                ->rules('nullable', 'max:31', 'numeric')
                ->hideFromIndex(),

            Text::make('Birth Month')
                ->rules('nullable', 'max:12', 'numeric')
                ->hideFromIndex(),

            Select::make('Astrological Sign')
                ->options(AstrologicalSign::asSelectArray())
                ->displayUsingLabels()
                ->nullable(true)
                ->sortable(),

            HasMany::make('Cast'),

            HasMany::make('Anime'),

            HasMany::make('Actors'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $characterName = $this->name;

        if (!is_string($characterName) || !strlen($characterName))
            $characterName = 'No character title';

        return $characterName . ' (ID: ' . $this->id . ')';
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
        <svg class="sidebar-icon" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M32.01 256C49.68 256 64 243.44 64 227.94V0L.97 221.13C-4.08 238.84 11.2 256 32.01 256zm543.02-34.87L512 0v227.94c0 15.5 14.32 28.06 31.99 28.06 20.81 0 36.09-17.16 31.04-34.87zM480 210.82C480 90.35 288 0 288 0S96 90.35 96 210.82c0 82.76-22.86 145.9-31.13 180.71-3.43 14.43 3.59 29.37 16.32 35.24l161.54 78.76a64.01 64.01 0 0 0 28.05 6.47h34.46c9.72 0 19.31-2.21 28.05-6.47l161.54-78.76c12.73-5.87 19.75-20.81 16.32-35.24-8.29-34.81-31.15-97.95-31.15-180.71zM312 462.5V288l88-32v-32H176v32l88 32v174.5l-149.12-72.69c.77-2.82 1.58-5.77 2.43-8.86 10.63-38.59 26.69-96.9 26.69-170.13 0-63.44 91.88-127.71 144-156.76 52.12 29.05 144 93.32 144 156.76 0 73.23 16.06 131.54 26.69 170.12.85 3.08 1.66 6.04 2.43 8.85L312 462.5z"/>
        </svg>
    ';
}
