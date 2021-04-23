<?php

namespace App\Nova;

use App\Enums\AnimeSource;
use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use App\Enums\DayOfWeek;
use App\Nova\Actions\FetchAnimeActors;
use App\Nova\Actions\FetchAnimeDetails;
use App\Nova\Actions\FetchAnimeImages;
use App\Nova\Lenses\UnmoderatedAnime;
use Chaseconey\ExternalImage\ExternalImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laraning\NovaTimeField\TimeField as Time;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Anime extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Anime';

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
        'id', 'title', 'tvdb_id', 'mal_id'
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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Heading::make('Identification'),

            ID::make()->sortable(),

            ExternalImage::make('Thumbnail', 'cached_poster_thumbnail')
                ->onlyOnIndex(),

            Number::make('AniDB ID')
                ->hideFromIndex()
                ->help('The ID of the Anime as noted on AniDB.'),

            Number::make('AniList ID')
                ->hideFromIndex()
                ->help('The ID of the Anime as noted on AniList.'),

            Text::make('IMDB ID')
                ->onlyOnForms()
                ->help('The ID of the Anime as noted on IMDB.'),

            Number::make('Kitsu ID')
                ->hideFromIndex()
                ->help('The ID of the Anime as noted on Kitsu.'),

            Number::make('MAL ID')
                ->hideFromIndex()
                ->help('The ID of the Anime as noted on MyAnimeList.'),

            Number::make('TVDB ID')
                ->hideFromIndex()
                ->help('The ID of the Anime as noted on The TVDB.'),

            Heading::make('Meta information'),

            Text::make('Slug')
                ->onlyOnForms()
                ->help('Used to identify the Anime in a URL: https://kurozora.app/anime/<strong>wolf-children</strong>. Leave empty to auto-generate from title.'),

            Text::make('Title')
                ->rules('required')
                ->sortable(),

            Text::make('Tagline')
                ->rules('max:255')
                ->hideFromIndex(),

            Textarea::make('Synopsis')
                ->hideFromIndex()
                ->help('A short description of the Anime.'),

            Select::make('Type')
                ->options(AnimeType::asSelectArray())
                ->displayUsingLabels()
                ->required()
                ->help('The general type of the anime, such as TV, Movie, or Music.'),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->required(),

            Select::make('Adaptation Source')
                ->options(AnimeSource::asSelectArray())
                ->displayUsingLabels()
                ->sortable()
                ->required()
                ->help('The adaptation source of the anime. For example `Manga`, `Game` or `Original` if not adapted from other sources. If no source is available, especially for older anime, then choose `Unknown`.'),

            Text::make('Video URL', 'video_url')
                ->rules('max:255')
                ->hideFromIndex(),

            Boolean::make('Is NSFW')
                ->sortable()
                ->help('NSFW: Not Safe For Work (not suitable for watchers under the age of 18).'),

            Heading::make('Production'),

            BelongsToMany::make('Studios')
                ->searchable(),

            Text::make('Network')
                ->hideFromIndex()
                ->help('The network that airs the Anime.'),

            Text::make('Producer')
                ->hideFromIndex()
                ->help('The producer that produces the Anime.'),

            // Display moderation indicator on index
            Text::make('Moderated by', function() { return $this->displayModIndicatorForIndex(); })
                ->asHtml()
                ->readonly()
                ->onlyOnIndex(),

            Heading::make('Schedule'),

            Date::make('First aired')
                ->format('DD-MM-YYYY')
                ->hideFromIndex()
                ->help('The date on which the show first aired. For example: 2015-12-03'),

            Date::make('Last aired')
                ->format('DD-MM-YYYY')
                ->hideFromIndex()
                ->help('The date on which the show last aired. For example: 2016-03-08'),

            Number::make('Runtime')
                ->onlyOnForms()
                ->help('For series: The average runtime in minutes of a single episode.<br />For movies: The amount of minutes the movie takes.'),

            Select::make('Air status')
                ->options(AnimeStatus::asSelectArray())
                ->displayUsingLabels()
                ->hideFromIndex()
                ->help('For example: Ended'),

            Time::make('Air time')
                ->withTwelveHourTime()
                ->hideFromIndex()
                ->help('The exact time the show airs at. For example: 1:30 PM (13:30)'),

            Select::make('Air day')
                ->options(DayOfWeek::asSelectArray())
                ->displayUsingLabels()
                ->hideFromIndex()
                ->help('The day of the week the show airs at. For example: Thursday'),

            Heading::make('Legal'),

            Text::make('Copyright')
                ->hideFromIndex()
                ->help('For example: © 2020 Kurozora B.V.'),

            Heading::make('Flags')
                ->onlyOnForms(),

            Boolean::make('Actors Fetched?', 'fetched_actors')
                ->onlyOnForms()
                ->help('Whether or not the actors were retrieved from TVDB.<br />Untick and the system will do so once the Anime gets visited the next time.'),

            Boolean::make('Base Episodes Fetched?', 'fetched_base_episodes')
                ->onlyOnForms()
                ->help('Whether or not the base episodes were retrieved from TVDB.<br />Untick and the system will do so once the Anime gets visited the next time.'),

            Boolean::make('Images Fetched?', 'fetched_images')
                ->onlyOnForms()
                ->help('Whether or not the images were retrieved from TVDB.<br />Untick and the system will do so once the Anime gets visited the next time.'),

            Boolean::make('Details Fetched?', 'fetched_details')
                ->onlyOnForms()
                ->help('Whether or not the details (information_ were retrieved from TVDB.<br />Untick and the system will do so once the Anime gets visited the next time.'),

            HasMany::make('Cast', 'actor_character_anime')
                ->sortable(),

            HasMany::make('Actors'),

            HasMany::make('Characters'),

            HasMany::make('Anime Images'),

            BelongsToMany::make('Genres')
                ->searchable(),

            HasMany::make('Anime Relations'),

            HasMany::make('Seasons'),

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
    public function title()
    {
        $animeName = $this->title;

        if (!is_string($animeName) || !strlen($animeName))
            $animeName = 'No Anime title';

        return $animeName . ' (ID: ' . $this->id . ')';
    }

    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label() {
        return 'Anime';
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [
            new UnmoderatedAnime
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new FetchAnimeImages,
            new FetchAnimeDetails,
            new FetchAnimeActors
        ];
    }

    /**
     * Returns an indication of whether the Anime is moderated.
     *
     * @return string|null
     */
    private function displayModIndicatorForIndex()
    {
        // Get the anime and moderator count
        /** @var \App\Models\Anime $anime */
        $anime = $this->resource;
        $modCount = $anime->moderators->count();

        // Return null when there are no mods to properly format the empty value
        if ($modCount <= 0) return null;

        return '<span class="py-1 px-2 mr-1 inline-block rounded align-middle" style="background-color: #465161; color: #fff;">' . $modCount . ' ' . Str::plural('mod', $modCount) . '</span>';
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return parent::indexQuery($request, $query)->withoutGlobalScope('tv_rating');
    }

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static $icon = '
        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
            <path fill="var(--sidebar-icon)" d="M528 464H112a16 16 0 0 0-16 16v16a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16v-16a16 16 0 0 0-16-16zM592 0H48A48 48 0 0 0 0 48v320a48 48 0 0 0 48 48h544a48 48 0 0 0 48-48V48a48 48 0 0 0-48-48zm0 368H48V48h544z"/>
        </svg>
    ';
}
