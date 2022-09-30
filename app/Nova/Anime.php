<?php

namespace App\Nova;

use App\Enums\DayOfWeek;
use App\Enums\SeasonOfYear;
use App\Nova\Actions\ScrapeAnime;
use App\Nova\Actions\ScrapeFiller;
use App\Nova\Lenses\UnmoderatedAnime;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laraning\NovaTimeField\TimeField as Time;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Ramsey\Uuid\Uuid;
use Timothyasp\Color\Color;

class Anime extends Resource
{
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
    public static $title = 'title';

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
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
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

            Text::make('Animix ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://animixplay.to/v1/' . ($this->resource->animix_id ?? 'slug-identifier') . '">AnimixPlay</a>'),

            Text::make('Filler ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://animefillerlist.com/shows/' . ($this->resource->filler_id ?? 'slug-identifier') . '">Anime Filler List</a>'),

            Text::make('IMDB ID')
                ->onlyOnForms()
                ->help('Used to identify the Anime on <a target="_blank" href="https://imdb.com/title/' . ($this->resource->imdb_id ?? 'slug-identifier') . '">IMDB</a>'),

            Number::make('Kitsu ID')
                ->hideFromIndex()
                ->help('Used to identify the Anime on <a target="_blank" href="https://kitsu.io/anime/' . ($this->resource->kitsu_id ?? 'slug-identifier') . '">Kitsu</a>'),

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

