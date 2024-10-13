<?php

namespace App\Nova;

use App\Enums\DayOfWeek;
use App\Enums\MediaCollection;
use App\Enums\SeasonOfYear;
use App\Nova\Actions\FixAnimeAiringSeason;
use App\Nova\Actions\ScrapeAnime;
use App\Nova\Actions\ScrapeAnimeBanner;
use App\Nova\Actions\ScrapeAnimeSeason;
use App\Nova\Actions\ScrapeFiller;
use App\Nova\Actions\ScrapeNewAnime;
use App\Nova\Actions\ScrapeTopAnime;
use App\Nova\Actions\ScrapeUpcomingAnime;
use App\Nova\Actions\UpdateAiringAnime;
use App\Nova\Filters\IsNsfw;
use App\Nova\Filters\StartedAtYear;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Kiritokatklian\NovaAstrotranslatable\HandlesTranslatable;
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

class Anime extends Resource
{
    use HandlesTranslatable;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Anime::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Anime|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'original_title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'tvdb_id', 'mal_id', 'slug', 'original_title'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Anime';

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
                ->help('Used to identify the Anime on <a target="_blank" href="https://anidb.net/anime/' . ($this->resource->anidb_id ?? 'slug-identifier') . '">AniDB</a>'),

            Number::make('AniList ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://anilist.co/anime/' . ($this->resource->anilist_id ?? 'slug-identifier') . '">AniList</a>'),

            Text::make('AnimePlanet ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://anime-planet.com/anime/' . ($this->resource->animeplanet_id ?? 'slug-identifier') . '">Anim-Planet</a>'),

            Number::make('AniSearch ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://anisearch.com/anime/' . ($this->resource->anisearch_id ?? 'slug-identifier') . '">AniSearch</a>'),

            Text::make('Filler ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://animefillerlist.com/shows/' . ($this->resource->filler_id ?? 'slug-identifier') . '">Anime Filler List</a>'),

            Text::make('IMDB ID')
                ->onlyOnForms()
                ->help('Used to identify the Anime on <a target="_blank" href="https://imdb.com/title/' . ($this->resource->imdb_id ?? 'slug-identifier') . '">IMDB</a>'),

            Number::make('Kitsu ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://kitsu.io/anime/' . ($this->resource->kitsu_id ?? 'slug-identifier') . '">Kitsu</a>'),

            Number::make('LiveChart ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://livechart.me/anime/' . ($this->resource->livechart_id ?? 'slug-identifier') . '">LiveChart</a>'),

            Number::make('MAL ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://myanimelist.net/anime/' . ($this->resource->mal_id ?? 'slug-identifier') . '">MyAnimeList</a>'),

            Text::make('Notify ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://notify.moe/anime/' . ($this->resource->notify_id ?? 'slug-identifier') . '">Notify</a>'),

            Number::make('Syoboi ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://cal.syoboi.jp/tid/' . ($this->resource->syoboi_id ?? 'slug-identifier') . '">Syoboi</a>'),

            Number::make('Trakt ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://trakt.tv/shows/' . ($this->resource->trakt_id ?? 'slug-identifier') . '">Trakt.tv</a>'),

            Number::make('TVDB ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://thetvdb.com/series/' . ($this->resource->tvdb_id ?? 'slug-identifier') . '">TheTVDB</a>'),

            Heading::make('Media'),

