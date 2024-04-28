<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

class MediaRelation extends Resource
{
    use SearchesRelations;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MediaRelation::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\MediaRelation|null
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
        'id',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static array $searchRelations = [
        'model' => ['original_title'],
        'related' => ['original_title'],
        'relation' => ['name'],
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Relations';

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

            MorphTo::make('Media', 'model')
                ->types([
                    Anime::class,
                    Game::class,
                    Manga::class,
                ])
                ->searchable()
                ->sortable(),

            Text::make('Model Type')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->sortable(),

            BelongsTo::make('Relation')
                ->sortable(),

            MorphTo::make('Related Media', 'related')
                ->types([
                    Anime::class,
                    Game::class,
                    Manga::class,
                ])
                ->searchable()
                ->sortable(),

            Text::make('Related Type', 'related_type')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->sortable(),
        ];
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewMediaRelation');
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