            Images::make('Poster')
                ->showStatistics()
                ->setFileName(function($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function($originalFilename, $model) {
                    return $this->resource->original_title;
                })
                ->customPropertiesFields([
                    Heading::make('Colors (automatically generated if empty)'),

                    Color::make('Background Color')
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

                    Heading::make('Dimensions (automatically generated if empty)'),

                    Number::make('Width')
                        ->help('The maximum width available for the image.'),

                    Number::make('Height')
                        ->help('The maximum height available for the image.'),
                ]),

            Images::make('Banner')
                ->hideFromIndex()
                ->showStatistics()
                ->setFileName(function($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function($originalFilename, $model) {
                    return $this->resource->original_title;
                })
                ->customPropertiesFields([
                    Heading::make('Colors (automatically generated if empty)'),

                    Color::make('Background Color')
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
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
                ->help('Other names the anime is known by globally.')
                ->rules(['nullable', 'json']),

            Text::make('Title Translations', 'title')
                ->hideFromIndex()
                ->rules('required')
                ->translatable(),

            Textarea::make('Synopsis Translations', 'synopsis')
                ->help('A short description of the Anime.')
                ->translatable(),

            Text::make('Tagline Translations', 'tagline')
                ->hideFromIndex()
                ->translatable(),

            BelongsTo::make('Source')
                ->sortable()
                ->help('The adaptation source of the anime. For example Manga, Game, Original, etc. If no source is available, especially for older anime, then choose Unknown.')
                ->required(),

            BelongsTo::make('Media Type')
                ->sortable()
                ->help('The general type of the anime. For example TV, Movie, Music, etc.')
                ->required(),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->help('The TV rating of the anime. For example NR, G, PG-12, etc.')
                ->required(),

            BelongsTo::make('Status')
                ->sortable()
                ->help('The airing status of the anime such as.')
                ->required(),

            Text::make('Video URL', 'video_url')
                ->rules('max:255')
                ->hideFromIndex(),

            Boolean::make('Is NSFW')
                ->sortable()
                ->help('NSFW: Not Safe For Work (not suitable for watchers under the age of 18).'),

            // Display moderation indicator on index
            Text::make('Moderated by', function() { return $this->displayModIndicatorForIndex(); })
                ->asHtml()
                ->readonly()
                ->onlyOnIndex(),

            Heading::make('Aggregates'),

            Number::make('Season Count')
                ->help('The total number of seasons.'),

            Number::make('Episode Count')
                ->help('The total number of episodes.'),

            Heading::make('Schedule'),

            Date::make('First aired')
                ->format('DD-MM-YYYY')
                ->hideFromIndex()
                ->help('The date on which the show first aired. For example: 2015-12-03'),

            Date::make('Last aired')
                ->format('DD-MM-YYYY')
                ->hideFromIndex()
                ->help('The date on which the show last aired. For example: 2016-03-08'),

            Number::make('Duration')
                ->onlyOnForms()
                ->help('For series: The average runtime in <b>seconds</b> of a single episode.<br />For movies: The total amount of seconds the movie takes.')
                ->required(),

            Time::make('Air time')
                ->withTwelveHourTime()
                ->hideFromIndex()
                ->help('The exact time the show airs at in JST timezone. For example: 1:30 PM (13:30)')
                ->nullable(),

            Select::make('Air day')
                ->options(DayOfWeek::asSelectArray())
                ->displayUsingLabels()
                ->hideFromIndex()
                ->help('The day of the week the show airs at. For example: Thursday'),

            Select::make('Air season')
                ->options(SeasonOfYear::asSelectArray())
                ->displayUsingLabels()
                ->help('The season of the year the show airs in.<br />Jan-Mar: Winter<br />Apr-Jun: Spring<br />Jul-Sep: Summer<br />Oct-Dec: Fall'),

            Heading::make('Legal'),

            Text::make('Copyright')
                ->hideFromIndex()
                ->help('For example: © ' . date('Y') . ' Redark'),

            MorphMany::make('Videos'),

            HasMany::make('Translations', 'anime_translations', AnimeTranslation::class),

            HasMany::make('Genres', 'media_genres', MediaGenre::class),

            HasMany::make('Themes', 'media_themes', MediaTheme::class),

            HasMany::make('Seasons'),

            HasMany::make('Cast'),

            HasMany::make('Songs', 'anime_songs', AnimeSong::class),

            MorphMany::make('Relations', 'relations', MediaRelation::class),

            HasMany::make('Staff', 'staff', AnimeStaff::class),

            HasMany::make('Studios', 'anime_studios', AnimeStudio::class),

            HasOne::make('Stats', 'stats', MediaStat::class),

            BelongsToMany::make('Moderators', 'moderators', User::class)
                // @TODO
                // This has been commented out, because it conflicts with the favoriteAnime relationship.
                //                ->fields(function() {
                //                    return [
                //                        DateTime::make('Moderating since', 'created_at')
                //                            ->rules('required')
                //                    ];
                //                })
                ->searchable(),
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
        return [
            new UnmoderatedAnime
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request): array
    {
        return [
            (new ScrapeAnime)
                ->confirmText('Are you sure you want to scrape this anime?')
                ->confirmButtonText('Scrape Anime')
                ->canSee(function ($request) {
                    return $request->user()->can('updateAnime');
                }),
            (new ScrapeFiller)
                ->confirmText('Are you sure you want to scrape this anime’s filler list?')
                ->confirmButtonText('Scrape Fillers')
                ->canSee(function ($request) {
                    return $request->user()->can('updateAnime') && $request->user()->can('updateEpisode');
                }),
//            (new ScrapeEpisodes)
//                ->confirmText('Are you sure you want to scrape episodes for this anime?')
//                ->confirmButtonText('Scrape Episodes')
//                ->canSee(function ($request) {
//                    return $request->user()->can('updateAnime');
//                }),
        ];
    }

    /**
     * Returns an indication of whether the Anime is moderated.
     *
     * @return ?string
     */
    private function displayModIndicatorForIndex(): ?string
    {
        // Get the anime and moderator count
        /** @var \App\Models\Anime $anime */
        $anime = $this->resource;
        $modCount = $anime->moderators->count();

        // Return null when there are no mods to properly format the empty value
        if ($modCount <= 0) {
            return null;
        }

        return '<span class="py-1 px-2 mr-1 inline-block rounded align-middle" style="background-color: #465161; color: #fff;">' . $modCount . ' ' . str('mod')->plural($modCount) . '</span>';
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
        return $query->where('type', 'anime');
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
        return $query->where('type', 'anime');
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
