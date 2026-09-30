<?php

namespace App\Services;

use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryPriority;
use App\Enums\UserLibraryRewatchValue;
use App\Enums\UserLibraryStatus;
use App\Enums\UserLibraryStorage;
use App\Exceptions\UnsupportedLibraryExportException;
use ErrorException;
use Generator;
use Illuminate\Http\UploadedFile;
use SimpleXMLElement;
use ZipArchive;

class LibraryImportParser
{
    /**
     * The shape of a parsed library entry.
     *
     * @var array
     */
    protected const array EMPTY_ENTRY = [
        'mal_id' => null,
        'status' => null,
        'score' => 0,
        'started_at' => null,
        'ended_at' => null,
        'rewatch_count' => null,
        'progress' => 0,
        'is_rewatching' => null,
        'rewatch_value' => null,
        'priority' => null,
        'storage' => null,
        'storage_amount' => null,
        'tags' => null,
        'note' => null,
    ];

    /**
     * The export element names of each library kind.
     *
     * @var array
     */
    protected const array EXPORT_ELEMENTS = [
        UserLibraryKind::Anime => [
            'entry' => 'anime',
            'mal_id' => 'series_animedb_id',
            'rewatch_count' => 'my_times_watched',
            'progress' => 'my_watched_episodes',
            'is_rewatching' => 'my_rewatching',
            'rewatch_value' => 'my_rewatch_value',
            'storage_amount' => 'my_storage_value',
        ],
        UserLibraryKind::Manga => [
            'entry' => 'manga',
            'mal_id' => 'manga_mangadb_id',
            'rewatch_count' => 'my_times_read',
            'progress' => 'my_read_chapters',
            'is_rewatching' => 'my_rereading',
            'rewatch_value' => 'my_reread_value',
            'storage_amount' => 'my_retail_volumes',
        ],
    ];

    /**
     * The largest storage amount the library can store.
     *
     * @var float
     */
    protected const float MAX_STORAGE_AMOUNT = 999999.99;

    /**
     * Returns the library entries in the given export file.
     *
     * @param UploadedFile $file
     *
     * @return array
     * @throws UnsupportedLibraryExportException
     */
    public static function parseFile(UploadedFile $file): array
    {
        $entriesByKind = [];

        foreach (self::readDocuments($file->getRealPath()) as $document) {
            $entriesByKind += self::parseDocument($document);
        }

        if (empty($entriesByKind)) {
            throw new UnsupportedLibraryExportException(__('The file does not contain an anime or manga list.'));
        }

        return $entriesByKind;
    }

    /**
     * Returns the library entries in the given MAL list.
     *
     * @param array           $listEntries
     * @param UserLibraryKind $libraryKind
     *
     * @return array
     */
    public static function parseList(array $listEntries, UserLibraryKind $libraryKind): array
    {
        $isManga = $libraryKind->is(UserLibraryKind::Manga);
        [$idKey, $progressKey, $rewatchingKey] = $isManga
            ? ['manga_id', 'num_read_chapters', 'is_rereading']
            : ['anime_id', 'num_watched_episodes', 'is_rewatching'];
        $isDayFirst = self::isDayFirst($listEntries);

        return array_map(function (array $listEntry) use ($isManga, $idKey, $progressKey, $rewatchingKey, $isDayFirst): array {
            $retailVolumes = $isManga ? self::convertAmount((string) ($listEntry['retail_string'] ?? '')) : null;

            return [
                ...self::EMPTY_ENTRY,
                'mal_id' => (int) $listEntry[$idKey],
                'status' => self::convertStatusCode((int) $listEntry['status']),
                'score' => (int) ($listEntry['score'] ?? 0),
                'started_at' => self::convertListDate($listEntry['start_date_string'] ?? null, $isDayFirst),
                'ended_at' => self::convertListDate($listEntry['finish_date_string'] ?? null, $isDayFirst),
                'progress' => (int) ($listEntry[$progressKey] ?? 0),
                'is_rewatching' => self::convertFlag((string) ($listEntry[$rewatchingKey] ?? '')),
                'priority' => self::convertPriority((string) ($listEntry['priority_string'] ?? '')),
                'storage' => $retailVolumes === null ? null : UserLibraryStorage::RetailManga,
                'storage_amount' => $retailVolumes,
                'tags' => self::convertTags((string) ($listEntry['tags'] ?? '')),
                'note' => self::convertNote((string) ($listEntry['editable_notes'] ?? '')),
            ];
        }, $listEntries);
    }

