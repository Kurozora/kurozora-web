<?php

namespace App\Nova;

use App\Enums\SongType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaSortable\Traits\HasSortableRows;
use Titasgailius\SearchRelations\SearchesRelations;

class MediaSong extends Resource
{
    use SearchesRelations,
        HasSortableRows {
            indexQuery as indexSortableQuery;
        }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MediaSong::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\MediaSong|null
     */
    public $resource;

    /**
     * Whether the sortable cache is enabled.
     *
     * @var bool
     */
    public static bool $sortableCacheEnabled = false;

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
        'id', 'type'
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static array $searchRelations = [
        'song' => ['id', 'title', 'artist'],
        'model' => ['id', 'original_title'],
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Media';

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

            MorphTo::make('Model')
                ->types([
                    Anime::class,
                    Game::class,
                ])
                ->searchable()
                ->sortable()
                ->required(),

            BelongsTo::make('Song')
                ->searchable()
                ->sortable()
                ->required(),

            Select::make('Type')
                ->options(SongType::asSelectArray())
                ->displayUsingLabels()
                ->sortable()
                ->help('Choose the type depending on when the song is played.')
                ->required(),

            Number::make('Position')
                ->sortable()
                ->help('In which order the same type was played? For example opening 1, opening 2, background 5 etc. When in doubt, use 1.')
                ->required(),

            Text::make('Episodes')
                ->help('For example: 1-12; or 1-12, 14-24; or even 14, 16, 19'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $mediaSong = $this->resource;

        return $mediaSong->song->title . ' | ' . $mediaSong->model->original_title . ' (ID: ' . $mediaSong->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewMediaSong');
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

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M10.2923301,76.4797685 L89.7476949,76.4797685 C96.5962347,76.4797685 100,73.1958239 100,66.1873892 L100,20.2923301 C100,13.2839364 96.5962347,10 89.7476949,10 L10.2923301,10 C3.44411838,10 0,13.2839364 0,20.2923301 L0,66.1873892 C0,73.1958239 3.44411838,76.4797685 10.2923301,76.4797685 Z M10.4124871,70.0320036 C7.64912183,70.0320036 6.4477157,68.9106475 6.4477157,66.0672322 L6.4477157,20.3724621 C6.4477157,17.5690718 7.64912183,16.4477157 10.4124871,16.4477157 L89.6279479,16.4477157 C92.3509602,16.4477157 93.5525304,17.5690718 93.5525304,20.3724621 L93.5525304,66.0672322 C93.5525304,68.9106475 92.3509602,70.0320036 89.6279479,70.0320036 L10.4124871,70.0320036 Z M62.0745088,35.1902418 C63.996775,34.7096137 64.5974781,34.3091177 64.5974781,31.9863554 L64.5974781,24.0969196 C64.5974781,22.5750674 64.0769071,21.8942323 61.9143268,22.4549104 L50.2202195,25.3383507 C48.2178213,25.8189787 47.8174073,26.2194748 47.8174073,28.622287 L47.8174073,46.643953 C47.8174073,48.4060372 47.6571432,48.7665083 45.61472,49.3271863 L41.9703126,50.2883604 C38.2859622,51.2494525 35.2422579,53.4121148 35.2422579,57.2967542 C35.2422579,60.7408726 37.8053342,63.1837918 41.8502376,63.1837918 C47.5770932,63.1837918 51.4617327,59.0988634 51.4617327,53.2919578 L51.4617327,39.5154022 C51.4617327,38.0336571 51.7820967,37.673186 52.6632208,37.472979 L62.0745088,35.1902418 Z M29.9158983,90.0160428 L70.0841017,90.0160428 C71.886539,90.0160428 73.327603,88.5743227 73.327603,86.8121646 C73.327603,85.0100062 71.886539,83.5282283 70.0841017,83.5282283 L29.9158983,83.5282283 C28.1537321,83.5282283 26.6719869,85.0100062 26.6719869,86.8121646 C26.6719869,88.5743227 28.1537321,90.0160428 29.9158983,90.0160428 Z"/>
        </svg>
    ';
}
