<?php

namespace App\Nova;

use App\Enums\MediaCollection;
use App\Nova\Filters\MissingSongAttributes;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Kiritokatklian\NovaAstrotranslatable\HandlesTranslatable;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;
use Validator;

class Song extends Resource
{
    use HandlesTranslatable;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Song::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Song|null
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
        'id', 'original_title', 'artist', 'original_lyrics'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Song';

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

            Text::make('Amazon Music ID', 'amazon_id')
                ->hideFromIndex()
                ->help('The ID of the song as noted on Amazon Music.<br>Note: Include the album ID and trackAsin.<br>Example: https://www.amazon.com/music/player/albums/<strong>B09976CJK1?trackAsin=B099767KH9</strong>'),

            Number::make('Apple Music ID', 'am_id')
                ->sortable()
                ->help('The ID of the song as noted on Apple Music.<br>Note: The id of the song is different from the album id.<br>Example: https://music.apple.com/us/album/w-a-v-e-r/1576224590?i=<strong>1576225422</strong>'),

            Number::make('Deezer ID', 'deezer_id')
                ->hideFromIndex()
                ->help('The ID of the song as noted on Deezer.<br>Note: The id of the song is different from the album id.<br>Example: https://deezer.com/track/<strong>1431835052</strong>'),

            Number::make('MAL ID')
                ->hideFromIndex()
                ->help('The ID of the song as noted on MyAnimeList.'),

            Text::make('Spotify ID', 'spotify_id')
                ->hideFromIndex()
                ->help('The ID of the song as noted on Spotify.<br>Example: https://open.spotify.com/track/<strong>7EzOCXhPB7X5Exjq7gCFee</strong>'),

            Text::make('YouTube Music ID', 'youtube_id')
                ->hideFromIndex()
                ->help('The ID of the song as noted on YouTube Music.<br>Example: https://music.youtube.com/watch?v=<strong>Cph9zq9emF0</strong>'),

            Heading::make('Media'),

            Avatar::make('Artwork')
                ->thumbnail(function () {
                    return $this->resource->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/music_album.webp');
                })->preview(function () {
                    return $this->resource->getFirstMediaFullUrl(\App\Enums\MediaCollection::Artwork()) ?? asset('images/static/placeholders/music_album.webp');
                })
                ->rounded()
                ->deletable(false)
                ->disableDownload()
                ->readonly()
                ->onlyOnPreview(),

            Images::make('Artwork', MediaCollection::Artwork)
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

            Text::make('Title', 'original_title')
                ->sortable()
                ->rules('max:280')
                ->required(),

            Text::make('Title Translations', 'title')
                ->hideFromIndex()
                ->nullable()
                ->translatable(),

            Text::make('Artist')
                ->sortable(),

            Textarea::make('Lyrics', 'original_lyrics')
                ->hideFromIndex()
                ->nullable(),

            Textarea::make('Lyrics Translations', 'lyrics')
                ->hideFromIndex()
                ->nullable()
                ->translatable(),

            Textarea::make('Copyright')
                ->hideFromIndex()
                ->help('For example: © ' . date('Y') . ' Kurozora'),

            HasMany::make('Translations', 'song_translations', SongTranslation::class),

            HasMany::make('Media Songs'),

//            BelongsToMany::make('Anime')
//                ->searchable(),

//            BelongsToMany::make('Games')
//                ->searchable(),

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
        $song = $this->resource;

        return $song->original_title . ' by ' . $song->artist ?? 'Unknown' . ' (ID: ' . $song->id . ')';
    }

    /**
     * Handle any post-validation processing.
     *
     * @param NovaRequest                      $request
     * @param \Illuminate\Validation\Validator $validator
     *
     * @return void
     * @throws ValidationException
     */
    protected static function afterValidation(NovaRequest $request, $validator): void
    {
        $resourceID = $request->resourceId;
        $originalTitle = $request->post('original_title');
        $artist = $request->post('artist');

        $unique = Rule::unique(\App\Models\Song::TABLE_NAME)->where(function ($query) use ($resourceID, $originalTitle, $artist) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query->where('original_title', $originalTitle)->where('artist', $artist);
        });

        $uniqueValidator = Validator::make($request->only('original_title'), [
            'original_title' => [$unique],
        ], [
            'original_title' => __('validation.unique')
        ]);

        $uniqueValidator->validate();
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
        return [
            new MissingSongAttributes
        ];
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
