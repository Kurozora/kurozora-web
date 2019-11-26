<?php

namespace App\Nova;

use App\Nova\Actions\FetchAnimeActors;
use App\Nova\Actions\FetchAnimeDetails;
use App\Nova\Actions\FetchAnimeImages;
use Chaseconey\ExternalImage\ExternalImage;
use Laraning\NovaTimeField\TimeField as Time;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
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
            ID::make()->sortable(),

            Heading::make('Basic information')
                ->onlyOnForms(),

            ExternalImage::make('Thumbnail', 'cached_poster_thumbnail')
                ->onlyOnIndex(),

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

            Number::make('TVDB ID', 'tvdb_id')
	            ->sortable()
                ->help('The ID of the Anime as noted on The TVDB.'),

            Number::make('MAL ID', 'mal_id')
                ->hideFromIndex()
                ->help('The ID of the Anime as noted on MyAnimeList.'),

            Text::make('IMDB ID', 'imdb_id')
                ->onlyOnForms()
                ->help('The ID of the Anime as noted on IMDB.'),

            Number::make('Runtime in minutes', 'runtime')
                ->onlyOnForms()
                ->help('For series: The average runtime in minutes of a single episode.<br />For movies: The amount of minutes the movie takes.'),

            Boolean::make('NSFW')
                ->sortable()
                ->help('NSFW: Not Safe For Work (not suitable for watchers under the age of 18).'),

            Text::make('Watch rating', 'watch_rating')
                ->onlyOnForms()
                ->help('for example: TV-PG.'),

            Heading::make('Schedule')
	            ->onlyOnForms(),

            Select::make('Air status', 'status')
	            ->options([
	            	0 => 'TBA',
		            1 => 'Ended',
		            2 => 'Continuing'
	            	])
	            ->onlyOnForms()
	            ->help('For example: Ended'),

            Date::make('First air date', 'first_aired')
	            ->format('DD-MM-YYYY')
	            ->onlyOnForms()
                ->help('The date on which the show first aired. For example: 2015-12-03'),

            Time::make('Air time', 'air_time')
	            ->withTwelveHourTime()
	            ->onlyOnForms()
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
	            ->onlyOnForms()
	            ->help('The day of the week the show airs at. For example: Thursday'),

            Heading::make('Images')
                ->onlyOnForms(),

            ExternalImage::make('Poster image URL', 'cached_poster')
                ->onlyOnForms(),

            ExternalImage::make('Poster Thumbnail image URL', 'cached_poster_thumbnail')
                ->onlyOnForms(),

            ExternalImage::make('Banner image URL', 'cached_background')
                ->onlyOnForms(),

            ExternalImage::make('Banner Thumbnail image URL', 'cached_background_thumbnail')
                ->onlyOnForms(),

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

            BelongsToMany::make('Genres')
                ->searchable(),

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
        return [];
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
}
