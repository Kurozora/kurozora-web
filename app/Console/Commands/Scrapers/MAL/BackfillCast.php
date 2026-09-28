<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Manga;
use App\Models\Person;
use App\Spiders\MAL\CastSpider;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class BackfillCast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_backfill_cast
                            {model? : Limit to "anime" or "manga"; both when omitted}
                            {--limit= : Cap the number of titles scraped per model}
                            {--all : Include titles that already have a cast}
                            {--fresh : Include titles checked in the last 30 days}';

    /**
     * The number of days a checked title is skipped for.
     */
    private const int CHECKED_DAYS = 30;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape the characters page for anime/manga with no cast or staff yet.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $model = $this->argument('model');

        if ($model !== null && !in_array($model, ['anime', 'manga'], true)) {
            $this->error('Model must be "anime" or "manga".');
            return Command::INVALID;
        }

        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $includesCast = $this->option('all');
        $skipsChecked = !$this->option('fresh');

        // Backfills touch too many rows for monitoring or search indexing to be worth the overhead.
        Pulse::stopRecording();
        Telescope::stopRecording();
        Anime::disableSearchSyncing();
        Manga::disableSearchSyncing();
        Character::disableSearchSyncing();
        Person::disableSearchSyncing();

        if ($model === null || $model === 'anime') {
            $count = $this->scrapeMissing(
                'anime',
                Anime::withoutGlobalScopes()->when(!$includesCast, function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->doesntHave('cast')->orDoesntHave('mediaStaff');
                    });
                }),
                fn ($malID) => str(config('scraper.domains.mal.anime_characters'))->replace(':x', $malID)->value(),
                $limit,
                $skipsChecked
            );
            $this->info("Scraped $count anime.");
        }

        if ($model === null || $model === 'manga') {
            $count = $this->scrapeMissing(
                'manga',
                Manga::withoutGlobalScopes()->when(!$includesCast, function (Builder $query) {
                    $query->doesntHave('cast');
                }),
                fn ($malID) => str(config('scraper.domains.mal.manga_characters'))->replace(':x', $malID)->value(),
                $limit,
                $skipsChecked
            );
            $this->info("Scraped $count manga.");
        }

        Anime::enableSearchSyncing();
        Manga::enableSearchSyncing();
        Character::enableSearchSyncing();
        Person::enableSearchSyncing();
        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Scrape the characters page for titles matching the given query.
     *
     * @param string   $model
     * @param Builder  $query
     * @param callable $urlFor
     * @param int|null $limit
     * @param bool     $skipsChecked
     *
     * @return int
     */
    private function scrapeMissing(string $model, Builder $query, callable $urlFor, ?int $limit, bool $skipsChecked): int
    {
        $cache = Cache::store('file');
        $malIDs = $query
            ->whereNull('deleted_at')
            ->whereNotNull('mal_id')
            ->orderBy('id')
            ->pluck('mal_id')
            ->when($skipsChecked, function (Collection $malIDs) use ($cache, $model) {
                return $malIDs->reject(function ($malID) use ($cache, $model) {
                    return $cache->has($this->checkedKey($model, $malID));
                });
            })
            ->when($limit !== null, function (Collection $malIDs) use ($limit) {
                return $malIDs->take($limit);
            })
            ->values();

        if ($malIDs->isEmpty()) {
            return 0;
        }

        Roach::startSpider(CastSpider::class, new Overrides(startUrls: $malIDs->map($urlFor)->all()), [
            'onParsed' => function (string $uri) use ($cache) {
                if (preg_match('#/(anime|manga)/(\d+)/#', $uri, $matches)) {
                    $cache->put($this->checkedKey($matches[1], $matches[2]), true, now()->addDays(self::CHECKED_DAYS));
                }
            },
        ]);

        return $malIDs->count();
    }

    /**
     * The cache key marking a title as checked.
     *
     * @param string     $model
     * @param int|string $malID
     *
     * @return string
     */
    private function checkedKey(string $model, int|string $malID): string
    {
        return "scrape:mal_backfill_cast:$model:$malID";
    }
}
