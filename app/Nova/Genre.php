<?php

namespace App\Nova;

use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;

class Genre extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Genre::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Genre|null
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
        'id', 'name', 'slug'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Genre';

    /**
     * Determine if this resource uses Laravel Scout.
     *
     * @return bool
     */
    public static function usesScout(): bool
    {
        return false;
    }

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
            Heading::make('Identification'),

            ID::make()->sortable(),

            Number::make('MAL ID')
                ->hideFromIndex()
                ->help('Used to identify the Genre on <a target="_blank" href="https://myanimelist.net/anime/genre/' . ($this->resource->mal_id ?? 'slug-identifier') . '">MyAnimeList</a>'),

            Heading::make('Media'),

            Images::make('Symbol')
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->name;
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

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the genre in a URL: ' . config('app.url') . '/genre/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from name.'),

            Text::make('Name')
                ->rules('required')
                ->sortable(),

            Text::make('Description')
                ->hideFromIndex(),

            Color::make('Color')
                ->slider()
                ->rules('required'),

            Color::make('Background Color 1')
                ->slider()
                ->rules('required'),

            Color::make('Background Color 2')
                ->slider()
                ->rules('required'),

            Color::make('Text Color 1')
                ->slider()
                ->rules('required'),

            Color::make('Text Color 2')
                ->slider()
                ->rules('required'),

            Boolean::make('Is NSFW')
                ->rules('required')
                ->sortable(),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->help('The TV rating of the genre. For example NR, G, PG-12, etc.')
                ->required(),

            BelongsToMany::make('Animes')
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
        $genre = $this->resource;
        $genreName = $genre->name;

        if (!is_string($genreName) || !strlen($genreName)) {
            $genreName = 'No genre title';
        }

        return $genreName . ' (ID: ' . $genre->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewGenre');
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
