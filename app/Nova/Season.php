<?php

namespace App\Nova;

use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;

class Season extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Season::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Season|null
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
        'id'
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = [
        'anime',
        'translation'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Season';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            Heading::make('Media'),

            Images::make('Poster')
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->title;
                })
                ->customPropertiesFields([
                    Heading::make('Colors (automatically generated if empty)'),

                    Color::make('Background Color')
                        ->slider()
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->slider()
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->slider()
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->slider()
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
                        ->slider()
                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

                    Heading::make('Dimensions (automatically generated if empty)'),

                    Number::make('Width')
                        ->help('The maximum width available for the image.'),

                    Number::make('Height')
                        ->help('The maximum height available for the image.'),
                ]),

            Heading::make('Meta information'),

            BelongsTo::make('Anime')
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->required()
                ->help('The TV rating of the episode. For example NR, G, PG-12, etc.'),

            Number::make('Season Number', 'number')
                ->rules('required', 'min:1')
                ->hideFromIndex()
                ->help('The sequence in which the season starts.'),

            Text::make('Title Translations', 'title')
                ->sortable()
                ->required()
                ->translatable()
                ->help('Usually the name of the arc of the story. If unknown, use "Season #" as the title.'),

            Textarea::make('Synopsis Translations', 'synopsis')
                ->nullable()
                ->translatable()
                ->help('A short description of the Season.'),

            DateTime::make('Started At')
                ->sortable()
                ->help('First air date of the of the season in JST timezone. Leave empty if not announced yet.'),

            DateTime::make('Ended At')
                ->sortable()
                ->help('Last air date of the of the season in JST timezone. Leave empty if not announced yet.'),

            HasMany::make('Translations', 'translations', SeasonTranslation::class),

            HasMany::make('Episodes'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $season = $this->resource;
        $seasonName = ''; // $season->title;

        if (!is_string($seasonName) || !strlen($seasonName)) {
            $seasonName = 'No season title';
        }

        return $seasonName . ' (ID: ' . $season->id . ')';
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     *
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
     *
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
     *
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
     *
     * @return array
     */
    public function actions(Request $request): array
    {
        return [];
    }
}
