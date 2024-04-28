<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class EpisodeTranslation extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\EpisodeTranslation::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\EpisodeTranslation|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title', 'locale'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Episode';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            Heading::make('Meta information'),

            BelongsTo::make('Episode')
                ->searchable()
                ->sortable(),

            BelongsTo::make('Language')
                ->sortable(),

            Text::make('Title')
                ->sortable()
                ->required(),

            Textarea::make('Synopsis')
                ->nullable(),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $episodeTranslation = $this->resource;

        return $episodeTranslation->title . ' (ID: ' . $episodeTranslation->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewEpisodeTranslation');
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
}
