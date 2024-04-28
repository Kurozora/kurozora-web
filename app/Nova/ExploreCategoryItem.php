<?php

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaSortable\Traits\HasSortableRows;

class ExploreCategoryItem extends Resource
{
    use HasSortableRows {
        indexQuery as indexSortableQuery;
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\ExploreCategoryItem::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\ExploreCategoryItem|null
     */
    public $resource;

    /**
     * Whether the sortable cache is enabled.
     *
     * @var bool
     */
    public static bool $sortableCacheEnabled = false;

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param Request $request
     * @return bool
     */
    public static function authorizedToViewAny(Request $request): bool
    {
        return $request->user()?->can('viewExploreCategoryItem') ?? false;
    }

    /**
     * Determine if the given resource is sortable.
     *
     * @param NovaRequest $request
     * @param $resource
     * @return bool
     */
    public static function canSort(NovaRequest $request, $resource): bool
    {
        return auth()->user()->hasRole(['superAdmin', 'admin', 'mod', 'editor']);
    }

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
        'explore_category'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Explore Category';

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

            Number::make('Position', 'position')
                ->sortable()
                ->readonly()
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->help('This determines the position on the item. Generated automatically!'),

            BelongsTo::make('Explore Category', 'explore_category'),

            MorphTo::make('Model')
                ->types([
                    Anime::class,
                    MediaSong::class,
                    Character::class,
                    Game::class,
                    Genre::class,
                    Manga::class,
                    Person::class,
                    Theme::class,
                ])
                ->searchable(),

            Text::make('Model Type')
                ->onlyOnIndex()
                ->onlyOnDetail()
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
        $exploreCategory = $this->resource->explore_category;

        return $exploreCategory->title . ' Items';
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request): array
    {
        return [
        ];
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
     * Build an "index" query for the given resource.
     *
     * @param NovaRequest $request
     * @param  Builder  $query
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return parent::indexQuery($request, static::indexSortableQuery($request, $query));
    }
}
