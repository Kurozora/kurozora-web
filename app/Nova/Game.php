<?php

namespace App\Nova;

use App\Enums\DayOfWeek;
use App\Enums\MediaCollection;
use App\Enums\SeasonOfYear;
use App\Nova\Actions\ScrapeGame;
use App\Nova\Actions\ScrapeNewGame;
use App\Nova\Actions\ScrapeTopGame;
use App\Nova\Actions\ScrapeUpcomingGame;
use App\Nova\Filters\IsNsfw;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ramsey\Uuid\Uuid;

class Game extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Game::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Game|null
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
        'id', 'slug', 'original_title'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Game';

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     * @throws Exception
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make('Identification'),

            ID::make()->sortable(),

            Text::make('Series ID')
                ->help('The ID of the franchise.'),

            BelongsTo::make('Parent', 'parent', Game::class)
                ->searchable()
                ->nullable()
                ->help('The ID of the parent game. Usually exists when the game is a Mod or a DLC of another game.'),

            Number::make('IGDB ID')
                ->hideFromIndex()
                ->help('Used to identify the Game on <a target="_blank" href="https://igdb.com/games/' . ($this->resource->igdb_id ?? 'slug-identifier') . '">IGDB</a>'),

            Text::make('IGDB Slug')
                ->hideFromIndex()
                ->help('Used to identify the Game on <a target="_blank" href="https://igdb.com/games/' . ($this->resource->igdb_slug ?? 'slug-identifier') . '">IGDB</a>'),

            Heading::make('Media'),

            Avatar::make('Poster')
                ->thumbnail(function () {
                    return  $this->resource->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp');
                })->preview(function () {
                    return $this->resource->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp');
                })
                ->rounded()
                ->deletable(false)
                ->disableDownload()
                ->readonly()
                ->onlyOnPreview(),

            Images::make('Poster', MediaCollection::Poster)
                ->showStatistics()
                ->setFileName(function($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function($originalFilename, $model) {
                    return $this->resource->original_title;
                }),
//                ->customPropertiesFields([
//                    Heading::make('Colors (automatically generated if empty)'),
//
//                    Color::make('Background Color')
//                        ->slider()
//                        ->help('The average background color of the image.'),
//
//                    Color::make('Text Color 1')
//                        ->slider()
//                        ->help('The primary text color that may be used if the background color is displayed.'),
//
//                    Color::make('Text Color 2')
//                        ->slider()
//                        ->help('The secondary text color that may be used if the background color is displayed.'),
//
//                    Color::make('Text Color 3')
//                        ->slider()
//                        ->help('The tertiary text color that may be used if the background color is displayed.'),
//
//                    Color::make('Text Color 4')
//                        ->slider()
//                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),
//
//                    Heading::make('Dimensions (automatically generated if empty)'),
//
//                    Number::make('Width')
//                        ->help('The maximum width available for the image.'),
//
//                    Number::make('Height')
//                        ->help('The maximum height available for the image.'),
//                ]),

            Images::make('Banner', MediaCollection::Banner)
                ->hideFromIndex()
                ->showStatistics()
                ->setFileName(function($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function($originalFilename, $model) {
                    return $this->resource->original_title;
                }),
//                ->customPropertiesFields([
//                    Heading::make('Colors (automatically generated if empty)'),
//
//                    Color::make('Background Color')
//                        ->slider()
//                        ->help('The average background color of the image.'),
//
//                    Color::make('Text Color 1')
//                        ->slider()
//                        ->help('The primary text color that may be used if the background color is displayed.'),
//
//                    Color::make('Text Color 2')
//                        ->slider()
//                        ->help('The secondary text color that may be used if the background color is displayed.'),
//
//                    Color::make('Text Color 3')
//                        ->slider()
//                        ->help('The tertiary text color that may be used if the background color is displayed.'),
//
//                    Color::make('Text Color 4')
//                        ->slider()
//                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),
//
//                    Heading::make('Dimensions (automatically generated if empty)'),
//
//                    Number::make('Width')
//                        ->help('The maximum width available for the image.'),
//
//                    Number::make('Height')
//                        ->help('The maximum height available for the image.'),
//                ]),

            Heading::make('Meta information'),

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the Game in a URL: ' . config('app.url') . '/games/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from title.'),

            Text::make('Title', 'original_title')
                ->sortable()
                ->required(),

            Code::make('Synonym Titles')
                ->json()
                ->sortable()
                ->help('Other names the game is known by globally.')
                ->rules(['json'])
                ->nullable(),

            Text::make('Title Translations', 'title')
                ->hideFromIndex()
                ->rules('required')
                ->translatable(),

            Textarea::make('Synopsis Translations', 'synopsis')
                ->help('A short description of the Game.')
                ->translatable(),

            Text::make('Tagline Translations', 'tagline')
                ->hideFromIndex()
                ->translatable(),

            BelongsTo::make('Source')
                ->sortable()
                ->help('The adaptation source of the game. For example Light Novel, Game, Original, etc. If no source is available, especially for older anime, then choose Unknown.')
                ->required(),

            BelongsTo::make('Media Type', 'media_type')
                ->sortable()
                ->help('The general type of the game. For example DLC, Mod, etc.')
                ->required(),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->help('The TV rating of the game. For example NR, G, PG-12, etc.')
                ->required(),

            BelongsTo::make('Status')
                ->sortable()
                ->help('The publication status of the game such as.')
                ->required(),

            Boolean::make('Is NSFW')
                ->sortable()
                ->help('NSFW: Not Safe For Work (not suitable for watchers under the age of 18).'),

            Heading::make('Aggregates'),

            Number::make('Edition Count')
                ->help('The total number of editions.'),

            Heading::make('Schedule'),

            Date::make('Published at')
                ->displayUsing(function ($publishedAt) {
                    return $publishedAt?->format('Y-m-d');
                })
                ->hideFromIndex()
                ->help('The date on which the game first published. For example: 2015-12-03'),

            Number::make('Duration')
                ->onlyOnForms()
                ->help('The average play-time in <b>seconds</b>.')
                ->required(),

            Select::make('Publication day')
                ->options(DayOfWeek::asSelectArray())
                ->displayUsing(function (?DayOfWeek $dayOfWeek) {
                    return $dayOfWeek?->key;
                })
                ->hideFromIndex()
                ->help('The day of the week the game publishes at. For example: Thursday'),

            Select::make('Publication season')
                ->options(SeasonOfYear::asSelectArray())
                ->displayUsing(function (?SeasonOfYear $seasonOfYear) {
                    return $seasonOfYear?->key;
                })
                ->help('The season of the year the game publishes in.<br />Jan-Mar: Winter<br />Apr-Jun: Spring<br />Jul-Sep: Summer<br />Oct-Dec: Fall'),

            Heading::make('Legal'),

            Text::make('Copyright')
                ->hideFromIndex()
                ->help('For example: © ' . date('Y') . ' Kurozora'),

            HasMany::make('Translations', 'game_translations', GameTranslation::class),

            MorphMany::make('Genres', 'mediaGenres', MediaGenre::class),

            MorphMany::make('Themes', 'mediaThemes', MediaTheme::class),

            MorphMany::make('Tags', 'mediaTags', MediaTag::class),

//            HasMany::make('Cast', 'cast', GameCast::class),

            MorphMany::make('Relations', 'mediaRelations', MediaRelation::class),

            MorphMany::make('Staff', 'mediaStaff', MediaStaff::class),

            MorphMany::make('Studios', 'mediaStudios', MediaStudio::class),

            MorphOne::make('Stats', 'mediaStat', MediaStat::class),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $game = $this->resource;

        return $game->original_title . ' (ID: ' . $game->id . ')';
    }

    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Game';
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [
            new IsNsfw
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [
//            ScrapeNewGame::make()
//                ->canSee(function ($request) {
//                    return $request->user()->can('createGame');
//                })
//                ->confirmButtonText('Scrape New Game')
//                ->standalone(),
//            ScrapeTopGame::make()
//                ->canSee(function ($request) {
//                    return $request->user()->hasRole('superAdmin');
//                })
//                ->standalone(),
//            ScrapeUpcomingGame::make()
//                ->canSee(function ($request) {
//                    return $request->user()->hasRole('superAdmin');
//                })
//                ->standalone(),
//            ScrapeGame::make()
//                ->confirmText('Are you sure you want to scrape this game?')
//                ->confirmButtonText('Scrape Game')
//                ->canSee(function ($request) {
//                    return $request->user()->can('updateGame');
//                })->showInline(),
        ];
    }

    /**
     * Build a "relatable" query for media types.
     *
     * @param NovaRequest $request
     * @param Builder $query
     * @return Builder
     */
    public static function relatableMediaTypes(NovaRequest $request, Builder $query): Builder
    {
        return $query->where('type', 'game');
    }

    /**
     * Build a "relatable" query for statuses.
     *
     * @param NovaRequest $request
     * @param Builder $query
     * @return Builder
     */
    public static function relatableStatuses(NovaRequest $request, Builder $query): Builder
    {
        return $query->where('type', 'game');
    }

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M82.5,82.5 L17.5,82.5 C16.1192881,82.5 15,83.6192881 15,85 L15,87.5 C15,88.8807119 16.1192881,90 17.5,90 L82.5,90 C83.8807119,90 85,88.8807119 85,87.5 L85,85 C85,83.6192881 83.8807119,82.5 82.5,82.5 Z M92.5,10 L7.5,10 C3.35786438,10 0,13.3578644 0,17.5 L0,67.5 C0,71.6421356 3.35786438,75 7.5,75 L92.5,75 C96.6421356,75 100,71.6421356 100,67.5 L100,17.5 C100,13.3578644 96.6421356,10 92.5,10 Z M92.5,67.5 L7.5,67.5 L7.5,17.5 L92.5,17.5 L92.5,67.5 Z"/>
        </svg>
    ';
}
