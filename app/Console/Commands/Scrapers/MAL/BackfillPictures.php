<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Enums\MediaCollection;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Manga;
use App\Models\Person;
use App\Spiders\MAL\PicturesSpider;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class BackfillPictures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_backfill_pictures
                            {model? : Limit to anime, manga, character, or people; all when omitted}
                            {--limit= : Cap the number of entities scraped per model}
                            {--days=1 : Skip entities scraped within this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape the pictures page for entities with no gallery beyond their single image.';

    /**
     * The models to backfill, keyed by type.
     *
     * @var array<string, array{0: class-string, 1: string, 2: string}>
     */
    private array $targets = [
        'anime' => [Anime::class, MediaCollection::Poster, 'scraper.domains.mal.anime_pictures'],
        'manga' => [Manga::class, MediaCollection::Poster, 'scraper.domains.mal.manga_pictures'],
        'character' => [Character::class, MediaCollection::Profile, 'scraper.domains.mal.character_pictures'],
        'people' => [Person::class, MediaCollection::Profile, 'scraper.domains.mal.people_pictures'],
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $model = $this->argument('model');

        if ($model !== null && !array_key_exists($model, $this->targets)) {
            $this->error('Model must be anime, manga, character, or people.');
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

        foreach ($this->targets as $type => [$modelClass, $collection, $configKey]) {
            if ($model !== null && $model !== $type) {
                continue;
            }

            $count = $this->scrapeMissing($modelClass, $collection, config($configKey), $limit, $days);
            $this->info("Scraped $count $type.");
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
     * Scrape the pictures page for entities with at most one image in the collection.
     *
     * @param class-string $model
     * @param string       $collection
     * @param string       $baseURL
     * @param int|null     $limit
     * @param int          $days
     *
     * @return int
     */
    private function scrapeMissing(string $model, string $collection, string $baseURL, ?int $limit, int $days): int
    {
        $query = $model::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->whereNotNull('mal_id')
            ->where('updated_at', '<', now()->subDays($days))
            ->withCount(['media as gallery_count' => function (Builder $query) use ($collection) {
                $query->where('collection_name', '=', $collection);
            }])
            ->having('gallery_count', '<=', 1)
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $malIDs = $query->pluck('mal_id');

        if ($malIDs->isEmpty()) {
            return 0;
        }

        $urls = $malIDs->map(function ($malID) use ($baseURL) {
            return str($baseURL)->replace(':x', $malID)->value();
        })->all();

        Roach::startSpider(PicturesSpider::class, new Overrides(startUrls: $urls));

        return $malIDs->count();
    }
}
