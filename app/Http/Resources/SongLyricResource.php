<?php

namespace App\Http\Resources;

use App\Models\SongLyric;
use App\Models\SongLyricLine;
use App\Models\SongLyricWord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SongLyricResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var SongLyric $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $lines = $this->resource->lines;
        $originals = $lines->where('kind', 'original');
        $translations = $lines->where('kind', 'translation')->groupBy('line_key');
        $transliterations = $lines->where('kind', 'transliteration')->groupBy('line_key');

        return [
            'id' => (string) $this->resource->id,
            'type' => 'lyrics',
            'href' => route('api.songs.lyrics', $this->resource->song_id, false),
            'attributes' => [
                'source' => $this->resource->source,
                'language' => $this->resource->language,
                'timing' => $this->resource->timing,
                'leadingSilenceMs' => $this->resource->leading_silence_ms,
                'lyricOffsetMs' => $this->resource->lyric_offset_ms,
                'durationMs' => $this->resource->duration_ms,
                'agents' => $this->resource->agents ?? [],
                'lines' => $originals->map(function (SongLyricLine $line) use ($translations, $transliterations) {
                    return [
                        'key' => $line->line_key,
                        'position' => $line->position,
                        'songPart' => $line->song_part,
                        'agent' => $line->agent,
                        'beginMs' => $line->begin_ms,
                        'endMs' => $line->end_ms,
                        'text' => $line->text,
                        'words' => $this->mapWords($line->words),
                        'translations' => ($translations->get($line->line_key) ?? collect())
                            ->map(fn (SongLyricLine $translation) => [
                                'language' => $translation->language,
                                'text' => $translation->text,
                            ])->values(),
                        'transliterations' => ($transliterations->get($line->line_key) ?? collect())
                            ->map(fn (SongLyricLine $transliteration) => [
                                'language' => $transliteration->language,
                                'text' => $transliteration->text,
                                'words' => $this->mapWords($transliteration->words),
                            ])->values(),
                    ];
                })->values(),
            ],
        ];
    }

    /**
     * Maps a line's words into their serialized form.
     *
     * @param Collection $words
     *
     * @return array
     */
    private function mapWords(Collection $words): array
    {
        return $words->map(fn (SongLyricWord $word) => [
            'beginMs' => $word->begin_ms,
            'endMs' => $word->end_ms,
            'text' => $word->text,
            'background' => $word->is_background,
            'trailingSpace' => $word->trailing_space,
        ])->values()->all();
    }
}
