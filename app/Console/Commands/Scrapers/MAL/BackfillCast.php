<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Manga;
use App\Models\Person;
use App\Spiders\MAL\CastSpider;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
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
                            {--all : Include titles that already have a cast}';

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

        // Backfills touch too many rows for monitoring or search indexing to be worth the overhead.
        Pulse::stopRecording();
        Telescope::stopRecording();
        Anime::disableSearchSyncing();
        Manga::disableSearchSyncing();
        Character::disableSearchSyncing();
        Person::disableSearchSyncing();

        if ($model === null || $model === 'anime') {
            $count = $this->scrapeMissing(
                Anime::withoutGlobalScopes()->when(!$includesCast, function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->doesntHave('cast')->orDoesntHave('mediaStaff');
                    });
                }),
                fn ($malID) => str(config('scraper.domains.mal.anime_characters'))->replace(':x', $malID)->value(),
                $limit
            );
            $this->info("Scraped $count anime.");
        }

        if ($model === null || $model === 'manga') {
            $count = $this->scrapeMissing(
                Manga::withoutGlobalScopes()->when(!$includesCast, function (Builder $query) {
                    $query->doesntHave('cast');
                }),
                fn ($malID) => str(config('scraper.domains.mal.manga_characters'))->replace(':x', $malID)->value(),
                $limit
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
     * @param Builder  $query
     * @param callable $urlFor
     * @param int|null $limit
     *
     * @return int
     */
    private function scrapeMissing(Builder $query, callable $urlFor, ?int $limit): int
    {
        $malIDs = $query
            ->whereNull('deleted_at')
            ->whereNotNull('mal_id')
            ->when($limit !== null, function (Builder $query) use ($limit) {
                $query->limit($limit);
            })
            ->orderBy('id')
            ->pluck('mal_id');

        if ($malIDs->isEmpty()) {
            return 0;
        }

        $urls = $malIDs->map($urlFor)->all();

        Roach::startSpider(CastSpider::class, new Overrides(startUrls: $urls));

        return $malIDs->count();
    }
}
