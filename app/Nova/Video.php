<?php

namespace App\Nova;

use App\Enums\VideoSource;
use App\Enums\VideoType;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class Video extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Video::class;

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
        'id', 'source', 'code'
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
            Heading::make('Identification'),

            ID::make()->sortable(),

            Heading::make('Meta information'),

            MorphTo::make('Videoable')
                ->types([
                    Anime::class,
                    Episode::class,
                    Game::class,
                    Manga::class,
                ])
                ->required()
                ->searchable()
                ->sortable(),

            BelongsTo::make('Audio Language', 'language', Language::class)
                ->default(73) // Japanese
                ->required()
                ->sortable()
                ->help('The language of the audio. Used for grouping videos in different languages.'),

            Select::make('Source')
                ->options(VideoSource::asSelectArray())
                ->displayUsingLabels()
                ->required()
                ->sortable()
                ->help('The source of the video. For example: YouTube.'),

            Text::make('Code')
                ->displayUsing(function ($value) {
                    return str($value)->limit(50);
                })
                ->required()
                ->sortable()
                ->help('The code of the video. Usually the gibberish part of the link.'),

            Select::make('Type')
                ->options(VideoType::asSelectArray())
                ->displayUsing(function (VideoType $videoType) {
                    return $videoType->key;
                })
                ->required()
                ->sortable()
                ->help('The type of the video. For example: Promotional Video.<br />Choose <strong>Default</strong> for Episode.'),

            Boolean::make('Is Sub')
                ->required()
                ->sortable()
                ->help('Mark the video as subbed if it contains subtitles.'),

            Boolean::make('Is Dub')
                ->required()
                ->sortable()
                ->help('Mark the video as dubbed if the audio is not original.'),

            Number::make('Order')
                ->rules('nullable', 'integer', 'min:1')
                ->nullable()
                ->sortable()
                ->help('Only used for non-episode videos.<br />The order in which the video is shown.<br />Leave empty to assign automatically.'),
        ];
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
}