            Avatar::make('Poster')
                ->thumbnail(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Poster());
                })->preview(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Poster());
                })
                ->rounded()
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
                ->help('Used to identify the Anime in a URL: ' . config('app.url') . '/anime/<strong>' . ($this->resource->slug ?? 'slug-identifier') . '</strong>. Leave empty to auto-generate from title.'),

            Text::make('Title', 'original_title')
                ->sortable()
                ->required(),

            Code::make('Synonym Titles')
                ->json()
                ->sortable()
                ->rules(['json'])
                ->nullable()
                ->help('Other names the anime is known by globally.'),

            Text::make('Title Translations', 'title')
                ->hideFromIndex()
                ->required()
                ->translatable(),

            Textarea::make('Synopsis Translations', 'synopsis')
                ->help('A short description of the Anime.')
                ->nullable()
                ->translatable(),

            Text::make('Tagline Translations', 'tagline')
                ->hideFromIndex()
                ->nullable()
                ->translatable(),

            BelongsTo::make('Source')
                ->sortable()
                ->required()
                ->help('The adaptation source of the anime. For example Manga, Game, Original, etc. If no source is available, especially for older anime, then choose Unknown.'),

            BelongsTo::make('Media Type', 'media_type')
                ->sortable()
                ->required()
                ->help('The general type of the anime. For example TV, Movie, Music, etc.'),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->required()
                ->help('The TV rating of the anime. For example NR, G, PG-12, etc.'),

            BelongsTo::make('Country of Origin', 'country_of_origin', Country::class)
                ->sortable()
                ->required()
                ->help('The country the anime originated from. For example Japan, Korea, China and the US.'),

            BelongsTo::make('Status')
                ->sortable()
                ->required()
                ->help('The airing status of the anime such as.'),

            Text::make('Video URL', 'video_url')
                ->rules('max:255')
                ->hideFromIndex(),

            Boolean::make('Is NSFW')
                ->sortable()
                ->help('NSFW: Not Safe For Work (not suitable for watchers under the age of 18).'),

            Heading::make('Aggregates'),

            Number::make('Season Count')
                ->default(1)
                ->help('The total number of seasons.'),

            Number::make('Episode Count')
                ->default(12)
                ->help('The total number of episodes.'),

            Heading::make('Schedule'),

            Date::make('Started At')
                ->displayUsing(function ($startedAt) {
                    return $startedAt?->format('Y-m-d');
                })
                ->hideFromIndex()
                ->help('The date on which the show started. For example: 2015-12-03'),

            Date::make('Ended At')
                ->displayUsing(function ($endedAt) {
                    return $endedAt?->format('Y-m-d');
                })
                ->hideFromIndex()
                ->help('The date on which the show ended. For example: 2016-03-08'),

            Number::make('Duration')
                ->onlyOnForms()
                ->required()
                ->help('For series: The average runtime in <b>seconds</b> of a single episode.<br />For movies: The total amount of seconds the movie takes.'),

            Text::make('Air time')
                ->withMeta(['type' => 'time'])
                ->displayUsing(function ($time) {
                    return Carbon::parse($time)->format('h:i A');
                })
                ->hideFromIndex()
                ->nullable()
                ->help('The exact time the show airs at in JST timezone. For example: 1:30 PM (13:30)'),

            Select::make('Air day')
                ->options(DayOfWeek::asSelectArray())
                ->displayUsing(function (?DayOfWeek $dayOfWeek) {
                    return $dayOfWeek?->key;
                })
                ->hideFromIndex()
                ->help('The day of the week the show airs at. For example: Thursday'),

            Select::make('Air season')
                ->options(SeasonOfYear::asSelectArray())
                ->displayUsing(function (?SeasonOfYear $seasonOfYear) {
                    return $seasonOfYear?->key;
                })
                ->help('The season of the year the show airs in.<br />Jan-Mar: Winter<br />Apr-Jun: Spring<br />Jul-Sep: Summer<br />Oct-Dec: Fall'),

            Heading::make('Legal'),

            Textarea::make('Copyright')
                ->hideFromIndex()
                ->help('For example: © ' . date('Y') . ' Kurozora'),

            HasMany::make('Translations', 'anime_translations', AnimeTranslation::class),

            MorphMany::make('Videos'),

            MorphMany::make('Genres', 'mediaGenres', MediaGenre::class),

            MorphMany::make('Themes', 'mediaThemes', MediaTheme::class),

            MorphMany::make('Tags', 'mediaTags', MediaTag::class),

            HasMany::make('Seasons'),

            HasMany::make('Cast', 'cast', AnimeCast::class),

            HasMany::make('Songs', 'mediaSongs', MediaSong::class),

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
        $anime = $this->resource;

        return $anime->original_title . ' (ID: ' . $anime->id . ')';
    }

    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Anime';
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
            ScrapeNewAnime::make()
                ->canSee(function ($request) {
                    return $request->user()->can('createAnime');
                })
                ->confirmButtonText('Scrape New Anime')
                ->standalone(),
            ScrapeAnimeSeason::make()
                ->canSee(function ($request) {
                    return $request->user()->hasRole('superAdmin');
                })
                ->standalone(),
            ScrapeTopAnime::make()
                ->canSee(function ($request) {
                    return $request->user()->hasRole('superAdmin');
                })
                ->standalone(),
            ScrapeUpcomingAnime::make()
                ->canSee(function ($request) {
                    return $request->user()->hasRole('superAdmin');
                })
                ->standalone(),
            FixAnimeAiringSeason::make()
                ->canSee(function ($request) {
                    return $request->user()->hasRole('superAdmin');
                })
                ->standalone(),
            UpdateAiringAnime::make()
                ->canSee(function ($request) {
                    return $request->user()->hasRole('superAdmin');
                })
                ->confirmButtonText('Update Airing Anime')
                ->standalone(),
            ScrapeAnime::make()
                ->confirmText('Are you sure you want to scrape this anime?')
                ->confirmButtonText('Scrape Anime')
                ->canSee(function ($request) {
                    return $request->user()->can('updateAnime');
                })->showInline(),
            ScrapeAnimeBanner::make()
                ->confirmText('Are you sure you want to scrape this anime’s banner?')
                ->confirmButtonText('Scrape Anime Banner')
                ->canSee(function ($request) {
                    return $request->user()->can('updateAnime');
                })->showInline(),
            ScrapeFiller::make()
                ->confirmText('Are you sure you want to scrape this anime’s filler list?')
                ->confirmButtonText('Scrape Fillers')
                ->canSee(function ($request) {
                    return $request->user()->can('updateAnime') && $request->user()->can('updateEpisode');
                }),
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
        return $query->where('type', 'anime');
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
        return $query->where('type', 'anime');
    }
}
