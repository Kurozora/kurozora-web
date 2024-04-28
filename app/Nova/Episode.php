<?php

namespace App\Nova;

use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOneThrough;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;
use Titasgailius\SearchRelations\SearchesRelations;

class Episode extends Resource
{
    use SearchesRelations;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Episode::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Episode|null
     */
    public $resource;

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
        'id'
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = [
        'anime',
        'translations'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Episode';

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
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            Heading::make('Media'),

            Images::make('Banner')
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->title;
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

            HasOneThrough::make('Anime')
                ->onlyOnDetail()
                ->readonly(),

            BelongsTo::make('Season')
                ->searchable()
                ->sortable(),

            BelongsTo::make('TV rating', 'tv_rating')
                ->sortable()
                ->help('The TV rating of the episode. For example NR, G, PG-12, etc.')
                ->required(),

            BelongsTo::make('Previous Episode', 'previous_episode', Episode::class)
                ->hideFromIndex()
                ->searchable()
                ->sortable()
                ->nullable(),

            BelongsTo::make('Next Episode', 'next_episode', Episode::class)
                ->hideFromIndex()
                ->searchable()
                ->sortable()
                ->nullable(),

            Number::make('Number')
                ->sortable()
                ->rules('required')
                ->help('The number of the episode in the current season.'),

            Number::make('Number Total')
                ->sortable()
                ->rules('required')
                ->help('The total number of the episode in regard to past episodes.'),

            Text::make('Title Translations', 'title')
                ->sortable()
                ->required()
                ->translatable()
                ->help('The real title of the episode. If unknown, then use "Episode #" as the title in the respective locale.'),

            Textarea::make('Synopsis Translations', 'synopsis')
                ->nullable()
                ->hideFromIndex()
                ->translatable()
                ->help('A short description of the episode.'),

            Number::make('Duration')
                ->rules('required')
                ->sortable()
                ->help('The duration of the episode in <b>seconds</b>. Usually the same as the duration of the anime, but can be different in special cases.'),

            Boolean::make('Is Filler')
                ->default(false)
                ->required()
                ->sortable()
                ->help('Check the box if the episode is a filler, and the story is understood even if this episode is skipped.'),

            Boolean::make('Is NSFW')
                ->default(false)
                ->required()
                ->sortable()
                ->help('Check the box if the episode is <b>Not Safe For Work</b>.'),

            Boolean::make('Is Special')
                ->default(false)
                ->required()
                ->sortable()
                ->help('Check the box if the episode is special. This usually means an episode that runs longer than the average episode length.'),

            Boolean::make('Is Premiere')
                ->default(false)
                ->required()
                ->sortable()
                ->help('Check the box if the episode is the <b>first episode</b> of the anime or season.'),

            Boolean::make('Is Finale')
                ->default(false)
                ->required()
                ->sortable()
                ->help('Check the box if the episode is the <b>last episode</b> of the anime or season.'),

            Boolean::make('Is Verified')
                ->help('Check the box if the information is correct.'),

            Heading::make('Broadcast'),

            DateTime::make('Started At')
                ->sortable()
                ->help('The air date of the episode in JST timezone. Leave empty if not announced yet.'),

            DateTime::make('Ended At')
                ->sortable()
                ->help('The end date of the episode in JST timezone. Leave empty if not announced yet, or to auto-generate from the value in <b>Started At</b> if <b>Duration</b> is also specified.'),

            MorphMany::make('Videos'),

            HasMany::make('Translations', 'translations', EpisodeTranslation::class),

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
        $episode = $this->resource;

        return $episode->title . ' (ID: ' . $episode->id . ')';
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
}