    /**
     * Returns the XML documents in the file at the given path.
     *
     * @param string $path
     *
     * @return Generator
     * @throws UnsupportedLibraryExportException
     */
    protected static function readDocuments(string $path): Generator
    {
        $remainingBytes = (int) config('import.max_xml_file_size') * 1024;
        $signature = (string) file_get_contents($path, length: 4);

        if (!str_starts_with($signature, "PK\x03\x04")) {
            $wrapper = str_starts_with($signature, "\x1F\x8B") ? 'compress.zlib://' : '';

            yield self::readStream(fopen($wrapper . $path, 'rb'), $remainingBytes);
            return;
        }

        $archive = new ZipArchive();

        if ($archive->open($path, ZipArchive::RDONLY) !== true) {
            return;
        }

        try {
            for ($index = 0; $index < $archive->numFiles; $index++) {
                $name = (string) $archive->getNameIndex($index);

                if (!str_ends_with(strtolower($name), '.xml') || str_starts_with($name, '__MACOSX/')) {
                    continue;
                }

                $document = self::readStream($archive->getStreamIndex($index), $remainingBytes);
                $remainingBytes -= strlen($document);

                yield $document;
            }
        } finally {
            $archive->close();
        }
    }

    /**
     * Reads the given stream up to the given number of bytes.
     *
     * @param mixed $stream
     * @param int   $maxBytes
     *
     * @return string
     * @throws UnsupportedLibraryExportException
     */
    protected static function readStream(mixed $stream, int $maxBytes): string
    {
        if (!is_resource($stream)) {
            return '';
        }

        try {
            $contents = stream_get_contents($stream, $maxBytes + 1);
        } catch (ErrorException) {
            $contents = false;
        } finally {
            fclose($stream);
        }

        if (strlen((string) $contents) > $maxBytes) {
            throw new UnsupportedLibraryExportException(__('The file must not be larger than :max kilobytes once uncompressed.', ['max' => config('import.max_xml_file_size')]));
        }

        return (string) $contents;
    }

