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

class ExploreCategory extends Resource
{
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
     * Determine if the resource should be available for the given request.
     *
     * @param Request $request
     * @return bool
     */
    public static function authorizedToViewAny(Request $request): bool
    {
        return $request->user()->can('viewExploreCategory');
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
            Heading::make('Identification'),

            ID::make()->sortable(),

            Heading::make('Meta information'),

            Text::make('Title')
                ->rules('required', 'max:255')
                ->sortable()
                ->help('Please fill in a title, even if it is not displayed on the explore page.'),

            Text::make('Description')
                ->rules('max:255')
                ->sortable()
                ->help('Give the category a description if necessary.'),

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the explore category in a URL: https://kurozora.app/explore/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from title.'),

            Text::make('Secondary Slug')
                ->rules('max:2083')
                ->sortable()
                ->help('If the category shouldn\'t link to itself, then add a secondary slug here. This slug starts from the root URL: https://kurozora.app/<strong>' . ($this->resource->secondary_slug ?? 'slug-identifier') . '</strong>'),

            Number::make('Position/order', 'position')
                ->rules('required', 'numeric', 'min:1', 'max:100')
                ->sortable()
                ->help('This will determine the position on the explore page. Enter a number ranging from <strong>1 to 100</strong>. Lower numbers will display first.'),

            Select::make('Type')
                ->options(ExploreCategoryTypes::asSelectArray())
                ->displayUsingLabels()
                ->rules('required')
                ->sortable()
                ->help('<b>Most Popular Shows</b> is automatically generated.'),

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

            HasMany::make('Items', 'explore_category_items', ExploreCategoryItem::class),
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
        return parent::indexQuery($request, $query)->withoutGlobalScope(new ExploreCategoryIsEnabledScope);
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

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M24.2840417,97.0596724 C22.6768904,97.9047734 20.7288152,97.7611859 19.2629394,96.6895801 C17.7970635,95.6179743 17.0691385,93.8053134 17.3867356,92.0175038 L22.2862013,63.3342239 L1.45158006,43.0704142 C0.141228879,41.8042816 -0.330705638,39.9008711 0.236376642,38.1692449 C0.803458922,36.4376186 2.30989771,35.1820965 4.11536726,34.9363497 L32.8937825,30.7503984 L45.7370422,4.68333799 C46.5318327,3.04230081 48.1947541,2 50.0181287,2 C51.8415034,2 53.5044247,3.04230081 54.2992153,4.68333799 L67.142475,30.7503984 L95.9684578,34.9363497 C97.7451542,35.2104819 99.2155259,36.4628238 99.7689007,38.173255 C100.322275,39.8836863 99.8641056,41.7599681 98.5846774,43.0228465 L77.7500561,63.3342239 L82.6970895,92.0175038 C83.0282951,93.8190351 82.2956488,95.6506887 80.81339,96.7268492 C79.3311313,97.8030096 77.3626373,97.9324579 75.7522157,97.0596724 L50.0656963,83.5028983 L24.2840417,97.0596724 Z M47.8300178,73.8942374 C49.21491,73.1668375 50.8689151,73.1668375 52.2538072,73.8942374 L71.6613997,84.1212775 L67.9511246,62.4780066 C67.6977936,60.9409875 68.2130915,59.377325 69.3305859,58.2920553 L85.0279033,42.9752789 L63.3370647,39.7882478 C61.7821905,39.5805117 60.4297065,38.6195363 59.7219249,37.2195959 L50.0181287,17.5265977 L40.3143325,37.2195959 C39.6158423,38.6069283 38.2840046,39.5658515 36.7467604,39.7882478 L15.0083542,42.9752789 L30.7056716,58.2920553 C31.8404975,59.3679778 32.3740097,60.932947 32.1327004,62.4780066 L28.3748578,84.1212775 L47.8300178,73.8942374 L47.8300178,73.8942374 Z"/>
        </svg>
    ';
}
