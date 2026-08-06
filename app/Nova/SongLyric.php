<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class SongLyric extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\SongLyric::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\SongLyric|null
     */
    public $resource;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'language', 'source'
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
            ID::make()->sortable(),

            BelongsTo::make('Song', 'song', Song::class)
                ->searchable(),

            Select::make('Source')
                ->options([
                    'apple' => 'Apple Music',
                    'user' => 'User submission',
                    'lrclib' => 'LRCLIB',
                ])
                ->default('apple')
                ->sortable(),

            Text::make('Language')
                ->sortable()
                ->help('Original language of the lyrics (BCP-47, e.g. ja, en, ko).'),

            Select::make('Timing')
                ->options([
                    'word' => 'Word (karaoke)',
                    'line' => 'Line',
                    'none' => 'None (static)',
                ])
                ->default('word')
                ->sortable(),

            Select::make('Status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->default('pending')
                ->sortable(),

            Number::make('Leading silence (ms)', 'leading_silence_ms')
                ->hideFromIndex()
                ->nullable()
                ->help('Silence before the first line begins.'),

            Number::make('Lyric offset (ms)', 'lyric_offset_ms')
                ->hideFromIndex()
                ->nullable()
                ->help('Global timing nudge applied to every timestamp. May be negative.'),

            Number::make('Duration (ms)', 'duration_ms')
                ->hideFromIndex()
                ->nullable(),

            Code::make('Agents')
                ->json()
                ->nullable()
                ->hideFromIndex()
                ->help('The cast of voices, e.g. [{"key":"v1","type":"person"},{"key":"v1000","type":"group"}]. Each line references one by its agent key.'),

            BelongsTo::make('Submitter', 'submitter', User::class)
                ->nullable()
                ->hideFromIndex(),

            HasMany::make('Lines', 'lines', SongLyricLine::class),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->resource->source . ' · ' . $this->resource->language . ' (song ' . $this->resource->song_id . ')';
    }
}
