<?php

namespace App\Nova;

use App\Enums\ExploreCategorySize;
use App\Enums\ExploreCategoryTypes;
use App\Scopes\ExploreCategoryIsEnabledScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaSortable\Traits\HasSortableRows;

class ExploreCategory extends Resource
{
    use HasSortableRows {
        indexQuery as indexSortableQuery;
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\ExploreCategory::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\ExploreCategory|null
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
        return $request->user()?->can('viewExploreCategory') ?? false;
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
        'id', 'title', 'description', 'slug'
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
                ->help('This determines the position on the explore page. Generated automatically!'),

            Text::make('Title')
                ->rules('required', 'max:255')
                ->sortable()
                ->help('Please fill in a title, even if it is not displayed on the explore page.'),

            Text::make('Description')
                ->rules('max:255')
                ->sortable()
                ->help('Give the category a description if necessary.'),

            Text::make('Slug')
                ->rules('max:2083')
                ->sortable()
                ->help('Used to identify the explore category in a URL: ' . config('app.url') . '/explore/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from title.'),

            Text::make('Secondary Slug')
                ->rules('max:2083')
                ->sortable()
                ->help('If the category shouldn’t link to itself, then add a secondary slug here. This slug starts from the root URL: ' . config('app.url') . '/<strong>' . ($this->resource->secondary_slug ?? 'slug-identifier') . '</strong>'),

            Select::make('Type')
                ->options(ExploreCategoryTypes::asSelectArray())
                ->displayUsingLabels()
                ->rules('required')
                ->sortable()
                ->help('The following are automtically generated:<br/><b>Most Popular Shows</b><br/><b>Upcoming Shows</b><br/><b>Anime Continuing</b><br/><b>Anime Season</b><br/><b>Characters</b><br/><b>People</b>'),

            Select::make('Size')
                ->options(ExploreCategorySize::asSelectArray())
                ->rules('required')
                ->sortable()
                ->help('For genres, only use <b>Medium</b>.<br/><b>Video</b> is used only for shows.'),

            Boolean::make('Is Global')
                ->default(false)
                ->rules('required')
                ->sortable()
                ->help('Turning this on will also include the category in genre specific categories.'),

            Boolean::make('Is Enabled')
                ->default(true)
                ->rules('required')
                ->sortable()
                ->help('Turning this on will make the category visible to the users.'),

            HasMany::make('Items', 'exploreCategoryItems', ExploreCategoryItem::class),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $exploreCategory = $this->resource;

        return $exploreCategory->title . ' (ID: ' . $exploreCategory->id . ')';
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
        $query->withoutGlobalScope(new ExploreCategoryIsEnabledScope);
        return parent::indexQuery($request, static::indexSortableQuery($request, $query));
    }

    /**
     * Build a "detail" query for the given resource.
     *
     * @param NovaRequest $request
     * @param  Builder  $query
     * @return Builder
     */
    public static function detailQuery(NovaRequest $request, $query): Builder
    {
        return parent::detailQuery($request, $query)->withoutGlobalScope(new ExploreCategoryIsEnabledScope);
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param NovaRequest $request
     * @param  Builder  $query
     * @return Builder
     */
    public static function relatableQuery(NovaRequest $request, $query): Builder
    {
        return parent::relatableQuery($request, $query)->withoutGlobalScope(new ExploreCategoryIsEnabledScope);
    }
}
