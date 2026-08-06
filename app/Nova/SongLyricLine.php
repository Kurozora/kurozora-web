<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class SongLyricLine extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\SongLyricLine::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\SongLyricLine|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource.
     *
     * @var string
     */
    public static $title = 'line_key';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'line_key', 'text'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Song';

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

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
            ID::make()->sortable(),

            BelongsTo::make('Lyric', 'lyric', SongLyric::class),

            Select::make('Kind')
                ->options([
                    'original' => 'Original',
                    'translation' => 'Translation',
                    'transliteration' => 'Transliteration',
                ])
                ->default('original')
                ->sortable(),

            Text::make('Language')
                ->sortable(),

            Text::make('Line key', 'line_key')
                ->sortable()
                ->help('L1, L2, etc. Links translation and transliteration rows to their original line.'),

            Number::make('Position')
                ->sortable(),

            Number::make('Begin (ms)', 'begin_ms')
                ->hideFromIndex()
                ->nullable(),

            Number::make('End (ms)', 'end_ms')
                ->hideFromIndex()
                ->nullable(),

            Text::make('Agent')
                ->nullable()
                ->hideFromIndex(),

            Text::make('Song part', 'song_part')
                ->nullable()
                ->hideFromIndex(),

            Textarea::make('Text')
                ->alwaysShow(),

            HasMany::make('Words', 'words', SongLyricWord::class),
        ];
    }
}
