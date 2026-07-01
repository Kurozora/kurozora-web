<?php

namespace App\Processors\IGDB;

use App\Enums\GameMode;
use App\Enums\LanguageSupportType;
use App\Enums\MediaCollection;
use App\Enums\PlayerPerspective;
use App\Enums\StudioType;
use App\Enums\VideoSource;
use App\Enums\VideoType;
use App\Events\BareBonesGameAdded;
use App\Models\Franchise;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Language;
use App\Models\MediaFranchise;
use App\Models\MediaGameMode;
use App\Models\MediaGenre;
use App\Models\MediaLanguage;
use App\Models\MediaPlatform;
use App\Models\MediaPlayerPerspective;
use App\Models\MediaRelation;
use App\Models\MediaStudio;
use App\Models\MediaTag;
use App\Models\MediaTheme;
use App\Models\MediaTool;
use App\Models\MediaType;
use App\Models\Platform;
use App\Models\Relation;
use App\Models\Source;
use App\Models\Status;
use App\Models\Studio;
use App\Models\Tag;
use App\Models\Theme;
use App\Models\Tool;
use App\Models\TvRating;
use App\Spiders\IGDB\Models\GameItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;

class GameProcessor extends CustomItemProcessor
{
    /**
     * The maximum per-channel color spread of a blank poster.
     *
     * @var int
     */
    protected const int MAXIMUM_BLANK_COLOR_SPREAD = 24;

    /**
     * The available NSFW keywords.
     *
     * @var string[]
     */
    protected array $nsfwKeywords = [
        'erotic',
        'hentai',
        'adult only',
        'pornographic',
    ];

    /**
     * Genre and theme keywords that mark suggestive (R15+) content.
     *
     * @var string[]
     */
    protected array $suggestiveKeywords = [
        'ecchi',
        'nudity',
        'sexual content',
    ];

    /**
     * ISO 3166-1 numeric country codes mapped to their alpha-2 code.
     *
     * @var array<int, string>
     */
    protected const array COUNTRY_CODES = [
        32 => 'ar', 36 => 'au', 40 => 'at', 56 => 'be', 76 => 'br',
        124 => 'ca', 156 => 'cn', 158 => 'tw', 203 => 'cz', 208 => 'dk',
        246 => 'fi', 250 => 'fr', 276 => 'de', 300 => 'gr', 344 => 'hk',
        348 => 'hu', 356 => 'in', 360 => 'id', 376 => 'il', 380 => 'it',
        392 => 'jp', 410 => 'kr', 458 => 'my', 484 => 'mx', 528 => 'nl',
        554 => 'nz', 578 => 'no', 608 => 'ph', 616 => 'pl', 620 => 'pt',
        643 => 'ru', 702 => 'sg', 704 => 'vn', 724 => 'es', 752 => 'se',
        756 => 'ch', 764 => 'th', 792 => 'tr', 804 => 'ua', 826 => 'gb',
        840 => 'us',
    ];

    /**
     * The item classes the processor handles.
     *
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            GameItem::class,
        ];
    }

    /**
     * Persist the game and all of its relationships.
     *
     * @param ItemInterface $item
     *
     * @return ItemInterface
     */
    public function processItem(ItemInterface $item): ItemInterface
    {
        $slug = $item->get('slug');
        $data = $item->get('game');

        if ($item->get('gated') && !$this->passesAnimeGate($data)) {
            logger()->channel('stderr')->info('🚫 [IGDB:' . $slug . '] Skipped (failed anime gate)');
            $this->recordProgress($slug);
            return $item;
        }

        if ($this->isAiGenerated($slug)) {
            logger()->channel('stderr')->info('🚫 [IGDB:' . $slug . '] Skipped (AI-generated)');
            $this->recordProgress($slug);
            return $item;
        }

        logger()->channel('stderr')->info('🔄 [IGDB:' . $slug . '] Processing');

        $game = $this->upsertGame($slug, $data);

        $this->syncGenresThemesTags($game, $data);
        $this->syncGameModes($game, $data['gameModes'] ?? []);
        $this->syncPlayerPerspectives($game, $data['playerPerspectives'] ?? []);
        $this->syncTools($game, $data['gameEngines'] ?? []);
        $this->syncFranchises($game, $data['franchises'] ?? []);
        $this->syncStudios($game, $data);
        $this->syncLanguages($game, $data['languageSupports'] ?? []);
        $this->syncPlatforms($game, $data['releases'] ?? []);
        $this->syncVideos($game, $data['videos'] ?? []);
        $this->syncRelations($game, $data);
        $this->syncRating($game, $data);
        $this->addPoster($game, $data['coverSrc'] ?? null);

        logger()->channel('stderr')->debug('✅️ [IGDB:' . $slug . '] Done');

        $this->recordProgress($slug);

        return $item;
    }

