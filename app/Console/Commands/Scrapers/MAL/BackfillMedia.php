<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Enums\MediaCollection;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Manga;
use App\Models\Person;
use App\Spiders\MAL\CharacterSpider;
use App\Spiders\MAL\PersonSpider;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class BackfillMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_backfill_media
                            {model? : Limit to "character" or "person"; both when omitted}
                            {--limit= : Cap the number of stubs scraped per model}
                            {--days=1 : Skip entities scraped within this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape characters and people that have a MAL id but no profile image.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $model = $this->argument('model');

        if ($model !== null && !in_array($model, ['character', 'person'], true)) {
            $this->error('Model must be "character" or "person".');
            return Command::INVALID;
        }

        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $days = (int) $this->option('days');

        // Backfills touch too many rows for monitoring or search indexing to be worth the overhead.
        Pulse::stopRecording();
        Telescope::stopRecording();
        Anime::disableSearchSyncing();
        Manga::disableSearchSyncing();
        Character::disableSearchSyncing();
        Person::disableSearchSyncing();

        if ($model === null || $model === 'character') {
            $count = $this->scrapeStubs(Character::class, config('scraper.domains.mal.character'), CharacterSpider::class, $limit, $days);
            $this->info("Scraped $count characters.");
        }

        if ($model === null || $model === 'person') {
            $count = $this->scrapeStubs(Person::class, config('scraper.domains.mal.people'), PersonSpider::class, $limit, $days);
            $this->info("Scraped $count people.");
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
     * Scrape the stubs of the given model that lack a profile image.
     *
     * @param class-string<Character|Person> $model
     * @param string                         $baseURL
     * @param class-string                   $spider
     * @param int|null                       $limit
     * @param int                            $days
     *
     * @return int
     */
    private function scrapeStubs(string $model, string $baseURL, string $spider, ?int $limit, int $days): int
    {
        $query = $model::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->whereNotNull('mal_id')
            ->whereDoesntHave('media', function (Builder $query) {
                $query->where('collection_name', '=', MediaCollection::Profile);
            })
            ->where('updated_at', '<', now()->subDays($days))
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $malIDs = $query->pluck('mal_id');

        if ($malIDs->isEmpty()) {
            return 0;
        }

        logger()->channel('stderr')->debug('Scraping ' . $malIDs->count() . ' ' . strtolower(class_basename($model)) . '(s) from MAL.');

        $urls = $malIDs->map(function ($malID) use ($baseURL) {
            return $baseURL . '/' . $malID;
        })->all();

        Roach::startSpider($spider, new Overrides(startUrls: $urls));

        return $malIDs->count();
    }
}
