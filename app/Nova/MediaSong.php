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

class MediaSong extends Resource
{
    use HasSortableRows {
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
     * @param             $resource
     *
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
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = [
        'song',
        'song.translations',
        'model'
    ];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'type', 'song.id', 'song.original_title', 'song.artist', 'song.translations.title', 'model.id', 'model.original_title'
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
     *
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification'),

            ID::make()->sortable(),

            Heading::make('Meta information'),

            MorphTo::make('Model')
                ->types([
                    Anime::class,
                    Game::class,
                ])
                ->searchable()
                ->required(),

            BelongsTo::make('Song')
                ->searchable()
                ->sortable()
                ->required(),

            Select::make('Type')
                ->options(SongType::asSelectArray())
                ->displayUsing(function (SongType $songType) {
                    return $songType->key;
                })
                ->required()
                ->sortable()
                ->help('Choose the type depending on when the song is played.'),

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

        return $mediaSong->song->original_title . ' | ' . $mediaSong->model?->original_title . ' (ID: ' . $mediaSong->id . ')';
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
        return $request->user()->can('viewMediaSong');
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

    /**
     * Build an "index" query for the given resource.
     *
     * @param NovaRequest $request
     * @param Builder     $query
     *
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return parent::indexQuery($request, static::indexSortableQuery($request, $query));
    }
}