    /**
     * Create or update the game's core attributes.
     *
     * @param string $slug
     * @param array  $data
     *
     * @return Game
     */
    protected function upsertGame(string $slug, array $data): Game
    {
        $publishedAt = $this->parseDate($data['firstReleaseDate'] ?? null);
        $timeToBeat = $data['timeToBeat'] ?? [];
        $nsfwWeight = $this->nsfwWeight($data['genres'] ?? [], $data['themes'] ?? []);
        $tvRating = $this->resolveTvRating($data['gameRatings'] ?? [], $nsfwWeight);

        $attributes = [
            'igdb_id' => isset($data['id']) ? (int) $data['id'] : null,
            'original_title' => $data['name'],
            'title' => $data['name'],
            'synopsis' => $data['summary'] ?? ($data['storyline'] ?? null),
            'synonym_titles' => $this->synonymTitles($data['alternativeNames'] ?? []),
            'website_urls' => $this->websiteUrls($data['websites'] ?? []),
            'media_type_id' => $this->resolveMediaType($data['category'] ?? 0)->id,
            'source_id' => $this->resolveSourceID(),
            'status_id' => $this->resolveStatus($publishedAt)?->id,
            'tv_rating_id' => $tvRating?->id,
            'duration' => (int) ($timeToBeat['normally'] ?? 0),
            'time_to_beat_hastily' => (int) ($timeToBeat['hastily'] ?? 0),
            'time_to_beat_completely' => (int) ($timeToBeat['completely'] ?? 0),
            'is_nsfw' => $nsfwWeight !== null || $tvRating?->weight === 5,
            'published_at' => $publishedAt,
            'publication_day' => $publishedAt?->dayOfWeek,
            'publication_season' => $publishedAt ? season_of_year($publishedAt)->value : 0,
        ];

        $attributes = array_merge($attributes, $this->localizedTitles($data));

        // Infer the country of origin from the developer.
        $country = $this->resolveCountry($data['developers'] ?? []);

        if ($country !== null) {
            $attributes['country_id'] = $country;
        }

        $game = Game::withoutGlobalScopes()
            ->firstWhere('igdb_id', '=', $attributes['igdb_id']);

        if (empty($game)) {
            return Game::create(array_merge(['igdb_slug' => $slug], $attributes));
        }

        $attributes = array_filter($attributes, fn($value) => $value !== null);
        $game->fill(array_merge(['igdb_slug' => $slug], $attributes))->save();

        return $game;
    }