    /**
     * Returns the library entries in the given XML document.
     *
     * @param string $document
     *
     * @return array
     */
    protected static function parseDocument(string $document): array
    {
        $usesInternalErrors = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($document, options: LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($usesInternalErrors);

        if ($xml === false) {
            return [];
        }

        return match ($xml->getName()) {
            'myanimelist' => self::parseExport($xml),
            'list' => self::parseFolders($xml),
            default => [],
        };
    }

    /**
     * Returns the library entries in the given MAL or Kitsu export.
     *
     * @param SimpleXMLElement $xml
     *
     * @return array
     */
    protected static function parseExport(SimpleXMLElement $xml): array
    {
        $entriesByKind = [];

        foreach (self::EXPORT_ELEMENTS as $kindValue => $elementNames) {
            foreach ($xml->{$elementNames['entry']} as $element) {
                $entriesByKind[$kindValue][] = [
                    ...self::EMPTY_ENTRY,
                    'mal_id' => self::convertID((string) $element->{$elementNames['mal_id']}),
                    'status' => self::convertStatusName((string) $element->my_status),
                    'score' => (int) $element->my_score,
                    'started_at' => self::convertExportDate((string) $element->my_start_date),
                    'ended_at' => self::convertExportDate((string) $element->my_finish_date),
                    'rewatch_count' => isset($element->{$elementNames['rewatch_count']}) ? (int) $element->{$elementNames['rewatch_count']} : null,
                    'progress' => (int) $element->{$elementNames['progress']},
                    'is_rewatching' => self::convertFlag((string) $element->{$elementNames['is_rewatching']}),
                    'rewatch_value' => self::convertRewatchValue((string) $element->{$elementNames['rewatch_value']}),
                    'priority' => self::convertPriority((string) $element->my_priority),
                    'storage' => self::convertStorage((string) $element->my_storage),
                    'storage_amount' => self::convertAmount((string) $element->{$elementNames['storage_amount']}),
                    'tags' => self::convertTags((string) $element->my_tags),
                    'note' => self::convertNote((string) $element->my_comments),
                ];
            }
        }

        return $entriesByKind;
    }

    /**
     * Returns the library entries in the given folder export.
     *
     * @param SimpleXMLElement $xml
     *
     * @return array
     */
    protected static function parseFolders(SimpleXMLElement $xml): array
    {
        $entriesByKind = [];

        foreach ($xml->folder as $folder) {
            $status = self::convertStatusName((string) $folder->name);

            foreach ($folder->data->item as $item) {
                preg_match('#myanimelist\.net/(anime|manga)/(\d+)#', (string) $item->link, $linkComponents);
                $kindValue = ($linkComponents[1] ?? null) === 'manga' ? UserLibraryKind::Manga : UserLibraryKind::Anime;

                $entriesByKind[$kindValue][] = [
                    ...self::EMPTY_ENTRY,
                    'mal_id' => isset($linkComponents[2]) ? (int) $linkComponents[2] : null,
                    'status' => $status,
                ];
            }
        }

        return $entriesByKind;
    }

    /**
     * Converts an exported MAL ID to an integer.
     *
     * @param string $malID
     *
     * @return ?int
     */
    protected static function convertID(string $malID): ?int
    {
        $malID = trim($malID);

        return ctype_digit($malID) ? (int) $malID : null;
    }

    /**
     * Converts an exported status name to our library status.
     *
     * @param string $malStatus
     *
     * @return ?int
     */
    protected static function convertStatusName(string $malStatus): ?int
    {
        $malStatus = str($malStatus)->trim()
            ->lower()
            ->camel()
            ->value();

        return match ($malStatus) {
            'reading', 'watching' => UserLibraryStatus::InProgress,
            'onHold' => UserLibraryStatus::OnHold,
            'planToWatch', 'planToRead' => UserLibraryStatus::Planning,
            'dropped' => UserLibraryStatus::Dropped,
            'completed' => UserLibraryStatus::Completed,
            default => null,
        };
    }

    /**
     * Converts a MAL list status code to our library status.
     *
     * @param int $malStatus
     *
     * @return ?int
     */
    protected static function convertStatusCode(int $malStatus): ?int
    {
        return match ($malStatus) {
            1 => UserLibraryStatus::InProgress,
            2 => UserLibraryStatus::Completed,
            3 => UserLibraryStatus::OnHold,
            4 => UserLibraryStatus::Dropped,
            6 => UserLibraryStatus::Planning,
            default => null,
        };
    }

    /**
     * Converts an exported yes or no value to a boolean.
     *
     * @param string $malFlag
     *
     * @return ?bool
     */
    protected static function convertFlag(string $malFlag): ?bool
    {
        return match (strtolower(trim($malFlag))) {
            '1', 'yes', 'true' => true,
            '0', 'no', 'false' => false,
            default => null,
        };
    }

    /**
     * Converts an exported priority to our library priority.
     *
     * @param string $malPriority
     *
     * @return ?int
     */
    protected static function convertPriority(string $malPriority): ?int
    {
        return match (strtolower(trim($malPriority))) {
            'low', '0' => UserLibraryPriority::Low,
            'medium', '1' => UserLibraryPriority::Medium,
            'high', '2' => UserLibraryPriority::High,
            default => null,
        };
    }

    /**
     * Converts an exported rewatch value to our library rewatch value.
     *
     * @param string $malRewatchValue
     *
     * @return ?int
     */
    protected static function convertRewatchValue(string $malRewatchValue): ?int
    {
        $malRewatchValue = str($malRewatchValue)->trim()
            ->lower()
            ->camel()
            ->value();

        return match ($malRewatchValue) {
            'veryLow', '1' => UserLibraryRewatchValue::VeryLow,
            'low', '2' => UserLibraryRewatchValue::Low,
            'medium', '3' => UserLibraryRewatchValue::Medium,
            'high', '4' => UserLibraryRewatchValue::High,
            'veryHigh', '5' => UserLibraryRewatchValue::VeryHigh,
            default => null,
        };
    }

    /**
     * Converts an exported storage name to our library storage.
     *
     * @param string $malStorage
     *
     * @return ?int
     */
    protected static function convertStorage(string $malStorage): ?int
    {
        return match (preg_replace('/[^a-z]/', '', strtolower($malStorage))) {
            'harddrive' => UserLibraryStorage::HardDrive,
            'dvdcd' => UserLibraryStorage::DVD,
            'retaildvd' => UserLibraryStorage::RetailDVD,
            'vhs' => UserLibraryStorage::VHS,
            'externalhd' => UserLibraryStorage::ExternalHardDrive,
            'nas' => UserLibraryStorage::NAS,
            'bluray' => UserLibraryStorage::BluRay,
            'retailmanga' => UserLibraryStorage::RetailManga,
            'magazine' => UserLibraryStorage::Magazine,
            default => null,
        };
    }

    /**
     * Converts an exported storage amount to a number.
     *
     * @param string $malAmount
     *
     * @return ?float
     */
    protected static function convertAmount(string $malAmount): ?float
    {
        $amount = (float) trim($malAmount);

        return $amount > 0 ? min($amount, self::MAX_STORAGE_AMOUNT) : null;
    }

    /**
     * Converts exported comma-separated tags to a list.
     *
     * @param string $malTags
     *
     * @return ?array
     */
    protected static function convertTags(string $malTags): ?array
    {
        $tags = array_map('trim', explode(',', $malTags));
        $tags = array_values(array_unique(array_filter($tags, fn (string $tag): bool => $tag !== '')));

        return empty($tags) ? null : $tags;
    }

    /**
     * Converts an exported note to plain text.
     *
     * @param string $malNote
     *
     * @return ?string
     */
    protected static function convertNote(string $malNote): ?string
    {
        $malNote = trim($malNote);

        return $malNote === '' ? null : $malNote;
    }

    /**
     * Converts an exported date to the YYYY-MM-DD format.
     *
     * @param string $malDate
     *
     * @return ?string
     */
    protected static function convertExportDate(string $malDate): ?string
    {
        $malDate = trim($malDate);

        if ($malDate === '0000-00-00' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $malDate)) {
            return null;
        }

        return $malDate;
    }

