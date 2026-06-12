<?php

namespace App\Console\Commands\Importers;

use App\Models\Song;
use App\Models\SongLyric;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSongLyrics extends Command
{
    private const NS_TT = 'http://www.w3.org/ns/ttml';
    private const NS_TTM = 'http://www.w3.org/ns/ttml#metadata';
    private const NS_ITUNES = 'http://music.apple.com/lyric-ttml-internal';
    private const NS_XML = 'http://www.w3.org/XML/1998/namespace';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:song_lyrics {path? : A .ttml file or a directory of {appleMusicID}.ttml files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports Apple Music TTML lyrics into the song_lyrics tables.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $path = $this->argument('path') ?? storage_path('app/lyrics');
        $files = is_dir($path) ? glob(rtrim($path, '/') . '/*.ttml') : [$path];

        if (empty($files)) {
            $this->error("No .ttml files found at: $path");

            return Command::FAILURE;
        }

        foreach ($files as $file) {
            $this->importFile($file);
        }

        return Command::SUCCESS;
    }

    /**
     * Imports a single TTML file.
     *
     * @param string $file
     *
     * @return void
     */
    private function importFile(string $file): void
    {
        $appleMusicID = (int) pathinfo($file, PATHINFO_FILENAME);
        $song = Song::where('am_id', $appleMusicID)->first();

        if ($song === null) {
            $this->warn("Skipped $appleMusicID: no song with that Apple Music ID.");

            return;
        }

        $document = new DOMDocument();
        if (!@$document->loadXML((string) file_get_contents($file))) {
            $this->warn("Skipped $appleMusicID: could not parse TTML.");

            return;
        }

        $root = $document->documentElement;
        $language = $root->getAttributeNS(self::NS_XML, 'lang');
        $timing = strtolower($root->getAttributeNS(self::NS_ITUNES, 'timing') ?: 'line');

        $metadata = $this->firstElement($document, self::NS_ITUNES, 'iTunesMetadata');
        $body = $this->firstElement($document, self::NS_TT, 'body');

        DB::transaction(function () use ($song, $language, $timing, $metadata, $body, $document) {
            if (SongLyric::where('song_id', $song->id)
                ->where('source', 'apple')
                ->where('language', $language)
                ->exists()) {
                $this->warn("Skipped $song->id: already exists.");
                return;
            }

            $lyric = SongLyric::create([
                'song_id' => $song->id,
                'source' => 'apple',
                'language' => $language,
                'timing' => $timing,
                'leading_silence_ms' => $this->parseTimeToMs($metadata?->getAttribute('leadingSilence')),
                'lyric_offset_ms' => $this->parseTimeToMs($this->firstElement($document, self::NS_ITUNES, 'audio')?->getAttribute('lyricOffset')),
                'duration_ms' => $this->parseTimeToMs($body?->getAttribute('dur')),
                'agents' => $this->parseAgents($document),
                'status' => 'approved',
            ]);

            $this->importOriginalLines($lyric, $body, $language);
            $this->importLocalizationTracks($lyric, $document);
        });

        $this->info("Imported $appleMusicID: $timing, $language.");
    }

    /**
     * Imports the original lyric lines from the TTML body.
     *
     * @param SongLyric       $lyric
     * @param DOMElement|null $body
     * @param string          $language
     *
     * @return void
     */
    private function importOriginalLines(SongLyric $lyric, ?DOMElement $body, string $language): void
    {
        if ($body === null) {
            return;
        }

        foreach ($body->getElementsByTagNameNS(self::NS_TT, 'div') as $div) {
            $songPart = $div->getAttributeNS(self::NS_ITUNES, 'songPart') ?: null;
            $divAgent = $div->getAttributeNS(self::NS_TTM, 'agent') ?: null;

            foreach ($div->getElementsByTagNameNS(self::NS_TT, 'p') as $paragraph) {
                $lineKey = $paragraph->getAttributeNS(self::NS_ITUNES, 'key');

                $line = $lyric->lines()->create([
                    'kind' => 'original',
                    'language' => $language,
                    'line_key' => $lineKey,
                    'position' => $this->positionFor($lineKey),
                    'begin_ms' => $this->parseTimeToMs($paragraph->getAttribute('begin')),
                    'end_ms' => $this->parseTimeToMs($paragraph->getAttribute('end')),
                    'agent' => ($paragraph->getAttributeNS(self::NS_TTM, 'agent') ?: $divAgent) ?: null,
                    'song_part' => $songPart,
                    'text' => $paragraph->textContent,
                ]);

                $position = 0;
                $words = $this->extractWords($paragraph, $position);

                if (!empty($words)) {
                    $line->words()->createMany($words);
                }
            }
        }
    }

    /**
     * Imports the translation and transliteration tracks from the TTML head.
     *
     * @param SongLyric   $lyric
     * @param DOMDocument $document
     *
     * @return void
     */
    private function importLocalizationTracks(SongLyric $lyric, DOMDocument $document): void
    {
        foreach ($document->getElementsByTagNameNS(self::NS_ITUNES, 'translation') as $translation) {
            $trackLanguage = $translation->getAttributeNS(self::NS_XML, 'lang');

            foreach ($translation->getElementsByTagNameNS(self::NS_ITUNES, 'text') as $text) {
                $lineKey = $text->getAttribute('for');
                $lyric->lines()->create([
                    'kind' => 'translation',
                    'language' => $trackLanguage,
                    'line_key' => $lineKey,
                    'position' => $this->positionFor($lineKey),
                    'text' => $text->textContent,
                ]);
            }
        }

        foreach ($document->getElementsByTagNameNS(self::NS_ITUNES, 'transliteration') as $transliteration) {
            $trackLanguage = $transliteration->getAttributeNS(self::NS_XML, 'lang');

            foreach ($transliteration->getElementsByTagNameNS(self::NS_ITUNES, 'text') as $text) {
                $lineKey = $text->getAttribute('for');

                $line = $lyric->lines()->create([
                    'kind' => 'transliteration',
                    'language' => $trackLanguage,
                    'line_key' => $lineKey,
                    'position' => $this->positionFor($lineKey),
                    'text' => $text->textContent,
                ]);

                $position = 0;
                $words = $this->extractWords($text, $position);
                if (!empty($words)) {
                    $line->words()->createMany($words);
                }
            }
        }
    }

    /**
     * Extracts the word-level timings from a paragraph or localization text node.
     *
     * @param DOMNode $container
     * @param int     $position
     * @param bool    $isBackground
     *
     * @return array
     */
    private function extractWords(DOMNode $container, int &$position, bool $isBackground = false): array
    {
        $words = [];

        foreach ($container->childNodes as $node) {
            if ($node instanceof DOMElement && $node->localName === 'span' && $node->namespaceURI === self::NS_TT) {
                if ($node->getAttributeNS(self::NS_TTM, 'role') === 'x-bg') {
                    $words = array_merge($words, $this->extractWords($node, $position, true));
                    continue;
                }

                $words[] = [
                    'position' => $position++,
                    'begin_ms' => $this->parseTimeToMs($node->getAttribute('begin')) ?? 0,
                    'end_ms' => $this->parseTimeToMs($node->getAttribute('end')) ?? 0,
                    'text' => $node->textContent,
                    'is_background' => $isBackground,
                    'trailing_space' => false,
                ];
            } else if ($node instanceof DOMText && $node->wholeText !== '' && trim($node->wholeText) === '' && !empty($words)) {
                $words[count($words) - 1]['trailing_space'] = true;
            }
        }

        return $words;
    }

    /**
     * Parses the agent declarations from the TTML head.
     *
     * @param DOMDocument $document
     *
     * @return array
     */
    private function parseAgents(DOMDocument $document): array
    {
        $agents = [];

        foreach ($document->getElementsByTagNameNS(self::NS_TTM, 'agent') as $agent) {
            $key = $agent->getAttributeNS(self::NS_XML, 'id');
            if ($key === '') {
                continue;
            }

            $agents[] = [
                'key' => $key,
                'type' => $agent->getAttribute('type') ?: 'person',
            ];
        }

        return $agents;
    }

    /**
     * Derives a stable ordering position from a line key such as `L12`.
     *
     * @param string $lineKey
     *
     * @return int
     */
    private function positionFor(string $lineKey): int
    {
        $numeric = ltrim($lineKey, 'Ll');

        return is_numeric($numeric) ? (int) $numeric : 0;
    }

    /**
     * Converts a TTML timestamp to milliseconds, handling `SS.fff`, `M:SS.fff`, and `H:MM:SS.fff`.
     *
     * @param string|null $value
     *
     * @return int|null
     */
    private function parseTimeToMs(?string $value): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);
        $sign = 1;

        if (str_starts_with($value, '-')) {
            $sign = -1;
            $value = substr($value, 1);
        }

        if (str_ends_with($value, 's')) {
            $value = substr($value, 0, -1);
        }

        $seconds = 0.0;
        foreach (explode(':', $value) as $part) {
            $seconds = $seconds * 60 + (float) $part;
        }

        return $sign * (int) round($seconds * 1000);
    }

    /**
     * Returns the first element matching the given namespace and local name.
     *
     * @param DOMDocument $document
     * @param string      $namespace
     * @param string      $localName
     *
     * @return DOMElement|null
     */
    private function firstElement(DOMDocument $document, string $namespace, string $localName): ?DOMElement
    {
        $element = $document->getElementsByTagNameNS($namespace, $localName)->item(0);

        return $element instanceof DOMElement ? $element : null;
    }
}