    /**
     * Build the synonym title list from the alternative names.
     *
     * @param array $alternativeNames
     *
     * @return array<string>
     */
    protected function synonymTitles(array $alternativeNames): array
    {
        return collect($alternativeNames)
            ->pluck('name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Build the website url list.
     *
     * @param array $websites
     *
     * @return array<string>
     */
    protected function websiteUrls(array $websites): array
    {
        return collect($websites)
            ->pluck('url')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Resolve the country of origin from the game's developers.
     *
     * @param array $developers
     *
     * @return string|null
     */
    protected function resolveCountry(array $developers): ?string
    {
        foreach ($developers as $developer) {
            $numeric = $developer['country'] ?? null;

            if ($numeric === null) {
                continue;
            }

            $code = self::COUNTRY_CODES[(int) $numeric] ?? null;

            if ($code !== null) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Build the per-locale title translations from the localizations.
     *
     * @param array $data
     *
     * @return array
     */
    protected function localizedTitles(array $data): array
    {
        $english = $data['name'];
        $byLocale = [];

        foreach ($data['localizations'] ?? [] as $localization) {
            $locale = $this->resolveLocale($localization['region']['identifier'] ?? null);
            $name = $localization['name'] ?? null;

            if ($locale === null || $locale === 'en' || empty($name)) {
                continue;
            }

            $byLocale[$locale] = $name;
        }

        // Japanese is always present, falling back to the English title.
        $translations = [
            'ja' => ['title' => $byLocale['ja'] ?? $english],
        ];
        unset($byLocale['ja']);

        foreach ($byLocale as $locale => $title) {
            $translations[$locale] = ['title' => $title];
        }

        return $translations;
    }

    /**
     * Resolve a region identifier (e.g. "ja-JP") to a supported locale.
     *
     * @param string|null $identifier
     *
     * @return string|null
     */
    protected function resolveLocale(?string $identifier): ?string
    {
        if (empty($identifier)) {
            return null;
        }

        $subtag = strtolower(Str::before($identifier, '-'));

        $language = Language::withoutGlobalScopes()
            ->where('code', '=', $subtag)
            ->orWhere('iso_639_3', '=', $subtag)
            ->first();

        return $language?->code ?: null;
    }

    /**
     * Resolve the game's media type from the category.
     *
     * @param int $category
     *
     * @return MediaType
     */
    protected function resolveMediaType(int $category): MediaType
    {
        // Map the granular categories onto Kurozora's game media types.
        $name = match ($category) {
            5 => 'MOD',             // mod
            6 => 'Episode',         // episode
            7 => 'Season',          // season
            1, 2, 13 => 'DLC',      // DLC, expansion, pack (add-on content)
            default => 'Full Game', // main game, standalone expansion, remake, remaster, port, edition, bundle
        };

        return $this->gameMediaType($name);
    }

    /**
     * Resolve a game media type by name, creating it when first encountered.
     *
     * @param string $name
     *
     * @return MediaType
     */
    protected function gameMediaType(string $name): MediaType
    {
        return MediaType::withoutGlobalScopes()->firstOrCreate([
            'type' => 'game',
            'name' => $name,
        ], [
            'description' => '',
        ]);
    }

    /**
     * Resolve the "Original" source id.
     *
     * @return int|null
     */
    protected function resolveSourceID(): ?int
    {
        return Source::withoutGlobalScopes()
            ->where('name', '=', 'Original')
            ->value('id');
    }

    /**
     * Resolve the publishing status from the release date.
     *
     * @param Carbon|null $publishedAt
     *
     * @return Status|null
     */
    protected function resolveStatus(?Carbon $publishedAt): ?Status
    {
        if (empty($publishedAt)) {
            $name = 'To Be Announced';
        } else if ($publishedAt->isPast() || $publishedAt->isToday()) {
            $name = 'Finished Publishing';
        } else {
            $name = 'Not Published Yet';
        }

        return Status::withoutGlobalScopes()
            ->where('type', '=', 'game')
            ->where('name', '=', $name)
            ->first();
    }

    /**
     * Resolve the TV rating from the age ratings, taking the most permissive.
     *
     * @param array    $gameRatings
     * @param null|int $nsfwFloor
     *
     * @return TvRating|null
     */
    protected function resolveTvRating(array $gameRatings, ?int $nsfwFloor = null): ?TvRating
    {
        // category => (rating tier => Kurozora weight); weight 2=G, 3=PG-12, 4=R15+, 5=R18+.
        $scale = [
            1 => [1 => 2, 2 => 2, 3 => 2, 4 => 3, 5 => 3, 6 => 4, 7 => 5], // ESRB
            2 => [1 => 2, 2 => 2, 3 => 3, 4 => 4, 5 => 5],                 // PEGI
            3 => [1 => 2, 2 => 3, 3 => 4, 4 => 4, 5 => 5],                 // CERO
            4 => [1 => 2, 2 => 2, 3 => 3, 4 => 4, 5 => 5],                 // USK
            5 => [1 => 2, 2 => 3, 3 => 4, 4 => 5],                         // GRAC
            6 => [1 => 2, 2 => 3, 3 => 3, 4 => 4, 5 => 4, 6 => 5],         // ClassInd
            7 => [1 => 2, 2 => 3, 3 => 4, 4 => 4, 5 => 5],                 // ACB
        ];

        $weight = null;

        foreach ($gameRatings as $rating) {
            $candidate = $scale[$rating['category']][$rating['rating']] ?? null;

            if ($candidate !== null) {
                $weight = $weight === null ? $candidate : min($weight, $candidate);
            }
        }

        // NSFW genres or themes enforce a minimum rating, even when the age rating is absent.
        if ($nsfwFloor !== null) {
            $weight = max($weight ?? 0, $nsfwFloor);
        }

        $name = match ($weight) {
            2 => 'G',
            3 => 'PG-12',
            4 => 'R15+',
            5 => 'R18+',
            default => 'NR',
        };

        return TvRating::withoutGlobalScopes()
            ->where('name', '=', $name)
            ->first();
    }

    /**
     * Detect an NSFW rating floor from the genres and themes.
     *
     * @param array $genres
     * @param array $themes
     *
     * @return int|null
     */
    protected function nsfwWeight(array $genres, array $themes): ?int
    {
        $names = collect(array_merge($genres, $themes))
            ->pluck('name')
            ->filter()
            ->map(fn($name) => strtolower($name));

        if ($names->contains(fn($name) => Str::contains($name, $this->nsfwKeywords))) {
            return 5; // R18+
        }

        if ($names->contains(fn($name) => Str::contains($name, $this->suggestiveKeywords))) {
            return 4; // R15+
        }

        return null;
    }

    /**
     * Determine whether a gated game clears the anime gate.
     *
     * @param array $data
     *
     * @return bool
     */
    protected function passesAnimeGate(array $data): bool
    {
        return $this->hasEastAsianLocalization($data) || $this->hasEastAsianAudio($data);
    }

    /**
     * Determine whether the game ships an East-Asian localized title.
     *
     * @param array $data
     *
     * @return bool
     */
    protected function hasEastAsianLocalization(array $data): bool
    {
        foreach ($data['localizations'] ?? [] as $localization) {
            $identifier = $localization['region']['identifier'] ?? '';

            if (preg_match('/^(ja|ko|zh)/', $identifier) === 1) {
                return true;
            }

            if ($this->hasEastAsianScript($localization['name'] ?? '')) {
                return true;
            }
        }

        return $this->hasEastAsianScript($data['name'] ?? '');
    }

    /**
     * Determine whether the game is voiced in an East-Asian language.
     *
     * @param array $data
     *
     * @return bool
     */
    protected function hasEastAsianAudio(array $data): bool
    {
        foreach ($data['languageSupports'] ?? [] as $support) {
            $isAudio = ($support['languageSupportType']['name'] ?? '') === 'Audio';
            $language = $support['language']['name'] ?? '';

            if ($isAudio && Str::contains($language, ['Japanese', 'Korean', 'Chinese'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the text carries Japanese kana, CJK Han, or Korean Hangul.
     *
     * @param string $text
     *
     * @return bool
     */
    protected function hasEastAsianScript(string $text): bool
    {
        return preg_match('/[\x{3040}-\x{30FF}\x{4E00}-\x{9FFF}\x{AC00}-\x{D7AF}]/u', $text) === 1;
    }

    /**
     * Determine whether a game is built with AI generated assets.
     *
     * @param string $slug
     *
     * @return bool
     */
    protected function isAiGenerated(string $slug): bool
    {
        return array_any($this->fetchKeywords($slug), fn($keyword) => Str::contains($keyword, ['ai-generated', 'ai generated']));
    }

    /**
     * Fetch a game's lowercased keywords from its page's structured data.
     *
     * @param string $slug
     *
     * @return array<string>
     */
    protected function fetchKeywords(string $slug): array
    {
        $url = str(config('scraper.domains.igdb.game'))->replace(':x', $slug)->value();

        $result = Process::timeout((int) config('scraper.curl_impersonate.timeout'))->run([
            config('scraper.curl_impersonate.binary'),
            '--impersonate', config('scraper.curl_impersonate.profile'),
            '-sL', '--compressed',
            $url,
        ]);

        if ($result->failed() || !preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $result->output(), $matches)) {
            return [];
        }

        $data = json_decode(html_entity_decode($matches[1]), true);

        return collect($data['keywords'] ?? [])
            ->filter()
            ->map(fn($keyword) => strtolower($keyword))
            ->values()
            ->all();
    }

    /**
     * Map the genres and themes onto genres, themes, and tags.
     *
     * @param Game  $game
     * @param array $data
     *
     * @return void
     */
    protected function syncGenresThemesTags(Game $game, array $data): void
    {
        $names = [];

        foreach (array_merge($data['genres'] ?? [], $data['themes'] ?? []) as $entry) {
            foreach ($this->normalizeTaxonomyName($entry['name']) as $name) {
                $names[] = $name;
            }
        }

        $names = array_values(array_unique($names));

        $genres = Genre::withoutGlobalScopes()
            ->whereIn('name', $names)
            ->get(['id', 'name']);
        foreach ($genres as $genre) {
            MediaGenre::withoutGlobalScopes()->firstOrCreate([
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'genre_id' => $genre->id,
            ]);
        }
        $names = $this->without($names, $genres->pluck('name'));

        $themes = Theme::withoutGlobalScopes()
            ->whereIn('name', $names)
            ->get(['id', 'name']);
        foreach ($themes as $theme) {
            MediaTheme::withoutGlobalScopes()->firstOrCreate([
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'theme_id' => $theme->id,
            ]);
        }
        $names = $this->without($names, $themes->pluck('name'));

        foreach ($names as $name) {
            $tag = Tag::withoutGlobalScopes()->firstOrCreate(['name' => $name]);

            MediaTag::withoutGlobalScopes()->firstOrCreate([
                'taggable_id' => $game->id,
                'taggable_type' => $game->getMorphClass(),
                'tag_id' => $tag->id,
            ]);
        }
    }

    /**
     * Normalize a genre or theme name into Kurozora taxonomy names.
     *
     * @param string $name
     *
     * @return array<string>
     */
    protected function normalizeTaxonomyName(string $name): array
    {
        return match ($name) {
            'Role-playing (RPG)' => ['Role-playing game', 'RPG'],
            'Turn-based strategy (TBS)' => ['Turn-based strategy', 'TBS'],
            'Real Time Strategy (RTS)' => ['Real Time Strategy', 'RTS'],
            'Hack and slash/Beat \'em up' => ['Hack and slash', 'Beat \'em up'],
            'Card & Board Game' => ['Card game', 'Board game'],
            'Science fiction' => ['Science fiction', 'Sci-Fi'],
            'Sport' => ['Sport', 'Sports'],
            default => [Str::of($name)->trim()->value()],
        };
    }

    /**
     * Return the names not present in the given matched collection.
     *
     * @param array      $names
     * @param Collection $matched
     *
     * @return array<string>
     */
    protected function without(array $names, Collection $matched): array
    {
        $matchedLower = $matched->map(fn($name) => strtolower($name))->all();

        return array_values(array_filter($names, fn($name) => !in_array(strtolower($name), $matchedLower, true)));
    }

    /**
     * Attach the game modes.
     *
     * @param Game  $game
     * @param array $modes
     *
     * @return void
     */
    protected function syncGameModes(Game $game, array $modes): void
    {
        foreach ($modes as $mode) {
            $value = $this->resolveGameMode($mode['name'] ?? null);

            if ($value === null) {
                continue;
            }

            MediaGameMode::withoutGlobalScopes()->firstOrCreate([
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'game_mode' => $value,
            ]);
        }
    }

    /**
     * Resolve a game mode value from the name.
     *
     * @param string|null $name
     *
     * @return int|null
     */
    protected function resolveGameMode(?string $name): ?int
    {
        return match ($name) {
            'Single player' => GameMode::SinglePlayer,
            'Multiplayer' => GameMode::Multiplayer,
            'Co-operative' => GameMode::Cooperative,
            'Split screen' => GameMode::SplitScreen,
            'Massively Multiplayer Online (MMO)' => GameMode::MMO,
            'Battle Royale' => GameMode::BattleRoyale,
            default => null,
        };
    }

    /**
     * Attach the player perspectives.
     *
     * @param Game  $game
     * @param array $perspectives
     *
     * @return void
     */
    protected function syncPlayerPerspectives(Game $game, array $perspectives): void
    {
        foreach ($perspectives as $perspective) {
            $value = $this->resolvePlayerPerspective($perspective['name'] ?? null);

            if ($value === null) {
                continue;
            }

            MediaPlayerPerspective::withoutGlobalScopes()->firstOrCreate([
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'player_perspective' => $value,
            ]);
        }
    }

    /**
     * Resolve a player perspective value from the name.
     *
     * @param string|null $name
     *
     * @return int|null
     */
    protected function resolvePlayerPerspective(?string $name): ?int
    {
        return match ($name) {
            'First person' => PlayerPerspective::FirstPerson,
            'Third person' => PlayerPerspective::ThirdPerson,
            'Bird view / Isometric' => PlayerPerspective::BirdView,
            'Side view' => PlayerPerspective::SideView,
            'Text' => PlayerPerspective::Text,
            'Auditory' => PlayerPerspective::Auditory,
            'Virtual Reality' => PlayerPerspective::VirtualReality,
            default => null,
        };
    }

    /**
     * Attach the tools (game engines) the game was made with.
     *
     * @param Game  $game
     * @param array $engines
     *
     * @return void
     */
    protected function syncTools(Game $game, array $engines): void
    {
        foreach ($engines as $engine) {
            $tool = Tool::withoutGlobalScopes()->firstOrCreate(['name' => $engine['name']]);

            MediaTool::withoutGlobalScopes()->firstOrCreate([
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'tool_id' => $tool->id,
            ]);
        }
    }

    /**
     * Attach the franchises (the cross-media IP grouping).
     *
     * @param Game  $game
     * @param array $franchises
     *
     * @return void
     */
    protected function syncFranchises(Game $game, array $franchises): void
    {
        foreach ($franchises as $franchise) {
            $model = Franchise::withoutGlobalScopes()->firstOrCreate(['name' => $franchise['name']]);

            MediaFranchise::withoutGlobalScopes()->firstOrCreate([
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'franchise_id' => $model->id,
            ]);
        }
    }

    /**
     * Attach developers (developer/supporting/porting) and publishers.
     *
     * @param Game  $game
     * @param array $data
     *
     * @return void
     */
    protected function syncStudios(Game $game, array $data): void
    {
        $developers = array_merge(
            $data['developers'] ?? [],
            $data['supportingDevelopers'] ?? [],
            $data['portingDevelopers'] ?? [],
        );

        foreach ($developers as $developer) {
            $this->attachStudio($game, $developer, ['is_developer' => true]);
        }

        foreach ($data['publishers'] ?? [] as $publisher) {
            $this->attachStudio($game, $publisher, ['is_publisher' => true]);
        }
    }

    /**
     * Attach a single studio with the given role flag, enriching a newly created
     * studio with the inline company details.
     *
     * @param Game  $game
     * @param array $company
     * @param array $flags
     *
     * @return void
     */
    protected function attachStudio(Game $game, array $company, array $flags): void
    {
        if (empty($company['name'])) {
            return;
        }

        $studio = Studio::withoutGlobalScopes()->firstOrCreate(['name' => $company['name']], [
            'type' => StudioType::Game,
            'about' => $company['description'] ?? null,
            'founded_at' => $this->parseCompanyDate($company['startDate'] ?? null),
        ]);

        MediaStudio::withoutGlobalScopes()->updateOrCreate([
            'studio_id' => $studio->id,
            'model_id' => $game->id,
            'model_type' => $game->getMorphClass(),
        ], $flags);
    }

    /**
     * Attach audio, subtitle, and interface language support.
     *
     * @param Game  $game
     * @param array $languageSupports
     *
     * @return void
     */
    protected function syncLanguages(Game $game, array $languageSupports): void
    {
        foreach ($languageSupports as $support) {
            $language = $this->resolveLanguage($support['language'] ?? []);
            $type = $this->resolveLanguageSupportType($support['languageSupportType']['name'] ?? null);

            if (empty($language) || $type === null) {
                continue;
            }

            MediaLanguage::withoutGlobalScopes()->firstOrCreate([
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'language_id' => $language->id,
                'type' => $type,
            ]);
        }
    }

    /**
     * Resolve a language by name or locale.
     *
     * @param array $language
     *
     * @return Language|null
     */
    protected function resolveLanguage(array $language): ?Language
    {
        if (!empty($language['name'])) {
            $match = Language::withoutGlobalScopes()->where('name', '=', $language['name'])->first();

            if (!empty($match)) {
                return $match;
            }
        }

        $code = Str::of($language['locale'] ?? '')->before('-')->lower()->value();

        return empty($code) ? null : Language::withoutGlobalScopes()->where('code', '=', $code)->first();
    }

    /**
     * Resolve the language support type value.
     *
     * @param string|null $name
     *
     * @return int|null
     */
    protected function resolveLanguageSupportType(?string $name): ?int
    {
        return match ($name) {
            'Audio' => LanguageSupportType::Audio,
            'Subtitles' => LanguageSupportType::Subtitles,
            'Interface' => LanguageSupportType::Interface,
            default => null,
        };
    }

    /**
     * Attach the per-platform releases, enriching the platform inline.
     *
     * @param Game  $game
     * @param array $releases
     *
     * @return void
     */
    protected function syncPlatforms(Game $game, array $releases): void
    {
        foreach ($releases as $release) {
            $platformData = $release['platform'] ?? null;

            if (empty($platformData)) {
                continue;
            }

            $platform = $this->resolvePlatform($platformData);
            $region = $this->resolveRegion($release['region'] ?? null);

            MediaPlatform::withoutGlobalScopes()->updateOrCreate([
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'platform_id' => $platform->id,
                'region' => $region,
            ], [
                'released_at' => $this->parseDate($release['releaseDate'] ?? null),
                'release_status' => $release['releaseDateStatus']['name'] ?? null,
            ]);
        }
    }

    /**
     * Resolve a platform by name, enriching its type and generation.
     *
     * @param array $platformData
     *
     * @return Platform
     */
    protected function resolvePlatform(array $platformData): Platform
    {
        $category = $platformData['category'] ?? null;
        $generation = $platformData['generation'] ?? null;

        $platform = Platform::withoutGlobalScopes()->firstOrNew([
            'original_name' => $platformData['name'],
        ]);
        $platform->name = $platformData['name'];

        if (in_array($category, [1, 2, 3, 4, 5, 6], true)) {
            $platform->type = $category;
        }

        // Computers and operating systems (e.g. PC) have no console generation,
        // so default a new platform to 0 to satisfy the non-nullable column.
        if (!$platform->exists) {
            $platform->generation = $generation ?? 0;
        } else if (!is_null($generation)) {
            $platform->generation = $generation;
        }

        // The alternative names arrive as a single comma-separated string;
        // capture them once, without clobbering names already stored.
        $existingNames = $platform->alternative_names?->toArray() ?? [];

        if (empty($existingNames) && !empty($platformData['alternativeName'])) {
            $platform->alternative_names = collect(explode(',', $platformData['alternativeName']))
                ->map(fn($name) => trim($name))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $platform->save();

        return $platform;
    }

    /**
     * Resolve the region id into a readable name.
     *
     * @param int|null $region
     *
     * @return string|null
     */
    protected function resolveRegion(?int $region): ?string
    {
        return match ($region) {
            1 => 'Europe',
            2 => 'North America',
            3 => 'Australia',
            4 => 'New Zealand',
            5 => 'Japan',
            6 => 'China',
            7 => 'Asia',
            8 => 'Worldwide',
            9 => 'Korea',
            10 => 'Brazil',
            default => null,
        };
    }

    /**
     * Attach the YouTube trailers and set the primary video url.
     *
     * @param Game  $game
     * @param array $videos
     *
     * @return void
     */
    protected function syncVideos(Game $game, array $videos): void
    {
        $languageID = Language::withoutGlobalScopes()->where('code', '=', 'en')->value('id');

        foreach (array_values($videos) as $order => $video) {
            if (empty($video['videoId'])) {
                continue;
            }

            $game->videos()->firstOrCreate([
                'source' => VideoSource::YouTube,
                'code' => $video['videoId'],
            ], [
                'language_id' => $languageID,
                'type' => VideoType::Trailer,
                'is_sub' => false,
                'is_dub' => false,
                'order' => $order,
            ]);
        }

        $primary = $videos[0]['videoId'] ?? null;

        if (!empty($primary) && empty($game->video_url)) {
            $game->update(['video_url' => 'https://www.youtube.com/watch?v=' . $primary]);
        }
    }

    /**
     * Wire the game's inter-game relationships.
     *
     * @param Game  $game
     * @param array $data
     *
     * @return void
     */
    protected function syncRelations(Game $game, array $data): void
    {
        // This game derives from a base game (it is an edition, expansion, or DLC of it).
        if (!empty($data['versionParent'])) {
            $parent = $this->resolveRelatedGame($data['versionParent']);

            if ($parent !== null && $parent->id !== $game->id && empty($game->parent_id)) {
                $game->update(['parent_id' => $parent->id]);
            }
        }

        // Add-on content and structural divisions that derive from this game,
        // each seeded with its own media type.
        $this->attachChildren($game, $data['dlcs'] ?? [], 'DLC');
        $this->attachChildren($game, $data['expansions'] ?? [], 'DLC');
        $this->attachChildren($game, $data['seasons'] ?? [], 'Season');
        $this->attachChildren($game, $data['episodes'] ?? [], 'Episode');

        // Full editions and standalone expansions that derive from this game.
        foreach (['expandedGames', 'versions', 'standalones'] as $key) {
            $this->attachChildren($game, $data[$key] ?? [], 'Full Game');
        }

        $this->syncAlternateVersions($game, $data);
    }

    /**
     * Link remakes, remasters, ports, and forks through the shared
     * "Alternative Version" relation rather than `parent_id`.
     *
     * @param Game  $game
     * @param array $data
     *
     * @return void
     */
    protected function syncAlternateVersions(Game $game, array $data): void
    {
        $alternates = array_merge(
            $data['remake'] ?? [],
            $data['remasters'] ?? [],
            $data['ports'] ?? [],
            $data['forks'] ?? [],
        );

        if (empty($alternates)) {
            return;
        }

        $relation = Relation::firstOrCreate(['name' => 'Alternative Version']);

        foreach ($alternates as $entry) {
            $alternate = $this->resolveRelatedGame($entry, 'Full Game');

            if ($alternate === null || $alternate->id === $game->id) {
                continue;
            }

            MediaRelation::withoutGlobalScopes()->firstOrCreate([
                'model_id' => $game->id,
                'model_type' => $game->getMorphClass(),
                'relation_id' => $relation->id,
                'related_id' => $alternate->id,
                'related_type' => $alternate->getMorphClass(),
            ]);
        }
    }

    /**
     * Point each related game at this game as its parent, seeding the media type.
     *
     * @param Game   $game
     * @param array  $related
     * @param string $mediaTypeName
     *
     * @return void
     */
    protected function attachChildren(Game $game, array $related, string $mediaTypeName): void
    {
        foreach ($related as $entry) {
            $child = $this->resolveRelatedGame($entry, $mediaTypeName);

            if ($child !== null && $child->id !== $game->id && empty($child->parent_id)) {
                $child->update(['parent_id' => $game->id]);
            }
        }
    }

    /**
     * Resolve a related game, creating a stub when it does not exist yet.
     *
     * @param array $related
     *
     * @return Game|null
     */
    protected function resolveRelatedGame(array $related, ?string $mediaTypeName = null): ?Game
    {
        $igdbID = isset($related['id']) ? (int) $related['id'] : null;

        if (empty($igdbID)) {
            return null;
        }

        $attributes = [
            'igdb_slug' => $related['slug'] ?? null,
            'original_title' => $related['name'],
            'title' => $related['name'],
        ];

        if ($mediaTypeName !== null) {
            $attributes['media_type_id'] = $this->gameMediaType($mediaTypeName)->id;
        }

        $game = Game::withoutGlobalScopes()->firstOrCreate(['igdb_id' => $igdbID], $attributes);

        // Queue barebones game.
        if ($game->wasRecentlyCreated && !empty($game->igdb_slug)) {
            event(new BareBonesGameAdded($game));
        }

        return $game;
    }

    /**
     * Store the community rating breakdown on the media stat.
     *
     * @param Game  $game
     * @param array $data
     *
     * @return void
     */
    protected function syncRating(Game $game, array $data): void
    {
        $breakdown = $data['gameRatingBreakdown'] ?? [];
        $rating = $data['rating'] ?? [];

        $attributes = [
            'rating_average' => $rating['userRating'] ?? 0,
            'rating_count' => $rating['userRatingsCount'] ?? 0,
        ];

        for ($score = 1; $score <= 10; $score++) {
            $attributes['rating_' . $score] = $breakdown[$score] ?? 0;
        }

        $game->mediaStat()->update($attributes);
    }

    /**
     * Download and attach the poster when one is not set yet.
     *
     * @param Game        $game
     * @param string|null $coverSrc
     *
     * @return void
     */
    protected function addPoster(Game $game, ?string $coverSrc): void
    {
        if (empty($coverSrc) || !empty($game->getFirstMedia(MediaCollection::Poster))) {
            return;
        }

        $hash = Str::of($coverSrc)->afterLast('/')->before('.')->value();

        if (empty($hash) || Str::of($hash)->lower()->contains(['nocover', 'no_cover'])) {
            return;
        }

        $url = str(config('scraper.domains.igdb.image'))->replace(':x', $hash)->value();

        try {
            $contents = file_get_contents($url);

            if ($contents === false || $this->isBlankImage($contents)) {
                return;
            }

            $temporaryPath = tempnam(sys_get_temp_dir(), 'igdb_cover_');
            file_put_contents($temporaryPath, $contents);

            try {
                $game->updateImageMedia(MediaCollection::Poster(), $temporaryPath, $game->original_title, [], 'jpg');
            } finally {
                if (is_file($temporaryPath)) {
                    @unlink($temporaryPath);
                }
            }
        } catch (Exception $exception) {
            logger()->channel('stderr')->error($exception->getMessage());
        }
    }

    /**
     * Determine whether the image is a near-uniform blank.
     *
     * @param string $contents
     *
     * @return bool
     */
    protected function isBlankImage(string $contents): bool
    {
        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            return false;
        }

        $sample = imagecreatetruecolor(32, 32);
        imagecopyresampled($sample, $image, 0, 0, 0, 0, 32, 32, imagesx($image), imagesy($image));
        imagedestroy($image);

        $redMin = $greenMin = $blueMin = 255;
        $redMax = $greenMax = $blueMax = 0;

        for ($x = 0; $x < 32; $x++) {
            for ($y = 0; $y < 32; $y++) {
                $rgb = imagecolorat($sample, $x, $y);
                $red = ($rgb >> 16) & 0xFF;
                $green = ($rgb >> 8) & 0xFF;
                $blue = $rgb & 0xFF;

                $redMin = min($redMin, $red);
                $redMax = max($redMax, $red);
                $greenMin = min($greenMin, $green);
                $greenMax = max($greenMax, $green);
                $blueMin = min($blueMin, $blue);
                $blueMax = max($blueMax, $blue);
            }
        }

        imagedestroy($sample);

        return max($redMax - $redMin, $greenMax - $greenMin, $blueMax - $blueMin) < self::MAXIMUM_BLANK_COLOR_SPREAD;
    }

    /**
     * Append the processed slug to the facet's cache, so an interrupted run resumes.
     *
     * @param string $slug
     *
     * @return void
     */
    protected function recordProgress(string $slug): void
    {
        $path = config('scraper.igdb.cache_path');

        if (empty($path) || !is_file($path)) {
            return;
        }

        $state = json_decode(file_get_contents($path), true) ?: [];
        $slugs = $state['slugs'] ?? [];

        if (in_array($slug, $slugs, true)) {
            return;
        }

        $slugs[] = $slug;
        $state['slugs'] = $slugs;

        file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Parse a company founding date, anchoring a bare year to its first day.
     *
     * @param string|null $date
     *
     * @return string|null
     */
    protected function parseCompanyDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        if (preg_match('/^\d{4}$/', $date)) {
            return $date . '-01-01';
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Parse a date string into a Carbon instance.
     *
     * @param string|null $date
     *
     * @return Carbon|null
     */
    protected function parseDate(?string $date): ?Carbon
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->startOfDay();
        } catch (Exception $exception) {
            return null;
        }
    }
}
