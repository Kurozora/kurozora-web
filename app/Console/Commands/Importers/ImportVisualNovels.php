<?php

namespace App\Console\Commands\Importers;

use App\Enums\AstrologicalSign;
use App\Enums\LanguageSupportType;
use App\Enums\MediaCollection;
use App\Enums\PersonRelationshipType;
use App\Enums\StudioType;
use App\Models\Anime;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\Game;
use App\Models\GameCast;
use App\Models\Genre;
use App\Models\Language;
use App\Models\MediaGenre;
use App\Models\MediaLanguage;
use App\Models\MediaPlatform;
use App\Models\MediaRelation;
use App\Models\MediaStaff;
use App\Models\MediaStudio;
use App\Models\MediaTag;
use App\Models\MediaTheme;
use App\Models\MediaType;
use App\Models\Person;
use App\Models\PersonRelationship;
use App\Models\Platform;
use App\Models\Relation;
use App\Models\Source;
use App\Models\StaffRole;
use App\Models\Status;
use App\Models\Studio;
use App\Models\Tag;
use App\Models\Theme;
use App\Models\TvRating;
use Carbon\Carbon;
use Exception;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;
use Pulse;

class ImportVisualNovels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:visual_novels
                            {path? : Path to the VNDB dump; defaults to the newest storage/app/vndb-db-*.tar.zst}
                            {--limit=0 : Only process the first N visual novels (0 = all)}
                            {--dry-run : List the games that would be inserted, without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports visual novels from a VNDB database dump, de-duplicating against existing games.';

    /**
     * The database connection used for writes, matching the other importers.
     *
     * @var string
     */
    protected const string CONNECTION = 'elb';

    /**
     * The dump table files this importer needs.
     *
     * @var string[]
     */
    protected const array TABLES = [
        'db/vn', 'db/vn_titles', 'db/releases', 'db/releases_vn', 'db/releases_producers',
        'db/releases_extlinks', 'db/releases_platforms', 'db/producers', 'db/vn_extlinks',
        'db/extlinks', 'db/wikidata', 'db/wikidata.header', 'db/vn_anime', 'db/anime',
        'db/tags', 'db/tags_vn', 'db/chars', 'db/chars_names', 'db/chars_vns', 'db/vn_seiyuu',
        'db/staff', 'db/staff_alias', 'db/staff_extlinks', 'db/vn_staff', 'db/vn_editions',
        'db/vn_relations', 'db/producers_extlinks', 'db/releases_titles',
    ];

    /**
     * VNDB external link sites mapped to their URL template.
     *
     * @var string[]
     */
    protected const array LINK_TEMPLATES = [
        'animateg' => 'https://www.animategames.jp/home/detail/{v}',
        'appstore' => 'https://apps.apple.com/app/id{v}',
        'denpa' => 'https://denpasoft.com/product/{v}/',
        'digiket' => 'https://www.digiket.com/work/show/_data/ID=ITM{v}/',
        'dlsite' => 'https://www.dlsite.com/home/work/=/product_id/{v}.html',
        'dlsiteen' => 'https://www.dlsite.com/ecchi-eng/work/=/product_id/{v}.html',
        'dmm' => 'https://{v}',
        'egs' => 'https://erogamescape.dyndns.org/~ap2/ero/toukei_kaiseki/game.php?game={v}',
        'fakku' => 'https://www.fakku.net/games/{v}',
        'freegame' => 'https://freegame-mugen.jp/{v}.html',
        'freem' => 'https://www.freem.ne.jp/win/game/{v}',
        'gamejolt' => 'https://gamejolt.com/games/vn/{v}',
        'getchu' => 'http://www.getchu.com/soft.phtml?id={v}',
        'getchudl' => 'http://dl.getchu.com/i/item{v}',
        'gog' => 'https://www.gog.com/en/game/{v}',
        'gyutto' => 'https://gyutto.com/i/item{v}',
        'jastusa' => 'https://jastusa.com/games/{v}',
        'jlist' => 'https://jlist.com/shop/product/{v}',
        'johren' => 'https://www.johren.games/games/download/{v}/',
        'kagura' => 'https://www.kaguragames.com/product/{v}/',
        'melonjp' => 'https://www.melonbooks.co.jp/detail/detail.php?product_id={v}',
        'nutaku' => 'https://www.nutaku.net/games/{v}/',
        'playasia' => 'https://www.play-asia.com/13/70{v}',
        'toranoana' => 'https://ec.toranoana.shop/tora/ec/item/{v}/',
        'patreon' => 'https://www.patreon.com/{v}',
        'patreonp' => 'https://www.patreon.com/posts/{v}',
        'playstation_eu' => 'https://store.playstation.com/en-gb/product/{v}',
        'playstation_na' => 'https://store.playstation.com/en-us/product/{v}',
        'renai' => 'https://renai.us/game/{v}',
        'steam' => 'https://store.steampowered.com/app/{v}/',
        'substar' => 'https://subscribestar.{v}',
        'website' => '{v}',
        'wikidata' => 'https://www.wikidata.org/wiki/Q{v}',
    ];

    /**
     * Sites whose id is zero-padded to a fixed width in a URL.
     *
     * @var int[]
     */
    protected const array LINK_PADDING = [
        'digiket' => 7,
        'toranoana' => 12,
    ];

    /**
     * The MangaGamer URL templates keyed by whether the release is adult.
     *
     * @var string[]
     */
    protected const array MANGAGAMER_TEMPLATES = [
        'https://www.mangagamer.com/detail.php?product_code={v}',
        'https://www.mangagamer.com/r18/detail.php?product_code={v}',
    ];

    /**
     * Wikidata columns mapped to their URL template.
     *
     * @var string[]
     */
    protected const array WIKIDATA_LINK_TEMPLATES = [
        'enwiki' => 'https://en.wikipedia.org/wiki/{v}',
        'jawiki' => 'https://ja.wikipedia.org/wiki/{v}',
        'igdb_game' => 'https://www.igdb.com/games/{v}',
        'mobygames_game' => 'https://www.mobygames.com/game/{v}/',
        'pcgamingwiki' => 'https://www.pcgamingwiki.com/wiki/{v}',
        'howlongtobeat' => 'http://howlongtobeat.com/game.php?id={v}',
        'vgmdb_product' => 'https://vgmdb.net/product/{v}',
        'lutris' => 'https://lutris.net/games/{v}',
        'wine' => 'https://appdb.winehq.org/appview.php?iAppId={v}',
        'steam' => 'https://store.steampowered.com/app/{v}/',
        'gog' => 'https://www.gog.com/en/game/{v}',
    ];

    /**
     * VNDB staff link sites mapped to their URL template.
     *
     * @var string[]
     */
    protected const array STAFF_LINK_TEMPLATES = [
        'anidb' => 'https://anidb.net/cr{v}',
        'anison' => 'http://anison.info/data/person/{v}.html',
        'bgmtv' => 'https://bgm.tv/person/{v}',
        'bilibili' => 'https://space.bilibili.com/{v}',
        'boosty' => 'https://boosty.to/{v}',
        'booth_pub' => 'https://{v}.booth.pm/',
        'bsky' => 'https://bsky.app/profile/{v}',
        'cien' => 'https://ci-en.dlsite.com/creator/{v}',
        'deviantar' => 'https://www.deviantart.com/{v}',
        'discogs' => 'https://www.discogs.com/artist/{v}',
        'egs_creator' => 'https://erogamescape.dyndns.org/~ap2/ero/toukei_kaiseki/creater.php?creater={v}',
        'facebook' => 'https://www.facebook.com/{v}',
        'fanbox' => 'https://{v}.fanbox.cc/',
        'fantia' => 'https://fantia.jp/fanclubs/{v}',
        'imdb' => 'https://www.imdb.com/name/nm{v}',
        'instagram' => 'https://www.instagram.com/{v}/',
        'itch_dev' => 'https://{v}.itch.io/',
        'kofi' => 'https://ko-fi.com/{v}',
        'mbrainz' => 'https://musicbrainz.org/artist/{v}',
        'mobygames' => 'https://www.mobygames.com/person/{v}',
        'mobygames_comp' => 'https://www.mobygames.com/company/{v}',
        'nijie' => 'https://nijie.info/members.php?id={v}',
        'patreon' => 'https://www.patreon.com/{v}',
        'pixiv' => 'https://www.pixiv.net/member.php?id={v}',
        'scloud' => 'https://soundcloud.com/{v}',
        'steam_curator' => 'https://store.steampowered.com/curator/{v}',
        'substar' => 'https://subscribestar.{v}',
        'tumblr' => 'https://{v}.tumblr.com/',
        'twitter' => 'https://x.com/{v}',
        'vgmdb' => 'https://vgmdb.net/artist/{v}',
        'vgmdb_org' => 'https://vgmdb.net/org/{v}',
        'vk' => 'https://vk.com/{v}',
        'vndb' => 'https://vndb.org/{v}',
        'website' => '{v}',
        'weibo' => 'https://weibo.com/u/{v}',
        'wikidata' => 'https://www.wikidata.org/wiki/Q{v}',
        'wp' => 'https://en.wikipedia.org/wiki/{v}',
        'youtube' => 'https://www.youtube.com/@{v}',
    ];

    /**
     * VNDB staff link sites mapped to the column holding their id.
     *
     * @var string[]
     */
    protected const array STAFF_ID_COLUMNS = [
        'anidb' => 'anidb_id',
        'anison' => 'anison_id',
        'bgmtv' => 'bgmtv_id',
        'discogs' => 'discogs_id',
        'egs_creator' => 'egs_id',
        'imdb' => 'imdb_id',
        'mbrainz' => 'mbrainz_id',
        'mobygames' => 'mobygames_id',
        'vgmdb' => 'vgmdb_id',
        'wikidata' => 'wikidata_id',
    ];

    /**
     * Staff link sites that are social media profiles.
     *
     * @var string[]
     */
    protected const array SOCIAL_STAFF_SITES = [
        'bilibili', 'bsky', 'deviantar', 'facebook', 'instagram', 'nijie',
        'pixiv', 'scloud', 'tumblr', 'twitter', 'vk', 'weibo', 'youtube',
    ];

    /**
     * Staff link sites that are a page the person runs.
     *
     * @var string[]
     */
    protected const array WEBSITE_STAFF_SITES = [
        'boosty', 'booth_pub', 'cien', 'fanbox', 'fantia', 'itch_dev',
        'kofi', 'patreon', 'substar', 'website',
    ];

    /**
     * VNDB character roles mapped to Kurozora cast roles.
     *
     * @var string[]
     */
    protected const array CAST_ROLES = [
        'main' => 'Protagonist',
        'primary' => 'Deuteragonist',
        'side' => 'Supporting Character',
        'appears' => 'Supporting Character',
    ];

    /**
     * VNDB credit types mapped to Kurozora staff roles.
     *
     * @var string[]
     */
    protected const array STAFF_ROLES = [
        'scenario' => 'Script',
        'chardesign' => 'Character Design',
        'art' => 'Art',
        'music' => 'Music',
        'songs' => 'Theme Song Performance',
        'director' => 'Director',
        'editor' => 'Editor',
        'translator' => 'Translator',
        'qa' => 'Quality Assurance',
        'staff' => 'Other',
    ];

    /**
     * VNDB visual novel relations mapped to Kurozora relations.
     *
     * @var string[]
     */
    protected const array RELATIONS = [
        'seq' => 'Sequel',
        'preq' => 'Prequel',
        'set' => 'Alternative Setting',
        'alt' => 'Alternative Version',
        'char' => 'Character',
        'side' => 'Side Story',
        'par' => 'Parent Story',
        'fan' => 'Spin-Off',
        'orig' => 'Parent Story',
        'ser' => 'Other',
    ];

    /**
     * VNDB tag categories that are mapped into the catalog.
     *
     * @var string[]
     */
    protected const array TAG_CATEGORIES = ['cont', 'ero', 'tech'];

    /**
     * VNDB languages whose names are written family name first.
     *
     * @var string[]
     */
    protected const array FAMILY_NAME_FIRST = ['ja', 'ko', 'zh', 'zh-Hans', 'zh-Hant'];

    /**
     * The parentheticals VNDB appends to tell two people apart.
     *
     * @var string[]
     */
    public const array NAME_QUALIFIERS = [
        'seiyuu', '声優', 'artist', 'writer', 'director', 'producer', 'composer',
        'illustrator', 'musician', 'singer', 'scenario', 'staff', 'retired',
        '仮', '源氏名', 'genjina', '兄', 'ani', '弟', 'otouto',
    ];

    /**
     * VNDB platform codes mapped to Kurozora platform names.
     *
     * @var string[]
     */
    protected const array PLATFORMS = [
        'win' => 'PC (Microsoft Windows)', 'dos' => 'DOS', 'lin' => 'Linux', 'mac' => 'Mac',
        'ios' => 'iOS', 'and' => 'Android', 'web' => 'Web browser',
        'ps1' => 'PlayStation', 'ps2' => 'PlayStation 2', 'ps3' => 'PlayStation 3',
        'ps4' => 'PlayStation 4', 'ps5' => 'PlayStation 5', 'psp' => 'PlayStation Portable', 'psv' => 'PlayStation Vita',
        'nds' => 'Nintendo DS', 'n3d' => 'Nintendo 3DS', 'swi' => 'Nintendo Switch', 'sw2' => 'Nintendo Switch 2',
        'wii' => 'Wii', 'wiu' => 'Wii U', 'nes' => 'Nintendo Entertainment System', 'sfc' => 'Super Nintendo Entertainment System',
        'gba' => 'Game Boy Advance', 'gbc' => 'Game Boy Color', 'pce' => 'PC Engine',
        'p88' => 'PC-8800 Series', 'p98' => 'PC-98', 'fm7' => 'FM-7', 'fm8' => 'FM-8', 'fmt' => 'FM Towns',
        'x68' => 'Sharp X68000', 'msx' => 'MSX', 'sat' => 'Sega Saturn', 'smd' => 'Sega Mega Drive/Genesis',
        'scd' => 'Sega CD', 'drc' => 'Dreamcast', 'xbo' => 'Xbox One', 'xb3' => 'Xbox Series X|S', 'xxs' => 'Xbox Series X|S',
    ];

    /**
     * Existing games indexed by every key used for de-duplication.
     *
     * @var array<string, array<string, int>>
     */
    protected array $index = [
        'vndb' => [], 'igdbSlug' => [], 'igdbId' => [], 'steam' => [], 'titleYear' => [],
    ];

    /**
     * Existing anime ids keyed by MAL id, for linking a visual novel to its adaptations.
     *
     * @var array<int, int>
     */
    protected array $animeByMal = [];

    /**
     * The id of the unrated TV rating.
     *
     * @var int|null
     */
    protected ?int $unratedTvRatingID = null;

    /**
     * VNDB character attributes keyed by VNDB character id.
     *
     * @var array
     */
    protected array $characters = [];

    /**
     * VNDB staff ids keyed by alias id.
     *
     * @var array
     */
    protected array $staffAliases = [];

    /**
     * VNDB staff attributes keyed by VNDB staff id.
     *
     * @var array
     */
    protected array $staffMembers = [];

    /**
     * Existing people ids keyed by VNDB staff id.
     *
     * @var int[]
     */
    protected array $peopleByVndb = [];

    /**
     * VNDB staff ids that could not be matched without guessing.
     *
     * @var array
     */
    protected array $unresolvedPeople = [];

    /**
     * VNDB staff names that were too generic to match.
     *
     * @var array
     */
    protected array $weakMatches = [];

    /**
     * Unclaimed people ids keyed by normalized name.
     *
     * @var array
     */
    protected array $peopleByName = [];

    /**
     * Existing character ids keyed by VNDB character id.
     *
     * @var int[]
     */
    protected array $charactersByVndb = [];

    /**
     * Unclaimed character ids keyed by game id and normalized name.
     *
     * @var array
     */
    protected array $charactersByGame = [];

    /**
     * Genre, theme, cast role, staff role, relation and language ids keyed by name.
     *
     * @var array
     */
    protected array $lookups = [];

    /**
     * Names that matched more than one unclaimed record.
     *
     * @var array
     */
    protected array $ambiguous = ['people' => [], 'characters' => []];

    /**
     * Seconds spent per phase of the run.
     *
     * @var array
     */
    protected array $timings = [];

    /**
     * Run a phase and record how long it took.
     *
     * @param string   $phase
     * @param callable $work
     * @return mixed
     */
    protected function measure(string $phase, callable $work): mixed
    {
        $startedAt = microtime(true);

        try {
            return $work();
        } finally {
            $this->timings[$phase] = ($this->timings[$phase] ?? 0) + microtime(true) - $startedAt;
        }
    }

    /**
     * Report how long each phase of the run took.
     *
     * @return void
     */
    protected function reportTimings(): void
    {
        $this->newLine();
        $this->info('Time spent:');

        foreach ($this->timings as $phase => $seconds) {
            $this->line(sprintf('  %-22s %6.1fs', $phase, $seconds));
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $path = $this->argument('path') ?: $this->defaultDumpPath();

        if (empty($path) || !is_file($path)) {
            $this->error('Dump not found' . ($path ? ' at ' . $path : ' in storage/app') . '. Adios...');
            return Command::INVALID;
        }

        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');
        ini_set('memory_limit', '-1');

        $directory = $this->measure('extract dump', fn () => $this->extract($path));

        try {
            $this->info('Loading lookup tables...');
            [$titles, $releases, $studiosByVn, $links, $platformsByVn, $languagesByVn, $animeByVn, $tagsByVn, $castByVn, $creditsByVn] = $this->measure('load dump', fn () => [
                $this->loadTitles($directory),
                $this->loadReleases($directory),
                $this->loadStudios($directory),
                $this->loadLinks($directory),
                $this->loadPlatforms($directory),
                $this->loadLanguages($directory),
                $this->loadAnime($directory),
                $this->loadTags($directory),
                $this->loadCast($directory),
                $this->loadStaff($directory),
            ]);

            $this->info('Indexing existing games, anime, characters and people...');
            $this->measure('index catalog', function () {
                $this->indexExistingGames();
                $this->indexExistingAnime();
                $this->indexExistingCharacters();
                $this->indexExistingPeople();
            });

            Pulse::stopRecording();
            Telescope::stopRecording();
            Game::disableSearchSyncing();
            Character::disableSearchSyncing();
            Person::disableSearchSyncing();
            Studio::disableSearchSyncing();
            Platform::disableSearchSyncing();

            $source = Source::on(self::CONNECTION)->withoutGlobalScopes()->where('name', '=', 'Visual Novel')->value('id');
            $mediaTypeID = MediaType::on(self::CONNECTION)->withoutGlobalScopes()
                ->where(['type' => 'game', 'name' => 'Full Game'])->value('id');

            $processed = 0;
            $inserted = 0;
            $matched = 0;
            $skipped = 0;

            foreach ($this->readCopy($directory . '/db/vn') as $row) {
                // id image c_image olang votecount rating average c_length c_lengthnum length devstatus alias description
                [$vndbID, $image, , $olang, $votecount, , , , , , $devstatus, $alias, $description] = $row;

                if (empty($titles[$vndbID])) {
                    continue;
                }

                $release = $releases[$vndbID] ?? [];
                $existingID = $this->resolveExisting($vndbID, $links[$vndbID] ?? [], $titles[$vndbID], $release['year'] ?? null);

                // Interim quality gate: skip bare stubs (no cover art and no votes)
                // that flood VNDB, unless they already match a catalogued game.
                if ($existingID === null && $image === null && (int) $votecount === 0) {
                    $skipped++;
                    continue;
                }

                if ($existingID !== null) {
                    $matched++;
                } else {
                    $inserted++;

                    if ($dryRun) {
                        // List each would-be insert so it can be verified as genuinely absent.
                        $this->line('  NEW  ' . $this->displayTitle($titles[$vndbID], $olang) . '  [' . $vndbID . ', ' . ($release['year'] ?? '????') . ']');
                    }
                }

                if (!$dryRun) {
                    $extras = [
                        'tags' => $tagsByVn[$vndbID] ?? [],
                        'cast' => $castByVn['cast'][$vndbID] ?? [],
                        'seiyuu' => $castByVn['seiyuu'][$vndbID] ?? [],
                        'credits' => $creditsByVn[$vndbID] ?? [],
                        'languages' => $languagesByVn[$vndbID] ?? [],
                    ];

                    $this->upsert($vndbID, $image, $olang, (int) $devstatus, $alias, $description, $titles[$vndbID], $release, $links[$vndbID] ?? [], $studiosByVn[$vndbID] ?? [], $platformsByVn[$vndbID] ?? [], $animeByVn[$vndbID] ?? [], $existingID, $source, $mediaTypeID, $extras);
                }

                if (++$processed % 500 === 0) {
                    $this->info($processed . ' processed (' . $inserted . ' new, ' . $matched . ' matched existing).');
                }

                if ($limit > 0 && $processed >= $limit) {
                    break;
                }
            }

            if (!$dryRun) {
                $this->measure('relations', fn () => $this->attachRelations($directory));
                $this->measure('marriages', fn () => $this->attachMarriages());
            }

            $this->info('Done. ' . $processed . ' visual novels: ' . $inserted . ' new, ' . $matched . ' matched existing, ' . $skipped . ' stubs skipped.' . ($dryRun ? ' (dry run — nothing written)' : ''));
            $this->reportAmbiguous();
            $this->reportTimings();
        } finally {
            File::deleteDirectory($directory);
            Game::enableSearchSyncing();
            Character::enableSearchSyncing();
            Person::enableSearchSyncing();
            Studio::enableSearchSyncing();
            Platform::enableSearchSyncing();
            Pulse::startRecording();
            Telescope::startRecording();
        }

        return Command::SUCCESS;
    }

    /**
     * Resolve the newest VNDB dump in storage/app when no path is given.
     *
     * @return string|null
     */
    protected function defaultDumpPath(): ?string
    {
        $matches = glob(storage_path('app/vndb-db-*.tar.zst')) ?: [];
        rsort($matches);

        return $matches[0] ?? null;
    }

    /**
     * Extract the needed dump tables into a temporary directory.
     *
     * @param string $path
     * @return string
     */
    protected function extract(string $path): string
    {
        $directory = storage_path('app/vndb-' . Str::random(8));
        File::ensureDirectoryExists($directory);

        $this->info('Extracting dump...');
        $command = 'zstd -dc ' . escapeshellarg($path) . ' | tar -x -C ' . escapeshellarg($directory) . ' ' . implode(' ', self::TABLES);
        $result = Process::timeout(600)->run(['bash', '-c', $command]);

        if ($result->failed()) {
            File::deleteDirectory($directory);
            throw new Exception('Failed to extract dump: ' . $result->errorOutput());
        }

        return $directory;
    }

    /**
     * Stream a PostgreSQL COPY (TSV) file, yielding each row's unescaped fields.
     *
     * @param string $file
     * @return Generator<array<int, string|null>>
     */
    protected function readCopy(string $file): Generator
    {
        $handle = fopen($file, 'rb');

        if ($handle === false) {
            return;
        }

        try {
            while (($line = fgets($handle)) !== false) {
                yield array_map($this->unescape(...), explode("\t", rtrim($line, "\n")));
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Unescape a single COPY field.
     *
     * @param string $field
     * @return string|null
     */
    protected function unescape(string $field): ?string
    {
        if ($field === '\\N') {
            return null;
        }

        return strtr($field, ['\\t' => "\t", '\\n' => "\n", '\\r' => "\r", '\\\\' => '\\']);
    }

    /**
     * Load each visual novel's titles keyed by VNDB id.
     *
     * @param string $directory
     * @return array<string, array<int, array{lang: string, official: bool, title: string, latin: ?string}>>
     */
    protected function loadTitles(string $directory): array
    {
        $titles = [];

        foreach ($this->readCopy($directory . '/db/vn_titles') as [$id, $lang, $official, $title, $latin]) {
            $titles[$id][] = ['lang' => $lang, 'official' => $official === 't', 'title' => $title, 'latin' => $latin];
        }

        return $titles;
    }

    /**
     * Aggregate release data (earliest date and lowest age rating) per visual novel.
     *
     * @param string $directory
     * @return array<string, array{year: ?int, released: ?Carbon, minage: ?int}>
     */
    protected function loadReleases(string $directory): array
    {
        $meta = [];

        foreach ($this->readCopy($directory . '/db/releases') as $row) {
            // id gtin olang released voiced reso_x reso_y minage ...
            $meta[$row[0]] = ['released' => (int) $row[3], 'minage' => $row[7] === null ? null : (int) $row[7]];
        }

        $result = [];

        foreach ($this->readCopy($directory . '/db/releases_vn') as [$releaseID, $vnID]) {
            $release = $meta[$releaseID] ?? null;

            if ($release === null) {
                continue;
            }

            $released = $this->parseReleaseDate($release['released']);

            if ($released !== null && ($result[$vnID]['released'] ?? null) === null) {
                $result[$vnID]['released'] = $released;
                $result[$vnID]['year'] = (int) $released->year;
            } elseif ($released !== null && $released->lt($result[$vnID]['released'])) {
                $result[$vnID]['released'] = $released;
                $result[$vnID]['year'] = (int) $released->year;
            }

            if ($release['minage'] !== null) {
                $result[$vnID]['minage'] = min($result[$vnID]['minage'] ?? $release['minage'], $release['minage']);
            }
        }

        return $result;
    }

    /**
     * Load each visual novel's developers and publishers keyed by VNDB id.
     *
     * @param string $directory
     * @return array<string, array<string, array{name: string, developer: bool, publisher: bool}>>
     */
    protected function loadStudios(string $directory): array
    {
        $producers = [];

        foreach ($this->readCopy($directory . '/db/producers') as [$id, , , $name, $latin]) {
            $producers[$id] = $latin ?: $name;
        }

        // Map release ids back to their visual novels.
        $vnOfRelease = [];

        foreach ($this->readCopy($directory . '/db/releases_vn') as [$releaseID, $vnID]) {
            $vnOfRelease[$releaseID][] = $vnID;
        }

        $studios = [];

        foreach ($this->readCopy($directory . '/db/releases_producers') as [$releaseID, $pid, $developer, $publisher]) {
            $name = $producers[$pid] ?? null;

            if ($name === null || empty($vnOfRelease[$releaseID])) {
                continue;
            }

            foreach ($vnOfRelease[$releaseID] as $vnID) {
                $studios[$vnID][$name]['name'] = $name;
                $studios[$vnID][$name]['developer'] = ($studios[$vnID][$name]['developer'] ?? false) || $developer === 't';
                $studios[$vnID][$name]['publisher'] = ($studios[$vnID][$name]['publisher'] ?? false) || $publisher === 't';
            }
        }

        return $studios;
    }

    /**
     * Load external links per visual novel, resolving the Steam and IGDB cross-references.
     *
     * @param string $directory
     * @return array<string, array{steam: string[], igdb: string[], vndb: string}>
     */
    protected function loadLinks(string $directory): array
    {
        $columns = $this->wikidataColumns($directory);
        $wikidata = [];

        foreach ($this->readCopy($directory . '/db/wikidata') as $row) {
            $entry = [];

            foreach (self::WIKIDATA_LINK_TEMPLATES as $column => $template) {
                $values = $this->parsePgArray($row[$columns[$column] ?? -1] ?? null);

                if (!empty($values)) {
                    $entry[$column] = $values;
                }
            }

            if (!empty($entry)) {
                $wikidata[$row[0]] = $entry;
            }
        }

        // Extlink id → [site, value].
        $extlinks = [];

        foreach ($this->readCopy($directory . '/db/extlinks') as [$id, $site, $value]) {
            $extlinks[$id] = [$site, $value];
        }

        $links = [];

        foreach ($this->readCopy($directory . '/db/vn_extlinks') as [$vnID, $linkID]) {
            [$site, $value] = $extlinks[$linkID] ?? [null, null];

            if ($site === null) {
                continue;
            }

            $this->addLink($links, $vnID, $site, $value);

            if ($site !== 'wikidata') {
                continue;
            }

            foreach ($wikidata[$value] ?? [] as $column => $values) {
                foreach ($values as $wikidataValue) {
                    if ($column === 'igdb_game') {
                        $links[$vnID]['igdb'][] = $wikidataValue;
                    }

                    $links[$vnID]['urls'][] = $this->buildUrl(self::WIKIDATA_LINK_TEMPLATES[$column], $wikidataValue);
                }
            }
        }

        // Storefront links live on releases.
        $official = $this->officialReleases($directory);
        $vnOfRelease = [];

        foreach ($this->readCopy($directory . '/db/releases_vn') as [$releaseID, $vnID]) {
            $vnOfRelease[$releaseID][] = $vnID;
        }

        foreach ($this->readCopy($directory . '/db/releases_extlinks') as [$releaseID, $linkID]) {
            [$site, $value] = $extlinks[$linkID] ?? [null, null];

            // Fan patches contribute blog and file-host links.
            if ($site === null || !isset($official[$releaseID])) {
                continue;
            }

            foreach ($vnOfRelease[$releaseID] ?? [] as $vnID) {
                $this->addLink($links, $vnID, $site, $value, $official[$releaseID]);

                if ($site === 'steam') {
                    $links[$vnID]['steam'][] = $value;
                }
            }
        }

        return $links;
    }

    /**
     * Map the wikidata table's column names to their position.
     *
     * @param string $directory
     * @return array<string, int>
     */
    protected function wikidataColumns(string $directory): array
    {
        $header = trim((string) file_get_contents($directory . '/db/wikidata.header'));

        return array_flip(explode("\t", $header));
    }

    /**
     * Append a site's URL to a visual novel's links.
     *
     * @param array  $links
     * @param string $vnID
     * @param string $site
     * @param string $value
     * @return void
     */
    protected function addLink(array &$links, string $vnID, string $site, string $value, bool $isAdult = false): void
    {
        $template = $site === 'mg'
            ? self::MANGAGAMER_TEMPLATES[(int) $isAdult]
            : self::LINK_TEMPLATES[$site] ?? null;

        if ($template === null || $value === '') {
            return;
        }

        $padding = self::LINK_PADDING[$site] ?? null;

        if ($padding !== null) {
            $value = str_pad($value, $padding, '0', STR_PAD_LEFT);
        }

        $links[$vnID]['urls'][] = $this->buildUrl($template, $value);
    }

    /**
     * Fill a URL template with a link's value.
     *
     * @param string $template
     * @param string $value
     * @return string
     */
    protected function buildUrl(string $template, string $value): string
    {
        // Wiki titles are spelled with underscores in a URL.
        if (str_contains($template, 'wikipedia.org') || str_contains($template, 'pcgamingwiki')) {
            $value = str_replace(' ', '_', $value);
        }

        return str_replace('{v}', $value, $template);
    }

    /**
     * Every official, non-patch release mapped to whether it is adult.
     *
     * @param string $directory
     * @return array<string, bool>
     */
    protected function officialReleases(string $directory): array
    {
        $official = [];

        foreach ($this->readCopy($directory . '/db/releases') as $row) {
            // has_ero is column 17, patch is column 18 and official is column 21.
            if (($row[21] ?? 'f') === 't' && ($row[18] ?? 'f') !== 't') {
                $official[$row[0]] = ($row[17] ?? 'f') === 't';
            }
        }

        return $official;
    }

    /**
     * Load each visual novel's voicing and release languages keyed by VNDB id.
     *
     * @param string $directory
     * @return array
     */
    protected function loadLanguages(string $directory): array
    {
        $official = $this->officialReleases($directory);
        $voiced = [];

        foreach ($this->readCopy($directory . '/db/releases') as $row) {
            if (isset($official[$row[0]])) {
                $voiced[$row[0]] = (int) $row[4];
            }
        }

        $titles = [];

        foreach ($this->readCopy($directory . '/db/releases_titles') as [$releaseID, $language, $machine]) {
            if ($machine !== 't' && isset($official[$releaseID])) {
                $titles[$releaseID][$language] = $language;
            }
        }

        $languages = [];

        foreach ($this->readCopy($directory . '/db/releases_vn') as [$releaseID, $vnID]) {
            if (!isset($official[$releaseID])) {
                continue;
            }

            $languages[$vnID]['voiced'] = max($languages[$vnID]['voiced'] ?? 0, $voiced[$releaseID] ?? 0);

            foreach ($titles[$releaseID] ?? [] as $language) {
                $languages[$vnID]['interface'][$language] = $language;
            }
        }

        return $languages;
    }

    /**
     * Load each visual novel's release platforms keyed by VNDB id.
     *
     * @param string $directory
     * @return array<string, array<int, string>>
     */
    protected function loadPlatforms(string $directory): array
    {
        $vnOfRelease = [];

        foreach ($this->readCopy($directory . '/db/releases_vn') as [$releaseID, $vnID]) {
            $vnOfRelease[$releaseID][] = $vnID;
        }

        $platforms = [];

        foreach ($this->readCopy($directory . '/db/releases_platforms') as [$releaseID, $platform]) {
            $name = self::PLATFORMS[$platform] ?? null;

            if ($name === null) {
                continue;
            }

            foreach ($vnOfRelease[$releaseID] ?? [] as $vnID) {
                $platforms[$vnID][$name] = $name;
            }
        }

        return array_map('array_values', $platforms);
    }

    /**
     * Load each visual novel's linked anime MAL ids keyed by VNDB id.
     *
     * @param string $directory
     * @return array<string, array<int, int>>
     */
    protected function loadAnime(string $directory): array
    {
        // Anime record id → its MAL ids.
        $malOfAnime = [];

        foreach ($this->readCopy($directory . '/db/anime') as $row) {
            $malIDs = $this->parsePgArray($row[6] ?? null); // mal_id (0-indexed column 6)

            if (!empty($malIDs)) {
                $malOfAnime[$row[0]] = array_map('intval', $malIDs);
            }
        }

        $anime = [];

        foreach ($this->readCopy($directory . '/db/vn_anime') as [$vnID, $animeID]) {
            foreach ($malOfAnime[$animeID] ?? [] as $malID) {
                $anime[$vnID][$malID] = $malID;
            }
        }

        return array_map('array_values', $anime);
    }

    /**
     * Load each visual novel's applied tag names keyed by VNDB id.
     *
     * @param string $directory
     * @return array
     */
    protected function loadTags(string $directory): array
    {
        $names = [];

        foreach ($this->readCopy($directory . '/db/tags') as [$id, $category, $defaultSpoiler, , $applicable, $name]) {
            if ($applicable === 't' && (int) $defaultSpoiler === 0 && in_array($category, self::TAG_CATEGORIES, true)) {
                $names[$id] = $name;
            }
        }

        // A tag applies when its non-spoiler votes add up to a positive score.
        $scores = [];

        foreach ($this->readCopy($directory . '/db/tags_vn') as [, $tag, $vnID, , $vote, $spoiler, $ignore, $lie]) {
            if ($ignore === 't' || $lie === 't' || !isset($names[$tag]) || (int) $spoiler > 0) {
                continue;
            }

            $scores[$vnID][$tag] = ($scores[$vnID][$tag] ?? 0) + (int) $vote;
        }

        $tags = [];

        foreach ($scores as $vnID => $voted) {
            foreach ($voted as $tag => $score) {
                if ($score > 0) {
                    $tags[$vnID][] = $names[$tag];
                }
            }
        }

        return $tags;
    }

    /**
     * Load the characters of every visual novel with their voice actors.
     *
     * @param string $directory
     * @return array
     */
    protected function loadCast(string $directory): array
    {
        foreach ($this->readCopy($directory . '/db/chars') as $row) {
            // id image bloodt cup_size sex spoil_sex gender spoil_gender main main_spoil
            // s_bust s_waist s_hip birthday height weight age description
            $this->characters[$row[0]] = [
                'image' => $row[1],
                'bloodType' => empty($row[2]) || $row[2] === 'unknown' ? null : Str::upper($row[2]),
                'bust' => (int) $row[10] ?: null,
                'waist' => (int) $row[11] ?: null,
                'hip' => (int) $row[12] ?: null,
                'birthday' => (int) $row[13],
                'height' => (int) $row[14] ?: null,
                'weight' => (int) $row[15] ?: null,
                'age' => (int) $row[16] ?: null,
                'about' => $this->stripMarkup($row[17]),
                'names' => [],
            ];
        }

        foreach ($this->readCopy($directory . '/db/chars_names') as [$id, $language, $name, $latin]) {
            if (isset($this->characters[$id])) {
                $this->characters[$id]['names'][$language] = ['name' => $name, 'latin' => $latin];
            }
        }

        $cast = [];

        foreach ($this->readCopy($directory . '/db/chars_vns') as [$id, $vnID, , $role]) {
            if (isset($this->characters[$id]) && isset(self::CAST_ROLES[$role])) {
                $cast[$vnID][$id] = $role;
            }
        }

        $seiyuu = [];

        foreach ($this->readCopy($directory . '/db/vn_seiyuu') as [$vnID, $characterID, $aliasID]) {
            $seiyuu[$vnID][$characterID][] = $aliasID;
        }

        return ['cast' => $cast, 'seiyuu' => $seiyuu];
    }

    /**
     * Load the staff of every visual novel with their credited role.
     *
     * @param string $directory
     * @return array
     */
    protected function loadStaff(string $directory): array
    {
        $extlinks = [];

        foreach ($this->readCopy($directory . '/db/extlinks') as [$id, $site, $value]) {
            $extlinks[$id] = [$site, $value];
        }

        $links = $this->staffLinks($directory, $extlinks);
        $producerSites = $this->producerWebsites($directory, $extlinks);

        foreach ($this->readCopy($directory . '/db/staff') as [$id, , $language, $main, $description]) {
            $this->staffMembers[$id] = [
                'language' => $language,
                'main' => $main,
                'about' => $this->stripMarkup($description),
                'birthdate' => $this->parseBirthdate($description),
                'birthPlace' => $this->parseBirthPlace($description),
                'realName' => $this->parseRealName($description),
                'spouse' => $this->parseSpouse($description),
                'studios' => $this->producerLinks($description, $producerSites),
                'urls' => $links[$id] ?? [],
                'names' => [],
            ];
        }

        foreach ($this->readCopy($directory . '/db/staff_alias') as [$id, $aliasID, $name, $latin]) {
            if (isset($this->staffMembers[$id])) {
                $this->staffAliases[$aliasID] = $id;
                $this->staffMembers[$id]['names'][$aliasID] = ['name' => $name, 'latin' => $latin];
            }
        }

        // Fan translations are credited as editions of the visual novel.
        $unofficial = [];

        foreach ($this->readCopy($directory . '/db/vn_editions') as [$vnID, , $editionID, $official]) {
            if ($official !== 't') {
                $unofficial[$vnID . ':' . $editionID] = true;
            }
        }

        $credits = [];

        foreach ($this->readCopy($directory . '/db/vn_staff') as [$vnID, $aliasID, $role, $editionID]) {
            if (!isset(self::STAFF_ROLES[$role]) || isset($unofficial[$vnID . ':' . $editionID])) {
                continue;
            }

            $credits[$vnID][] = [$aliasID, $role];
        }

        return $credits;
    }

    /**
     * Load each staff member's links split by visibility.
     *
     * @param string $directory
     * @return array
     */
    protected function staffLinks(string $directory, array $extlinks): array
    {
        $links = [];

        foreach ($this->readCopy($directory . '/db/staff_extlinks') as [$staffID, $linkID]) {
            [$site, $value] = $extlinks[$linkID] ?? [null, null];
            $template = self::STAFF_LINK_TEMPLATES[$site] ?? null;

            if ($template === null || $value === '') {
                continue;
            }

            $column = self::STAFF_ID_COLUMNS[$site] ?? null;

            if ($column !== null) {
                $links[$staffID]['ids'][$column] = $site === 'imdb' ? 'nm' . $value : $value;

                continue;
            }

            $kind = match (true) {
                in_array($site, self::SOCIAL_STAFF_SITES, true) => 'social',
                in_array($site, self::WEBSITE_STAFF_SITES, true) => 'website',
                default => 'external',
            };

            $links[$staffID][$kind][] = $this->buildUrl($template, $value);
        }

        return $links;
    }

    /**
     * Map each producer to its official website.
     *
     * @param string $directory
     * @param array  $extlinks
     * @return string[]
     */
    protected function producerWebsites(string $directory, array $extlinks): array
    {
        $websites = [];

        foreach ($this->readCopy($directory . '/db/producers_extlinks') as [$producerID, $linkID]) {
            [$site, $value] = $extlinks[$linkID] ?? [null, null];

            if ($site === 'website' && !empty($value) && !isset($websites[$producerID])) {
                $websites[$producerID] = $value;
            }
        }

        return $websites;
    }

    /**
     * Resolve the studios a staff description credits to their websites.
     *
     * @param string|null $description
     * @param string[]    $producerWebsites
     * @return string[]
     */
    protected function producerLinks(?string $description, array $producerWebsites): array
    {
        if (empty($description) || !preg_match_all('#\[url=/(p\d+)\]#', $description, $matches)) {
            return [];
        }

        $urls = [];

        foreach (array_unique($matches[1]) as $producerID) {
            if (isset($producerWebsites[$producerID])) {
                $urls[] = $producerWebsites[$producerID];
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * Parse a real name out of a VNDB staff description.
     *
     * @param string|null $description
     * @return string|null
     */
    protected function parseRealName(?string $description): ?string
    {
        if (empty($description) || !preg_match('/\(\s*(?:real name|also known as|aka)\s*:?\s*([^()]{2,60}?)\s*(?:\(|,|\))/i', $description, $matches)) {
            return null;
        }

        $name = trim($matches[1], " \t\"“”'");

        return $name === '' ? null : $name;
    }

    /**
     * Parse the VNDB id of a staff member's spouse out of their description.
     *
     * @param string|null $description
     * @return string|null
     */
    protected function parseSpouse(?string $description): ?string
    {
        if (empty($description) || !preg_match('#\b(?:married to|spouse[:\s]+|wife(?: is)?|husband(?: is)?)\b[^.]{0,80}?\[url=/(s\d+)\]#i', $description, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Parse a birth place out of a VNDB staff description.
     *
     * @param string|null $description
     * @return string|null
     */
    protected function parseBirthPlace(?string $description): ?string
    {
        if (empty($description) || !preg_match('/\bborn\b[^.]{0,80}?\bin\s+([A-Z][^.\n]*)/', $description, $matches)) {
            return null;
        }

        // Trim the sentence that follows the place.
        $place = preg_replace('/(?:,\s*|\s+)(?:is|was|who|and|but|she|he|they|to|on|as|the)\b.*$/i', '', $matches[1]);
        $place = trim((string) $place, " ,;:\t\n");

        if ($place === '' || strlen($place) > 60) {
            return null;
        }

        return $place;
    }

    /**
     * Index existing anime by MAL id for adaptation linking.
     *
     * @return void
     */
    protected function indexExistingAnime(): void
    {
        $this->animeByMal = Anime::on(self::CONNECTION)->withoutGlobalScopes()
            ->whereNotNull('mal_id')
            ->pluck('id', 'mal_id')
            ->all();
    }

    /**
     * Index existing characters by VNDB id and by name within each game.
     *
     * @return void
     */
    protected function indexExistingCharacters(): void
    {
        $this->charactersByVndb = Character::on(self::CONNECTION)->withoutGlobalScopes()
            ->whereNotNull('vndb_id')
            ->pluck('id', 'vndb_id')
            ->all();

        $gamesByCharacter = [];

        GameCast::on(self::CONNECTION)->withoutGlobalScopes()
            ->whereNotNull('character_id')
            ->select(['id', 'game_id', 'character_id'])
            ->chunk(2000, function ($casts) use (&$gamesByCharacter) {
                foreach ($casts as $cast) {
                    $gamesByCharacter[$cast->character_id][$cast->game_id] = $cast->game_id;
                }
            });

        if (empty($gamesByCharacter)) {
            return;
        }

        Character::on(self::CONNECTION)->withoutGlobalScopes()
            ->with('translations')
            ->whereNull('vndb_id')
            ->whereIn('id', array_keys($gamesByCharacter))
            ->chunk(500, function ($characters) use ($gamesByCharacter) {
                foreach ($characters as $character) {
                    foreach ($this->characterNames($character) as $name) {
                        foreach ($gamesByCharacter[$character->id] as $gameID) {
                            $this->charactersByGame[$gameID][$this->nameKey($name)][] = $character->id;
                        }
                    }
                }
            });
    }

    /**
     * Index existing people by VNDB id and by name.
     *
     * @return void
     */
    protected function indexExistingPeople(): void
    {
        Person::on(self::CONNECTION)->withoutGlobalScopes()
            ->select(['id', 'vndb_id', 'first_name', 'last_name', 'alternative_names'])
            ->chunk(2000, function ($people) {
                foreach ($people as $person) {
                    if (!empty($person->vndb_id)) {
                        $this->peopleByVndb[$person->vndb_id] = $person->id;

                        continue;
                    }

                    foreach ($this->personNames($person) as $name) {
                        $this->peopleByName[$this->nameKey($name)][] = $person->id;
                    }
                }
            });
    }

    /**
     * Every name a catalogued character is known by.
     *
     * @param Character $character
     * @return string[]
     */
    protected function characterNames(Character $character): array
    {
        $names = $character->translations->pluck('name')->all();

        foreach (collect($character->nicknames ?? [])->all() as $nickname) {
            $names[] = $nickname;
        }

        return array_values(array_filter(array_map('trim', $names)));
    }

    /**
     * Every name a catalogued person is known by.
     *
     * @param Person $person
     * @return string[]
     */
    protected function personNames(Person $person): array
    {
        $names = [$person->first_name . ' ' . $person->last_name];

        foreach (collect($person->alternative_names ?? [])->all() as $alternativeName) {
            $names[] = $alternativeName;
        }

        return array_values(array_filter(array_map('trim', $names)));
    }

    /**
     * Build an order-agnostic key for matching a name.
     *
     * @param string $name
     * @return string
     */
    protected function nameKey(string $name): string
    {
        $stripped = preg_replace('/[^\p{L}\p{N}]+/u', ' ', Str::lower($name));
        $tokens = preg_split('/\s+/u', trim((string) $stripped), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        sort($tokens);

        return implode(' ', $tokens);
    }

    /**
     * Build the de-duplication index from every existing game.
     *
     * @return void
     */
    protected function indexExistingGames(): void
    {
        Game::on(self::CONNECTION)->withoutGlobalScopes()
            ->select(['id', 'igdb_id', 'igdb_slug', 'vndb_id', 'original_title', 'website_urls', 'published_at'])
            ->chunk(2000, function ($games) {
                foreach ($games as $game) {
                    if (!empty($game->vndb_id)) {
                        $this->index['vndb'][$game->vndb_id] = $game->id;
                    }

                    if (!empty($game->igdb_slug)) {
                        $this->index['igdbSlug'][$game->igdb_slug] = $game->id;
                    }

                    if (!empty($game->igdb_id)) {
                        $this->index['igdbId'][$game->igdb_id] = $game->id;
                    }

                    foreach (collect($game->website_urls ?? [])->all() as $url) {
                        $appID = $this->steamAppID($url);

                        if ($appID !== null) {
                            $this->index['steam'][$appID] = $game->id;
                        }
                    }

                    if (!empty($game->original_title) && $game->published_at !== null) {
                        $this->index['titleYear'][$this->titleKey($game->original_title, (int) $game->published_at->year)] = $game->id;
                    }
                }
            });
    }

    /**
     * Resolve an existing game id for a visual novel, in descending order of reliability.
     *
     * @param string     $vndbID
     * @param array      $links
     * @param array      $titles
     * @param int|null   $year
     * @return int|null
     */
    protected function resolveExisting(string $vndbID, array $links, array $titles, ?int $year): ?int
    {
        if (isset($this->index['vndb'][$vndbID])) {
            return $this->index['vndb'][$vndbID];
        }

        foreach ($links['steam'] ?? [] as $appID) {
            if (isset($this->index['steam'][$appID])) {
                return $this->index['steam'][$appID];
            }
        }

        foreach ($links['igdb'] ?? [] as $slug) {
            if (isset($this->index['igdbSlug'][$slug])) {
                return $this->index['igdbSlug'][$slug];
            }
        }

        if ($year !== null) {
            foreach ($titles as $title) {
                foreach ([$title['title'], $title['latin']] as $candidate) {
                    if (!empty($candidate) && isset($this->index['titleYear'][$this->titleKey($candidate, $year)])) {
                        return $this->index['titleYear'][$this->titleKey($candidate, $year)];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Insert or enrich the game for a visual novel.
     *
     * @return void
     */
    protected function upsert(string $vndbID, ?string $image, string $olang, int $devstatus, ?string $alias, ?string $description, array $titles, array $release, array $links, array $studios, array $platforms, array $animeMalIDs, ?int $existingID, ?int $sourceID, ?int $mediaTypeID, array $extras): void
    {
        $publishedAt = $release['released'] ?? null;
        $tvRatingID = $this->resolveTvRating($release['minage'] ?? null);
        $names = $this->resolveTitles($titles, $olang, $vndbID);

        $attributes = [
            'vndb_id' => $vndbID,
            'original_title' => $names['original'],
            'title' => $names['title'],
            'synopsis' => $this->stripMarkup($description),
            'synonym_titles' => $names['synonyms'],
            'website_urls' => array_values(array_unique(array_merge(
                ['https://vndb.org/' . $vndbID],
                $links['urls'] ?? [],
            ))),
            'country_id' => $this->resolveCountry($olang),
            'source_id' => $sourceID,
            'media_type_id' => $mediaTypeID,
            'status_id' => $this->resolveStatus($publishedAt, $devstatus)?->id,
            'tv_rating_id' => $tvRatingID,
            'is_nsfw' => ($release['minage'] ?? 0) >= 18,
            'published_at' => $publishedAt,
            'publication_day' => $publishedAt?->dayOfWeek,
            'publication_season' => $publishedAt ? season_of_year($publishedAt)->value : null,
            ...$names['localized'],
        ];

        // A matched game already carries authoritative IGDB data; only link the
        // VNDB entry and backfill values it is missing, leaving titles and studios
        // untouched to avoid duplicates.
        if ($existingID !== null) {
            $game = Game::on(self::CONNECTION)->withoutGlobalScopes()->find($existingID);

            if ($game === null) {
                return;
            }

            $game->vndb_id = $vndbID;
            $game->website_urls = array_values(array_unique(array_merge(collect($game->website_urls ?? [])->all(), $attributes['website_urls'])));
            $game->published_at ??= $publishedAt;

            // An unrated game still takes VNDB's rating.
            if ($this->isUnratedTvRating($game->tv_rating_id) && !$this->isUnratedTvRating($tvRatingID)) {
                $game->tv_rating_id = $tvRatingID;
            }

            $this->measure('game write', fn () => $game->save());
            $this->index['vndb'][$vndbID] = $game->id;
        } else {
            $game = $this->measure('game write', function () use ($attributes, $studios, $platforms, $publishedAt, $image, $olang, $extras) {
                $game = Game::on(self::CONNECTION)->withoutGlobalScopes()->create($attributes);

                $this->attachStudios($game, $studios);
                $this->attachPlatforms($game, $platforms, $publishedAt);
                $this->attachLanguages($game, $olang, $extras['languages']);
                $this->addPoster($game, $image);

                return $game;
            });

            $this->index['vndb'][$vndbID] = $game->id;
        }

        // Cast, staff, tags and the anime link are enrichment IGDB never provides.
        $this->measure('anime link', fn () => $this->attachAnime($game, $animeMalIDs));
        $this->measure('tags', fn () => $this->attachTags($game, $extras['tags']));
        $this->measure('cast', fn () => $this->attachCast($game, $extras['cast'], $extras['seiyuu']));
        $this->measure('staff', fn () => $this->attachCredits($game, $extras['credits']));
    }

    /**
     * Pick a human-readable title for reporting a visual novel.
     *
     * @param array  $titles
     * @param string $olang
     * @return string
     */
    protected function displayTitle(array $titles, string $olang): string
    {
        $english = null;
        $original = null;

        foreach ($titles as $title) {
            if ($title['lang'] === 'en' && $title['official']) {
                $english = $title['title'];
            }

            if ($title['lang'] === $olang) {
                $original = $title['latin'] ?: $title['title'];
            }
        }

        return $english ?? $original ?? ($titles[0]['latin'] ?? $titles[0]['title'] ?? '?');
    }

    /**
     * Resolve the default, original, localized, and synonym titles for a visual novel.
     *
     * @param array  $titles
     * @param string $olang
     * @param string $vndbID
     * @return array{title: string, original: string, localized: array, synonyms: array<string>}
     */
    protected function resolveTitles(array $titles, string $olang, string $vndbID): array
    {
        $original = null;
        $english = null;
        $localized = [];
        $synonyms = [];

        foreach ($titles as $title) {
            $native = $title['title'];
            $romanized = $title['latin'];

            // VNDB leaves `latin` empty when the title is already romanized.
            if ($title['lang'] === $olang) {
                $original = $romanized ?: $native;
            }

            if ($title['lang'] === 'en' && $title['official']) {
                $english = $native;
            }

            $locale = $this->resolveLocale($title['lang']);

            if ($locale !== null && $locale !== 'en') {
                $localized[$locale] = ['title' => $native, 'synopsis' => null];
            }

            foreach ([$native, $romanized] as $candidate) {
                if (!empty($candidate)) {
                    $synonyms[] = $candidate;
                }
            }
        }

        $original ??= $english ?? $vndbID;
        $title = $english ?? $original;

        return [
            'title' => $title,
            'original' => $original,
            'localized' => $localized,
            'synonyms' => array_values(array_unique(array_filter(
                $synonyms,
                fn ($synonym) => $synonym !== $title && $synonym !== $original,
            ))),
        ];
    }

    /**
     * Attach the developers and publishers, mirroring the scraper's studio handling.
     *
     * @param Game  $game
     * @param array $studios
     * @return void
     */
    protected function attachStudios(Game $game, array $studios): void
    {
        if (empty($studios)) {
            return;
        }

        $this->lookups['studio'] ??= $this->namedIDs(Studio::on(self::CONNECTION)->withoutGlobalScopes()->pluck('id', 'name'));

        $rows = [];

        foreach ($studios as $studio) {
            $key = Str::lower($studio['name']);

            $this->lookups['studio'][$key] ??= Studio::on(self::CONNECTION)->withoutGlobalScopes()
                ->firstOrCreate(['name' => $studio['name']], ['type' => StudioType::Game])
                ->id;

            $rows[] = [
                'studio_id' => $this->lookups['studio'][$key],
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'is_developer' => $studio['developer'],
                'is_publisher' => $studio['publisher'],
            ];
        }

        $this->insertMany(MediaStudio::TABLE_NAME, $rows);
    }

    /**
     * Attach the languages a visual novel is voiced and released in.
     *
     * @param Game   $game
     * @param string $olang
     * @param array  $languages
     * @return void
     */
    protected function attachLanguages(Game $game, string $olang, array $languages): void
    {
        $morphClass = $game->getMorphClass();
        $rows = [];

        // VNDB voices a release 0 unknown, 1 never, 2 ero only, 3 partly, 4 fully.
        $audioID = ($languages['voiced'] ?? 0) >= 2 ? $this->languageID($olang) : null;

        if ($audioID !== null) {
            $rows[] = ['model_id' => $game->id, 'model_type' => $morphClass, 'language_id' => $audioID, 'type' => LanguageSupportType::Audio];
        }

        foreach ($languages['interface'] ?? [] as $language) {
            $languageID = $this->languageID($language);

            if ($languageID !== null) {
                $rows[$languageID] = ['model_id' => $game->id, 'model_type' => $morphClass, 'language_id' => $languageID, 'type' => LanguageSupportType::Interface];
            }
        }

        $this->insertMany(MediaLanguage::TABLE_NAME, array_values($rows));
    }

    /**
     * Attach the release platforms.
     *
     * @param Game        $game
     * @param array       $platforms
     * @param Carbon|null $releasedAt
     * @return void
     */
    protected function attachPlatforms(Game $game, array $platforms, ?Carbon $releasedAt): void
    {
        if (empty($platforms)) {
            return;
        }

        $this->lookups['platform'] ??= $this->namedIDs(Platform::on(self::CONNECTION)->withoutGlobalScopes()->pluck('id', 'original_name'));

        $rows = [];

        foreach ($platforms as $name) {
            $key = Str::lower($name);

            $this->lookups['platform'][$key] ??= Platform::on(self::CONNECTION)->withoutGlobalScopes()
                ->firstOrCreate(['original_name' => $name], ['name' => $name, 'generation' => 0])
                ->id;

            $rows[] = [
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'platform_id' => $this->lookups['platform'][$key],
                'region' => null,
                'released_at' => $releasedAt,
            ];
        }

        $this->insertMany(MediaPlatform::TABLE_NAME, $rows);
    }

    /**
     * Link the visual novel to its anime adaptations already in the catalog.
     *
     * @param Game            $game
     * @param array<int, int> $malIDs
     * @return void
     */
    protected function attachAnime(Game $game, array $malIDs): void
    {
        $animeIDs = array_values(array_filter(array_map(fn ($malID) => $this->animeByMal[$malID] ?? null, $malIDs)));

        if (empty($animeIDs)) {
            return;
        }

        $relationID = $this->relationID('Adaptation');
        $animeMorph = (new Anime)->getMorphClass();
        $rows = [];

        foreach ($animeIDs as $animeID) {
            $rows[] = [
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'relation_id' => $relationID,
                'related_id' => $animeID,
                'related_type' => $animeMorph,
            ];
        }

        $this->insertMany(MediaRelation::TABLE_NAME, $rows);
    }

    /**
     * Attach the applied tags as genres, themes or tags.
     *
     * @param Game     $game
     * @param string[] $names
     * @return void
     */
    protected function attachTags(Game $game, array $names): void
    {
        if (empty($names)) {
            return;
        }

        $this->lookups['genre'] ??= $this->namedIDs(Genre::on(self::CONNECTION)->withoutGlobalScopes()->pluck('id', 'name'));
        $this->lookups['theme'] ??= $this->namedIDs(Theme::on(self::CONNECTION)->withoutGlobalScopes()->pluck('id', 'name'));
        $this->lookups['tag'] ??= $this->namedIDs(Tag::on(self::CONNECTION)->withoutGlobalScopes()->pluck('id', 'name'));

        $morphClass = $game->getMorphClass();
        $genres = [];
        $themes = [];
        $tags = [];

        foreach ($names as $name) {
            $key = Str::lower($name);

            if (isset($this->lookups['genre'][$key])) {
                $genres[] = ['model_id' => $game->id, 'model_type' => $morphClass, 'genre_id' => $this->lookups['genre'][$key]];

                continue;
            }

            if (isset($this->lookups['theme'][$key])) {
                $themes[] = ['model_id' => $game->id, 'model_type' => $morphClass, 'theme_id' => $this->lookups['theme'][$key]];

                continue;
            }

            $this->lookups['tag'][$key] ??= Tag::on(self::CONNECTION)->withoutGlobalScopes()
                ->firstOrCreate(['name' => $name])
                ->id;

            $tags[] = ['taggable_id' => $game->id, 'taggable_type' => $morphClass, 'tag_id' => $this->lookups['tag'][$key]];
        }

        $this->insertMany(MediaGenre::TABLE_NAME, $genres);
        $this->insertMany(MediaTheme::TABLE_NAME, $themes);
        $this->insertMany(MediaTag::TABLE_NAME, $tags);
    }

    /**
     * Insert the rows a media table is missing.
     *
     * @param string $table
     * @param array  $rows
     * @return void
     */
    protected function insertMany(string $table, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $now = now();
        $rows = array_map(fn (array $row) => $row + ['created_at' => $now, 'updated_at' => $now], $rows);

        DB::connection(self::CONNECTION)->table($table)->insertOrIgnore($rows);
    }

    /**
     * Attach the visual novel's characters and their voice actors.
     *
     * @param Game  $game
     * @param array $cast
     * @param array $seiyuu
     * @return void
     */
    protected function attachCast(Game $game, array $cast, array $seiyuu): void
    {
        $rows = [];

        foreach ($cast as $characterID => $role) {
            $character = $this->resolveCharacter($game, (string) $characterID);
            $castRoleID = $this->castRoleID(self::CAST_ROLES[$role]);

            if ($character === null || $castRoleID === null) {
                continue;
            }

            $actors = $seiyuu[$characterID] ?? [];

            if (empty($actors)) {
                $rows[] = ['game_id' => $game->id, 'character_id' => $character, 'person_id' => null, 'cast_role_id' => $castRoleID, 'language_id' => null];

                continue;
            }

            foreach ($actors as $aliasID) {
                $staffID = $this->staffAliases[$aliasID] ?? null;

                if ($staffID === null) {
                    continue;
                }

                $rows[] = [
                    'game_id' => $game->id,
                    'character_id' => $character,
                    'person_id' => $this->resolvePerson($staffID),
                    'cast_role_id' => $castRoleID,
                    'language_id' => $this->languageID($this->staffMembers[$staffID]['language'] ?? ''),
                ];
            }
        }

        $this->insertMany(GameCast::TABLE_NAME, $rows);
    }

    /**
     * Attach the visual novel's credited staff.
     *
     * @param Game  $game
     * @param array $credits
     * @return void
     */
    protected function attachCredits(Game $game, array $credits): void
    {
        $morphClass = $game->getMorphClass();
        $rows = [];

        foreach ($credits as [$aliasID, $role]) {
            $staffID = $this->staffAliases[$aliasID] ?? null;
            $personID = $staffID === null ? null : $this->resolvePerson($staffID);
            $staffRoleID = $this->staffRoleID(self::STAFF_ROLES[$role]);

            if ($personID === null || $staffRoleID === null) {
                continue;
            }

            $rows[$personID . ':' . $staffRoleID] = [
                'model_id' => $game->id,
                'model_type' => $morphClass,
                'person_id' => $personID,
                'staff_role_id' => $staffRoleID,
            ];
        }

        $this->insertMany(MediaStaff::TABLE_NAME, array_values($rows));
    }

    /**
     * Link visual novels that VNDB relates to one another.
     *
     * @param string $directory
     * @return void
     */
    protected function attachRelations(string $directory): void
    {
        $this->info('Linking related visual novels...');

        $morphClass = (new Game)->getMorphClass();
        $rows = [];

        foreach ($this->readCopy($directory . '/db/vn_relations') as [$vnID, $relatedVnID, $relation]) {
            $gameID = $this->index['vndb'][$vnID] ?? null;
            $relatedGameID = $this->index['vndb'][$relatedVnID] ?? null;

            if ($gameID === null || $relatedGameID === null || !isset(self::RELATIONS[$relation])) {
                continue;
            }

            $rows[] = [
                'model_id' => $gameID,
                'model_type' => $morphClass,
                'relation_id' => $this->relationID(self::RELATIONS[$relation]),
                'related_id' => $relatedGameID,
                'related_type' => $morphClass,
            ];
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            $this->insertMany(MediaRelation::TABLE_NAME, $chunk);
        }

        $this->info(count($rows) . ' relation(s) linked.');
    }

    /**
     * Link the spouses VNDB credits to one another.
     *
     * @return void
     */
    protected function attachMarriages(): void
    {
        $this->info('Linking spouses...');

        $rows = [];

        foreach ($this->staffMembers as $staffID => $member) {
            $personID = $this->peopleByVndb[$staffID] ?? null;
            $spouseID = $this->peopleByVndb[$member['spouse'] ?? ''] ?? null;

            if ($personID === null || $spouseID === null || $personID === $spouseID) {
                continue;
            }

            foreach ([[$personID, $spouseID], [$spouseID, $personID]] as [$left, $right]) {
                $rows[$left . ':' . $right] = [
                    'person_id' => $left,
                    'related_person_id' => $right,
                    'type' => PersonRelationshipType::Spouse,
                ];
            }
        }

        $this->insertMany(PersonRelationship::TABLE_NAME, array_values($rows));
        $this->info(count($rows) . ' spouse link(s) written.');
    }

    /**
     * Resolve the catalogued character for a VNDB character.
     *
     * @param Game   $game
     * @param string $characterID
     * @return int|null
     */
    protected function resolveCharacter(Game $game, string $characterID): ?int
    {
        if (isset($this->charactersByVndb[$characterID])) {
            return $this->charactersByVndb[$characterID];
        }

        $data = $this->characters[$characterID] ?? null;
        $names = $this->vndbCharacterNames($data['names'] ?? []);

        if ($data === null || $names === null) {
            return null;
        }

        $matches = $this->matchingIDs($this->charactersByGame[$game->id] ?? [], $names['keys']);

        if (count($matches) > 1) {
            $this->ambiguous['characters'][] = $names['name'] . ' [' . $characterID . '] matched characters ' . implode(', ', $matches);

            return null;
        }

        if (count($matches) === 1) {
            Character::on(self::CONNECTION)->withoutGlobalScopes()
                ->whereKey($matches[0])
                ->update(['vndb_id' => $characterID]);

            return $this->charactersByVndb[$characterID] = $matches[0];
        }

        $character = Character::on(self::CONNECTION)->withoutGlobalScopes()->create(array_filter([
            'vndb_id' => $characterID,
            'name' => $names['name'],
            'about' => $data['about'],
            'blood_type' => $data['bloodType'],
            'bust' => $data['bust'],
            'waist' => $data['waist'],
            'hip' => $data['hip'],
            'height' => $data['height'],
            'weight' => $data['weight'],
            'age' => $data['age'],
            ...$this->resolveBirthday($data['birthday']),
            ...($names['native'] === null ? [] : ['ja' => ['name' => $names['native'], 'about' => null]]),
        ], fn ($value) => $value !== null));

        $this->addProfileImage($character, $data['image']);

        return $this->charactersByVndb[$characterID] = $character->id;
    }

    /**
     * Resolve the catalogued person for a VNDB staff member.
     *
     * @param string $staffID
     * @return int|null
     */
    protected function resolvePerson(string $staffID): ?int
    {
        if (isset($this->peopleByVndb[$staffID])) {
            return $this->peopleByVndb[$staffID];
        }

        // An ambiguous staff member is credited many times over.
        if (isset($this->unresolvedPeople[$staffID])) {
            return null;
        }

        $data = $this->staffMembers[$staffID] ?? null;
        $names = $this->vndbStaffNames($data);

        if ($data === null || $names === null) {
            $this->unresolvedPeople[$staffID] = true;

            return null;
        }

        $matches = $this->matchingIDs($this->peopleByName, $names['keys']);
        $birthdate = $data['birthdate'];

        // VNDB's main alias is not always the name the person is credited under.
        $fromAlias = empty($matches);

        if ($fromAlias) {
            $matches = $this->matchingIDs($this->peopleByName, $names['aliasKeys']);
        }

        if (count($matches) > 1) {
            $matches = $this->narrowByIdentity($matches, $data['urls'] ?? [], $birthdate);
        }

        if (count($matches) > 1) {
            // Stage names and one-word names point at unrelated people.
            if ($fromAlias || $this->isWeakName($names['keys'])) {
                $this->weakMatches[$staffID] = $names['romanized'];
                $matches = [];
            } else {
                $this->ambiguous['people'][] = $names['romanized'] . ' [' . $staffID . '] matched people ' . implode(', ', $matches);
                $this->unresolvedPeople[$staffID] = true;

                return null;
            }
        }

        if (count($matches) === 1) {
            $this->enrichPerson($matches[0], $staffID, $data, $names, $birthdate);

            return $this->peopleByVndb[$staffID] = $matches[0];
        }

        $alternativeNames = $names['alternatives'];

        if ($data['realName'] !== null && !in_array($data['realName'], $alternativeNames, true)) {
            $alternativeNames[] = $data['realName'];
        }

        $person = Person::on(self::CONNECTION)->withoutGlobalScopes()->create([
            'vndb_id' => $staffID,
            ...$data['urls']['ids'] ?? [],
            'first_name' => $names['first'],
            'last_name' => $names['last'],
            'given_name' => $names['given'],
            'family_name' => $names['family'],
            'alternative_names' => $alternativeNames,
            'about' => $data['about'],
            'birthdate' => $birthdate?->toDateString(),
            'birth_place' => $data['birthPlace'],
            'astrological_sign' => $birthdate === null ? null : AstrologicalSign::getFromDate($birthdate)?->value,
            'website_urls' => array_values(array_unique(array_merge(
                $data['urls']['website'] ?? [],
                $data['studios'],
            ))),
            'social_urls' => $data['urls']['social'] ?? [],
            'external_urls' => array_values(array_unique(array_merge(
                ['https://vndb.org/' . $staffID],
                $data['urls']['external'] ?? [],
            ))),
        ]);

        return $this->peopleByVndb[$staffID] = $person->id;
    }

    /**
     * Stamp a matched person and fill only the columns it is missing.
     *
     * @param int         $personID
     * @param string      $staffID
     * @param array       $data
     * @param array       $names
     * @param Carbon|null $birthdate
     * @return void
     */
    protected function enrichPerson(int $personID, string $staffID, array $data, array $names, ?Carbon $birthdate): void
    {
        $person = Person::on(self::CONNECTION)->withoutGlobalScopes()->find($personID);

        if ($person === null) {
            return;
        }

        $person->vndb_id = $staffID;

        foreach ($data['urls']['ids'] ?? [] as $column => $value) {
            $person->{$column} ??= $value;
        }

        $person->birthdate ??= $birthdate;
        $person->birth_place ??= $data['birthPlace'];
        $person->about ??= $data['about'];
        $person->astrological_sign ??= $birthdate === null ? null : AstrologicalSign::getFromDate($birthdate)?->value;

        $person->website_urls = $this->mergeUrls($person->website_urls, array_merge($data['urls']['website'] ?? [], $data['studios']));
        $person->social_urls = $this->mergeUrls($person->social_urls, $data['urls']['social'] ?? []);
        $person->external_urls = $this->mergeUrls($person->external_urls, array_merge(['https://vndb.org/' . $staffID], $data['urls']['external'] ?? []));

        $aliases = collect($person->alternative_names ?? [])->filter()->all();
        $incoming = $names['alternatives'];

        if ($data['realName'] !== null) {
            $incoming[] = $data['realName'];
        }

        $person->alternative_names = array_values(array_unique(array_merge($aliases, $incoming)));

        $person->save();
    }

    /**
     * Merge new URLs into a stored list without duplicating them.
     *
     * @param mixed    $stored
     * @param string[] $incoming
     * @return string[]
     */
    protected function mergeUrls(mixed $stored, array $incoming): array
    {
        $urls = collect($stored ?? [])->filter()->all();
        $seen = [];

        foreach ($urls as $url) {
            $seen[$this->urlKey($url)] = true;
        }

        foreach ($incoming as $url) {
            if (!isset($seen[$this->urlKey($url)])) {
                $seen[$this->urlKey($url)] = true;
                $urls[] = $url;
            }
        }

        return array_values($urls);
    }

    /**
     * Narrow name matches to the candidates sharing a link or birthdate.
     *
     * @param int[]       $matches
     * @param array       $urls
     * @param Carbon|null $birthdate
     * @return int[]
     */
    protected function narrowByIdentity(array $matches, array $urls, ?Carbon $birthdate): array
    {
        $links = [];

        foreach (array_merge($urls['website'] ?? [], $urls['social'] ?? [], $urls['external'] ?? []) as $url) {
            $links[$this->urlKey($url)] = true;
        }

        if (empty($links) && $birthdate === null) {
            return $matches;
        }

        $candidates = Person::on(self::CONNECTION)->withoutGlobalScopes()
            ->whereKey($matches)
            ->get(['id', 'birthdate', 'website_urls', 'social_urls', 'external_urls']);

        $best = 0;
        $scored = [];

        foreach ($candidates as $candidate) {
            $score = 0;

            if ($birthdate !== null && $candidate->birthdate?->isSameDay($birthdate)) {
                $score += 2;
            }

            $candidateUrls = collect($candidate->website_urls ?? [])
                ->merge(collect($candidate->social_urls ?? []))
                ->merge(collect($candidate->external_urls ?? []));

            foreach ($candidateUrls->all() as $url) {
                if (isset($links[$this->urlKey($url)])) {
                    $score++;
                }
            }

            $scored[$candidate->id] = $score;
            $best = max($best, $score);
        }

        if ($best === 0) {
            return $matches;
        }

        return array_values(array_keys(array_filter($scored, fn (int $score) => $score === $best)));
    }

    /**
     * Build a comparable key for a profile URL.
     *
     * @param string|null $url
     * @return string
     */
    protected function urlKey(?string $url): string
    {
        $key = Str::lower(trim((string) $url));
        $key = preg_replace('#^https?://#', '', $key);
        $key = preg_replace('#^www\.#', '', (string) $key);

        // VNDB renders Twitter as x.com while MyAnimeList still stores twitter.com.
        $key = preg_replace('#^x\.com/#', 'twitter.com/', (string) $key);

        return rtrim((string) $key, '/');
    }

    /**
     * Parse a birth date out of a VNDB staff description.
     *
     * @param string|null $description
     * @return Carbon|null
     */
    protected function parseBirthdate(?string $description): ?Carbon
    {
        if (empty($description)) {
            return null;
        }

        $months = 'January|February|March|April|May|June|July|August|September|October|November|December';

        if (!preg_match('/\b(?:born|birth(?:day|date)?)\b[^.]{0,60}?\b(' . $months . ')\s+(\d{1,2}),?\s+(\d{4})/i', $description, $matches)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('F j Y', $matches[1] . ' ' . $matches[2] . ' ' . $matches[3])?->startOfDay();
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Determine whether a name is too generic to match on.
     *
     * @param string[] $keys
     * @return bool
     */
    protected function isWeakName(array $keys): bool
    {
        foreach ($keys as $key) {
            if (str_contains($key, ' ')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Collect the ids indexed under any of the given name keys.
     *
     * @param array    $index
     * @param string[] $keys
     * @return int[]
     */
    protected function matchingIDs(array $index, array $keys): array
    {
        $matches = [];

        foreach ($keys as $key) {
            $matches = array_merge($matches, $index[$key] ?? []);
        }

        return array_values(array_unique($matches));
    }

    /**
     * Resolve a VNDB character's display name with its native spelling.
     *
     * @param array $names
     * @return array|null
     */
    protected function vndbCharacterNames(array $names): ?array
    {
        if (empty($names)) {
            return null;
        }

        $entry = $names['ja'] ?? $names['en'] ?? reset($names);
        $native = $names['ja']['name'] ?? null;
        $name = $entry['latin'] ?: $entry['name'];

        if (empty($name)) {
            return null;
        }

        $keys = [$this->nameKey($name)];

        if (!empty($native)) {
            $keys[] = $this->nameKey($native);
        }

        return [
            'name' => $name,
            'native' => $native === $name ? null : $native,
            'keys' => array_values(array_unique($keys)),
        ];
    }

    /**
     * Resolve a VNDB staff member's name parts and aliases.
     *
     * @param array|null $data
     * @return array|null
     */
    protected function vndbStaffNames(?array $data): ?array
    {
        $aliases = $data['names'] ?? [];
        $main = $aliases[$data['main'] ?? ''] ?? (reset($aliases) ?: null);

        if ($main === null) {
            return null;
        }

        $romanized = $this->stripQualifier($main['latin'] ?: $main['name']);
        $native = $main['latin'] === null ? null : $this->stripQualifier($main['name']);
        $familyFirst = in_array($data['language'] ?? '', self::FAMILY_NAME_FIRST, true);

        [$first, $last] = $this->splitName($romanized, $familyFirst);
        [$given, $family] = $native === null ? [null, null] : $this->splitName($native, $familyFirst);

        $alternativeNames = [];
        $aliasKeys = [];

        foreach ($aliases as $alias) {
            foreach ([$alias['latin'], $alias['name']] as $value) {
                if (empty($value)) {
                    continue;
                }

                $value = $this->stripQualifier($value);
                $aliasKeys[] = $this->nameKey($value);

                if ($value !== $romanized && $value !== $native) {
                    $alternativeNames[] = $value;
                }
            }
        }

        // Matching on every alias at once pulls in unrelated people.
        $keys = [$this->nameKey($romanized)];

        if (!empty($native)) {
            $keys[] = $this->nameKey($native);
        }

        return [
            'aliasKeys' => array_values(array_diff(array_unique($aliasKeys), $keys)),
            'romanized' => $romanized,
            'first' => $first,
            'last' => $last,
            'given' => $given,
            'family' => $family,
            'alternatives' => array_values(array_unique($alternativeNames)),
            'keys' => array_values(array_unique($keys)),
        ];
    }

    /**
     * Remove the qualifier VNDB appends to tell two people apart.
     *
     * @param string $name
     * @return string
     */
    public function stripQualifier(string $name): string
    {
        if (!preg_match('/\s*\(([^)]*)\)\s*$/u', $name, $matches)) {
            return $name;
        }

        if (!in_array(Str::lower(trim($matches[1])), self::NAME_QUALIFIERS, true)) {
            return $name;
        }

        $stripped = trim(Str::beforeLast($name, $matches[0]));

        return $stripped === '' ? $name : $stripped;
    }

    /**
     * Split a full name into its given and family parts.
     *
     * @param string $name
     * @param bool   $familyFirst
     * @return array
     */
    protected function splitName(string $name, bool $familyFirst): array
    {
        $tokens = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($tokens) < 2) {
            return [$tokens[0] ?? $name, null];
        }

        $head = array_shift($tokens);
        $tail = implode(' ', $tokens);

        return $familyFirst ? [$tail, $head] : [$head, $tail];
    }

    /**
     * Resolve the birthday attributes from a VNDB month-and-day integer.
     *
     * @param int $birthday
     * @return array
     */
    protected function resolveBirthday(int $birthday): array
    {
        $month = intdiv($birthday, 100);
        $day = $birthday % 100;

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return [];
        }

        return [
            'birth_month' => $month,
            'birth_day' => $day,
            'astrological_sign' => AstrologicalSign::getFromDate(Carbon::create(2000, $month, $day))?->value,
        ];
    }

    /**
     * Strip VNDB's markup from a description.
     *
     * @param string|null $text
     * @return string|null
     */
    protected function stripMarkup(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }

        $text = preg_replace('/\[url=[^]]*]([^[]*)\[\/url]/u', '$1', $text);
        $text = preg_replace('/\[\/?(?:spoiler|quote|raw|code|b|i|s|u)]/u', '', (string) $text);

        return trim((string) $text) ?: null;
    }

    /**
     * Index a name-keyed id list by its lowercased name.
     *
     * @param Collection $named
     * @return int[]
     */
    protected function namedIDs(Collection $named): array
    {
        $ids = [];

        foreach ($named as $name => $id) {
            $ids[Str::lower((string) $name)] = $id;
        }

        return $ids;
    }

    /**
     * Resolve a cast role id by name.
     *
     * @param string $name
     * @return int|null
     */
    protected function castRoleID(string $name): ?int
    {
        return $this->cachedID('castRole', $name, fn () => CastRole::on(self::CONNECTION)->withoutGlobalScopes()->where('name', '=', $name)->value('id'));
    }

    /**
     * Resolve a staff role id by name.
     *
     * @param string $name
     * @return int|null
     */
    protected function staffRoleID(string $name): ?int
    {
        return $this->cachedID('staffRole', $name, fn () => StaffRole::on(self::CONNECTION)->withoutGlobalScopes()->where('name', '=', $name)->value('id'));
    }

    /**
     * Resolve a relation id by name.
     *
     * @param string $name
     * @return int|null
     */
    protected function relationID(string $name): ?int
    {
        return $this->cachedID('relation', $name, fn () => Relation::on(self::CONNECTION)->firstOrCreate(['name' => $name])->id);
    }

    /**
     * Resolve a language id from a VNDB language.
     *
     * @param string $language
     * @return int|null
     */
    protected function languageID(string $language): ?int
    {
        $locale = $this->resolveLocale($language);

        if ($locale === null) {
            return null;
        }

        return $this->cachedID('language', $locale, fn () => Language::on(self::CONNECTION)->withoutGlobalScopes()->where('code', '=', $locale)->value('id'));
    }

    /**
     * Resolve and remember an id by name.
     *
     * @param string   $kind
     * @param string   $name
     * @param callable $resolve
     * @return int|null
     */
    protected function cachedID(string $kind, string $name, callable $resolve): ?int
    {
        if (!array_key_exists($name, $this->lookups[$kind] ?? [])) {
            $this->lookups[$kind][$name] = $resolve();
        }

        return $this->lookups[$kind][$name];
    }

    /**
     * Report the names that could not be matched without guessing.
     *
     * @return void
     */
    protected function reportAmbiguous(): void
    {
        foreach ($this->ambiguous as $kind => $names) {
            if (empty($names)) {
                continue;
            }

            $names = array_values(array_unique($names));
            $this->warn(count($names) . ' ' . $kind . ' matched more than one catalogued record and were skipped:');

            foreach (array_slice($names, 0, 25) as $name) {
                $this->line('  ' . $name);
            }
        }

        if (!empty($this->weakMatches)) {
            $this->warn(count($this->weakMatches) . ' staff had a name too generic to match and were inserted as new people:');
            $this->line('  ' . implode(', ', array_slice(array_unique($this->weakMatches), 0, 25)));
        }
    }

    /**
     * Download and attach the VNDB cover when the game has no poster yet.
     *
     * @param Game        $game
     * @param string|null $image
     * @return void
     */
    protected function addPoster(Game $game, ?string $image): void
    {
        if (empty($image) || !empty($game->getFirstMedia(MediaCollection::Poster))) {
            return;
        }

        try {
            $this->measure('  of which images', fn () => $game->updateImageMedia(MediaCollection::Poster(), $this->imageUrl($image), $game->original_title));
        } catch (Exception $exception) {
            logger()->channel('stderr')->error($exception->getMessage());
        }
    }

    /**
     * Download and attach the VNDB portrait of a character.
     *
     * @param Character   $character
     * @param string|null $image
     * @return void
     */
    protected function addProfileImage(Character $character, ?string $image): void
    {
        if (empty($image)) {
            return;
        }

        try {
            $this->measure('  of which images', fn () => $character->updateImageMedia(MediaCollection::Profile(), $this->imageUrl($image), $character->name));
        } catch (Exception $exception) {
            logger()->channel('stderr')->error($exception->getMessage());
        }
    }

    /**
     * Build the URL of a VNDB image id.
     *
     * @param string $image
     * @return string
     */
    protected function imageUrl(string $image): string
    {
        $number = (int) substr($image, 2);
        $directory = str_pad((string) ($number % 100), 2, '0', STR_PAD_LEFT);

        return 'https://t.vndb.org/' . substr($image, 0, 2) . '/' . $directory . '/' . $number . '.jpg';
    }

    /**
     * Parse a VNDB release date integer (YYYYMMDD, with zeroed unknown parts).
     *
     * @param int $date
     * @return Carbon|null
     */
    protected function parseReleaseDate(int $date): ?Carbon
    {
        if ($date <= 0 || $date >= 99999999) {
            return null;
        }

        $year = intdiv($date, 10000);
        $month = intdiv($date % 10000, 100) ?: 1;
        $day = $date % 100 ?: 1;

        try {
            return Carbon::create($year, $month, $day)?->startOfDay();
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Determine whether a TV rating counts as unrated.
     *
     * @param int|null $tvRatingID
     * @return bool
     */
    protected function isUnratedTvRating(?int $tvRatingID): bool
    {
        $this->unratedTvRatingID ??= TvRating::on(self::CONNECTION)->withoutGlobalScopes()
            ->where('name', '=', 'NR')
            ->value('id');

        return $tvRatingID === null || $tvRatingID === $this->unratedTvRatingID;
    }

    /**
     * Resolve the TV rating from a VNDB minimum age.
     *
     * @param int|null $minage
     * @return int|null
     */
    protected function resolveTvRating(?int $minage): ?int
    {
        $name = match (true) {
            $minage === null => 'NR',
            $minage >= 18 => 'R18+',
            $minage >= 15 => 'R15+',
            $minage >= 12 => 'PG-12',
            default => 'G',
        };

        return TvRating::on(self::CONNECTION)->withoutGlobalScopes()->where('name', '=', $name)->value('id');
    }

    /**
     * Resolve the publishing status from the release date and development status.
     *
     * @param Carbon|null $publishedAt
     * @param int         $devstatus
     * @return Status|null
     */
    protected function resolveStatus(?Carbon $publishedAt, int $devstatus): ?Status
    {
        $name = match (true) {
            $devstatus === 1 => 'Not Published Yet',
            empty($publishedAt) => 'To Be Announced',
            $publishedAt->isPast() || $publishedAt->isToday() => 'Finished Publishing',
            default => 'Not Published Yet',
        };

        return Status::on(self::CONNECTION)->withoutGlobalScopes()
            ->where(['type' => 'game', 'name' => $name])->first();
    }

    /**
     * Resolve a country code from a VNDB original language.
     *
     * @param string $language
     * @return string
     */
    protected function resolveCountry(string $language): string
    {
        return match ($language) {
            'en' => 'us',
            'zh', 'zh-Hans', 'zh-Hant' => 'cn',
            'ko' => 'kr',
            default => 'jp',
        };
    }

    /**
     * Map a VNDB language to a supported locale.
     *
     * @param string $language
     * @return string|null
     */
    protected function resolveLocale(string $language): ?string
    {
        return match ($language) {
            'zh', 'zh-Hans', 'zh-Hant' => 'zh',
            'pt-pt', 'pt-br' => 'pt',
            default => strlen($language) === 2 ? $language : null,
        };
    }

    /**
     * Extract a Steam app id from a store url.
     *
     * @param string|null $url
     * @return string|null
     */
    protected function steamAppID(?string $url): ?string
    {
        if (!empty($url) && preg_match('#store\.steampowered\.com/app/(\d+)#', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Build a normalized title+year de-duplication key.
     *
     * @param string $title
     * @param int    $year
     * @return string
     */
    protected function titleKey(string $title, int $year): string
    {
        return preg_replace('/[^a-z0-9\x{3040}-\x{30ff}\x{4e00}-\x{9fff}\x{ac00}-\x{d7af}]/u', '', Str::lower($title)) . '|' . $year;
    }

    /**
     * Parse a PostgreSQL text array literal into a list of values.
     *
     * @param string|null $value
     * @return array<string>
     */
    protected function parsePgArray(?string $value): array
    {
        if (empty($value) || $value === '{}') {
            return [];
        }

        $inner = trim($value, '{}');

        return array_values(array_filter(array_map(
            fn ($element) => trim($element, '"'),
            str_getcsv($inner),
        )));
    }
}
