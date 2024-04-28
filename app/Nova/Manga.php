<?php

namespace App\Nova;

use App\Enums\DayOfWeek;
use App\Enums\MediaCollection;
use App\Enums\SeasonOfYear;
use App\Nova\Actions\ScrapeManga;
use App\Nova\Actions\ScrapeNewManga;
use App\Nova\Actions\ScrapeTopManga;
use App\Nova\Actions\ScrapeUpcomingManga;
use App\Nova\Actions\UpdatePublishingManga;
use App\Nova\Filters\IsNsfw;
use App\Nova\Filters\StartedAtYear;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;

class Manga extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Manga::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Manga|null
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
        'id', 'mal_id', 'slug', 'original_title'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Manga';

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     *
     * @return array
     * @throws Exception
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make('Identification'),

            ID::make()->sortable(),

            Number::make('AniDB ID')
                ->hideFromIndex()
                ->help('Used to identify the Manga on <a target="_blank" href="https://anidb.net/manga/' . ($this->resource->anidb_id ?? 'slug-identifier') . '">AniDB</a>'),

            Number::make('AniList ID')
                ->hideFromIndex()
                ->help('Used to identify the Manga on <a target="_blank" href="https://anilist.co/manga/' . ($this->resource->anilist_id ?? 'slug-identifier') . '">AniList</a>'),

            Text::make('AnimePlanet ID')
                ->hideFromIndex()
                ->help('Used to identify the Manga on <a target="_blank" href="https://anime-planet.com/manga/' . ($this->resource->animeplanet_id ?? 'slug-identifier') . '">Anim-Planet</a>'),

            Number::make('AniSearch ID')
                ->hideFromIndex()
                ->help('Used to identify the Manga on <a target="_blank" href="https://anisearch.com/manga/' . ($this->resource->anisearch_id ?? 'slug-identifier') . '">AniSearch</a>'),

            Number::make('Kitsu ID')
                ->hideFromIndex()
                ->help('Used to identify the Manga on <a target="_blank" href="https://kitsu.io/manga/' . ($this->resource->kitsu_id ?? 'slug-identifier') . '">Kitsu</a>'),

            Number::make('MAL ID')
                ->hideFromIndex()
                ->help('Used to identify the Manga on <a target="_blank" href="https://myanimelist.net/manga/' . ($this->resource->mal_id ?? 'slug-identifier') . '">MyAnimeList</a>'),

            Heading::make('Media'),

            Avatar::make('Poster')
                ->thumbnail(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Poster());
                })->preview(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Poster());
                })
                ->squared()
                ->deletable(false)
                ->disableDownload()
                ->readonly()
                ->onlyOnPreview(),

            Images::make('Poster', MediaCollection::Poster)
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->original_title;
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

            Images::make('Banner', MediaCollection::Banner)
                ->hideFromIndex()
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->original_title;
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
                ->help('Used to identify the Manga in a URL: ' . config('app.url') . '/manga/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from title.'),

            Text::make('Title', 'original_title')
                ->sortable()
                ->required(),

            Code::make('Synonym Titles')
                ->json()
                ->sortable()
                ->help('Other names the manga is known by globally.')
                ->rules(['json'])
                ->nullable(),

            Text::make('Title Translations', 'title')
                ->hideFromIndex()
                ->rules('required')
                ->translatable(),

            Textarea::make('Synopsis Translations', 'synopsis')
                ->help('A short description of the Manga.')
                ->translatable(),

            Text::make('Tagline Translations', 'tagline')
                ->hideFromIndex()
                ->translatable(),

            BelongsTo::make('Source')
                ->sortable()
                ->help('The adaptation source of the manga. For example Light Novel, Game, Original, etc. If no source is available, especially for older anime, then choose Unknown.')
                ->required(),

            BelongsTo::make('Media Type', 'media_type')
                ->sortable()
                ->help('The general type of the manga. For example Manga, Manhwa, One-shot, etc.')
                ->required(),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->help('The TV rating of the manga. For example NR, G, PG-12, etc.')
                ->required(),

            BelongsTo::make('Status')
                ->sortable()
                ->help('The publication status of the manga such as.')
                ->required(),

            Boolean::make('Is NSFW')
                ->sortable()
                ->help('NSFW: Not Safe For Work (not suitable for watchers under the age of 18).'),

            Heading::make('Aggregates'),

            Number::make('Volume Count')
                ->help('The total number of volumes.'),

            Number::make('Chapter Count')
                ->help('The total number of chapters.'),

            Number::make('Page Count')
                ->help('The total number of pages.'),

            Heading::make('Schedule'),

            Date::make('Started at')
                ->displayUsing(function ($startedAt) {
                    return $startedAt?->format('Y-m-d');
                })
                ->hideFromIndex()
                ->help('The date on which the manga first published. For example: 2015-12-03'),

            Date::make('Ended at')
                ->displayUsing(function ($endedAt) {
                    return $endedAt?->format('Y-m-d');
                })
                ->hideFromIndex()
                ->help('The date on which the manga last published. For example: 2016-03-08'),

            Number::make('Duration')
                ->onlyOnForms()
                ->help('The average read-time in <b>seconds</b> of a single chapter. Usually 240 seconds.')
                ->required(),

            Text::make('Publication time')
                ->withMeta(['type' => 'time'])
                ->displayUsing(function ($time) {
                    return Carbon::parse($time)->format('h:i A');
                })
                ->hideFromIndex()
                ->help('The exact time the manga publishes at in JST timezone. For example: 1:30 PM (13:30)')
                ->nullable(),

            Select::make('Publication day')
                ->options(DayOfWeek::asSelectArray())
                ->displayUsing(function (?DayOfWeek $dayOfWeek) {
                    return $dayOfWeek?->key;
                })
                ->hideFromIndex()
                ->help('The day of the week the manga publishes at. For example: Thursday'),

            Select::make('Publication season')
                ->options(SeasonOfYear::asSelectArray())
                ->displayUsing(function (?SeasonOfYear $seasonOfYear) {
                    return $seasonOfYear?->key;
                })
                ->help('The season of the year the manga publishes in.<br />Jan-Mar: Winter<br />Apr-Jun: Spring<br />Jul-Sep: Summer<br />Oct-Dec: Fall'),

            Heading::make('Legal'),

            Text::make('Copyright')
                ->hideFromIndex()
                ->help('For example: © ' . date('Y') . ' Kurozora'),

            HasMany::make('Translations', 'manga_translations', MangaTranslation::class),

            MorphMany::make('Genres', 'mediaGenres', MediaGenre::class),

            MorphMany::make('Themes', 'mediaThemes', MediaTheme::class),

            MorphMany::make('Tags', 'mediaTags', MediaTag::class),

            HasMany::make('Cast', 'cast', MangaCast::class),

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
        $manga = $this->resource;

        return $manga->original_title . ' (ID: ' . $manga->id . ')';
    }

    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Manga';
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     *
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
     *
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [
            new Filters\SeasonOfYear,
            new StartedAtYear,
            new IsNsfw
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param NovaRequest $request
     *
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
     *
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [
            ScrapeNewManga::make()
                ->canSee(function ($request) {
                    return $request->user()->can('createManga');
                })
                ->confirmButtonText('Scrape New Manga')
                ->standalone(),
            ScrapeTopManga::make()
                ->canSee(function ($request) {
                    return $request->user()->hasRole('superAdmin');
                })
                ->standalone(),
            ScrapeUpcomingManga::make()
                ->canSee(function ($request) {
                    return $request->user()->hasRole('superAdmin');
                })
                ->standalone(),
            UpdatePublishingManga::make()
                ->canSee(function ($request) {
                    return $request->user()->hasRole('superAdmin');
                })
                ->standalone(),
            ScrapeManga::make()
                ->confirmText('Are you sure you want to scrape this manga?')
                ->confirmButtonText('Scrape Manga')
                ->canSee(function ($request) {
                    return $request->user()->can('updateManga');
                })->showInline(),
        ];
    }

    /**
     * Build a "relatable" query for media types.
     *
     * @param NovaRequest $request
     * @param Builder     $query
     *
     * @return Builder
     */
    public static function relatableMediaTypes(NovaRequest $request, Builder $query): Builder
    {
        return $query->where('type', 'manga');
    }

    /**
     * Build a "relatable" query for statuses.
     *
     * @param NovaRequest $request
     * @param Builder     $query
     *
     * @return Builder
     */
    public static function relatableStatuses(NovaRequest $request, Builder $query): Builder
    {
        return $query->where('type', 'manga');
    }
}
