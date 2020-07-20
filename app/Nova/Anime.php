<?php

namespace App\Nova;

use App\Enums\WatchRating;
use App\Nova\Actions\FetchAnimeActors;
use App\Nova\Actions\FetchAnimeDetails;
use App\Nova\Actions\FetchAnimeImages;
use App\Nova\Lenses\UnmoderatedAnime;
use Chaseconey\ExternalImage\ExternalImage;
use Illuminate\Support\Str;
use Laraning\NovaTimeField\TimeField as Time;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class Anime extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Anime';

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
            Heading::make('Identification')
                ->onlyOnForms(),

            ID::make()->sortable(),

            ExternalImage::make('Thumbnail', 'cached_poster_thumbnail')
                ->onlyOnIndex(),

            Number::make('AniDB', 'anidb_id')
                ->hideFromIndex()
                ->help('The ID of the Anime as noted on AniDB.'),

            Number::make('AniList', 'anilist_id')
                ->hideFromIndex()
                ->help('The ID of the Anime as noted on AniList.'),

            Number::make('Kitsu', 'kitsu_id')
                ->hideFromIndex()
                ->help('The ID of the Anime as noted on Kitsu.'),

            Text::make('IMDB ID', 'imdb_id')
                ->onlyOnForms()
                ->help('The ID of the Anime as noted on IMDB.'),

            Number::make('MAL ID', 'mal_id')
                ->hideFromIndex()
                ->help('The ID of the Anime as noted on MyAnimeList.'),

            Number::make('TVDB ID', 'tvdb_id')
                ->sortable()
                ->help('The ID of the Anime as noted on The TVDB.'),

            Heading::make('Basic information')
                ->onlyOnForms(),

            Text::make('Title')
                ->rules('required')
                ->sortable(),

            Text::make('Tagline')
                ->rules('max:255')
                ->hideFromIndex(),

            Text::make('Video URL', 'video_url')
                ->rules('max:255')
                ->hideFromIndex(),

            Text::make('Slug')
                ->rules('required')
                ->onlyOnForms()
                ->help('Used to identify the Anime in a URL: https://kurozora.app/anime/<strong>wolf-children</strong>'),

            Textarea::make('Synopsis')
                ->onlyOnForms()
                ->help('A short description of the Anime.'),

            Heading::make('Meta information')
                ->onlyOnForms(),

            Text::make('Network')
                ->onlyOnForms()
                ->help('The network that airs the Anime.'),

            Number::make('Runtime in minutes', 'runtime')
                ->onlyOnForms()
                ->help('For series: The average runtime in minutes of a single episode.<br />For movies: The amount of minutes the movie takes.'),

            Boolean::make('NSFW')
                ->sortable()
                ->help('NSFW: Not Safe For Work (not suitable for watchers under the age of 18).'),

            // Display moderation indicator on index
            Text::make('Moderated by', function() { return $this->displayModIndicatorForIndex(); })
                ->asHtml()
                ->readonly()
                ->onlyOnIndex(),

            Select::make('Watch rating')
                ->options(WatchRating::toSelectArray())
                ->displayUsingLabels()
                ->nullable()
                ->hideFromIndex()
                ->help('Use `TV-Y7 (FV)` if the show exhibits more \'fantasy violence\', and/or is generally more intense or combative than other shows.'),

            Heading::make('Schedule')
	            ->onlyOnForms(),

            Select::make('Air status', 'status')
	            ->options([
	            	0 => 'TBA',
		            1 => 'Ended',
		            2 => 'Continuing'
	            	])
	            ->displayUsingLabels()
	            ->hideFromIndex()
	            ->help('For example: Ended'),

            Date::make('First air date', 'first_aired')
	            ->format('DD-MM-YYYY')
	            ->hideFromIndex()
                ->help('The date on which the show first aired. For example: 2015-12-03'),

            Date::make('Last air date', 'last_aired')
                ->format('DD-MM-YYYY')
                ->hideFromIndex()
                ->help('The date on which the show last aired. For example: 2016-03-08'),

            Time::make('Air time', 'air_time')
	            ->withTwelveHourTime()
	            ->hideFromIndex()
	            ->help('The exact time the show airs at. For example: 1:30 PM (13:30)'),

            Select::make('Air day', 'air_day')
	            ->options([
                    0 => 'Sunday',
	            	1 => 'Monday',
		            2 => 'Tuesday',
		            3 => 'Wednesday',
		            4 => 'Thursday',
		            5 => 'Friday',
		            6 => 'Saturday'
                ])
	            ->displayUsingLabels()
	            ->hideFromIndex()
	            ->help('The day of the week the show airs at. For example: Thursday'),

            Heading::make('Legal')
                ->onlyOnForms(),

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

            BelongsToMany::make('Genres')
                ->searchable(),

            HasMany::make('Anime Images'),

            HasMany::make('Seasons'),

            HasMany::make('Actors'),
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

        if(!is_string($animeName) || !strlen($animeName))
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
        /** @var \App\Anime $anime */
        $anime = $this->resource;
        $modCount = $anime->moderators->count();

        // Return null when there are no mods to properly format the empty value
        if($modCount <= 0) return null;

        return '<span class="py-1 px-2 mr-1 inline-block rounded align-middle" style="background-color: #465161; color: #fff;">' . $modCount . ' ' . Str::plural('mod', $modCount) . '</span>';
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