    /**
     * Converts a MAL list date to the YYYY-MM-DD format.
     *
     * @param null|string $malDate
     * @param bool        $isDayFirst
     *
     * @return ?string
     */
    protected static function convertListDate(?string $malDate, bool $isDayFirst): ?string
    {
        $dateComponents = explode('-', $malDate ?? '');

        if (count($dateComponents) !== 3) {
            return null;
        }

        [$first, $second, $year] = $dateComponents;
        [$month, $day] = $isDayFirst ? [$second, $first] : [$first, $second];
        $century = (int) $year > (int) now()->format('y') ? '19' : '20';

        return self::convertExportDate($century . $year . '-' . $month . '-' . $day);
    }

    /**
     * Whether the given MAL list's dates put the day first.
     *
     * @param array $listEntries
     *
     * @return bool
     */
    protected static function isDayFirst(array $listEntries): bool
    {
        foreach ($listEntries as $listEntry) {
            foreach ([$listEntry['start_date_string'] ?? null, $listEntry['finish_date_string'] ?? null] as $malDate) {
                $dateComponents = explode('-', $malDate ?? '');

                if (count($dateComponents) !== 3) {
                    continue;
                }

                if ((int) $dateComponents[0] > 12) {
                    return true;
                }

                if ((int) $dateComponents[1] > 12) {
                    return false;
                }
            }
        }

        return false;
    }
}
