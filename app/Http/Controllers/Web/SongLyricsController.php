<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Song;
use App\Models\SongLyric;
use App\Models\SongLyricLine;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class SongLyricsController extends Controller
{
    /**
     * The id of the song whose lyrics are shown.
     *
     * @var int|null $songID
     */
    protected ?int $songID = null;

    /**
     * The gap, in milliseconds, that warrants an interlude between two lines.
     *
     * @var int
     */
    private const int GAP_THRESHOLD_MS = 4000;

    /**
     * Show the lyrics of a song.
     *
     * @param Song $song
     *
     * @return Application|Factory|View
     */
    public function show(Song $song): Application|Factory|View
    {
        $this->songID = $song->id;

        return view('song.lyrics', ['lyrics' => $this->lyrics()]);
    }

    /**
     * The lyrics data prepared for rendering.
     *
     * @return array
     */
    protected function lyrics(): array
    {
        if ($this->songID === null) {
            return ['amID' => null, 'offsetMs' => 0, 'agents' => [], 'items' => []];
        }

        $lyric = SongLyric::where('song_id', '=', $this->songID)
            ->where('status', '=', 'approved')
            ->with(['lines.words'])
            ->orderByDesc('id')
            ->first();

        if ($lyric === null) {
            return ['amID' => null, 'offsetMs' => 0, 'agents' => [], 'items' => []];
        }

        return [
            'amID' => Song::where('id', '=', $this->songID)->value('am_id'),
            'offsetMs' => $lyric->lyric_offset_ms ?? 0,
            'agents' => $lyric->agents ?? [],
            'items' => $this->buildItems($lyric->lines),
        ];
    }

    /**
     * Builds the ordered line and interlude items from the lyric's lines.
     *
     * @param Collection $lines
     *
     * @return array
     */
    private function buildItems(Collection $lines): array
    {
        $originals = $lines->where('kind', '=', 'original');
        $translations = $lines->where('kind', '=', 'translation')->groupBy('line_key');
        $transliterations = $lines->where('kind', '=', 'transliteration')->groupBy('line_key');

        $items = [];
        $previousEndMs = 0;

        foreach ($originals as $line) {
            $beginMs = $line->begin_ms ?? $previousEndMs;

            if ($beginMs - $previousEndMs >= self::GAP_THRESHOLD_MS) {
                $items[] = [
                    'type' => 'interlude',
                    'startMs' => $previousEndMs,
                    'endMs' => $beginMs,
                ];
            }

            $items[] = $this->mapLine($line, $transliterations->get($line->line_key), $translations->get($line->line_key));
            $previousEndMs = $line->end_ms ?? $beginMs;
        }

        return $items;
    }

    /**
     * Maps a single original line into its rendered item, pairing each word with its transliteration.
     *
     * @param SongLyricLine    $line
     * @param Collection|null  $transliterations
     * @param Collection|null  $translations
     *
     * @return array
     */
    private function mapLine(SongLyricLine $line, ?Collection $transliterations, ?Collection $translations): array
    {
        $transliteration = $transliterations?->first();
        $romajiDiffers = $transliteration && trim($transliteration->text) !== trim($line->text);
        $romajiWords = $romajiDiffers ? $transliteration->words->values() : collect();

        $mainWords = [];
        $backgroundWords = [];
        $hasWordTiming = $line->words->isNotEmpty();

        if ($hasWordTiming) {
            foreach ($line->words->values() as $index => $word) {
                $pair = [
                    'text' => $word->text,
                    'beginMs' => $word->begin_ms,
                    'endMs' => $word->end_ms,
                    'trailingSpace' => $word->trailing_space,
                    'romaji' => $romajiWords->get($index)?->text,
                ];

                if ($word->is_background) {
                    $backgroundWords[] = $pair;
                } else {
                    $mainWords[] = $pair;
                }
            }
        } else {
            $mainWords[] = [
                'text' => $line->text,
                'beginMs' => $line->begin_ms ?? 0,
                'endMs' => $line->end_ms ?? 0,
                'trailingSpace' => false,
                'romaji' => $romajiDiffers ? $transliteration->text : null,
            ];
        }

        return [
            'type' => 'line',
            'beginMs' => $line->begin_ms,
            'endMs' => $line->end_ms,
            'agent' => $line->agent,
            'hasWordTiming' => $hasWordTiming,
            'hasBackground' => !empty($backgroundWords),
            'mainWords' => $mainWords,
            'backgroundWords' => $backgroundWords,
            'translations' => ($translations ?? collect())
                ->map(fn (SongLyricLine $translation) => [
                    'language' => $translation->language,
                    'text' => $translation->text,
                ])->values()->all(),
        ];
    }

}
