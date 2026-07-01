<?php

namespace App\Console\Commands\Scrapers\IGDB;

use App\Models\Game as GameModel;
use App\Models\Platform;
use App\Models\Studio;
use App\Spiders\IGDB\GameSpider;
use App\Spiders\IGDB\Http\CurlImpersonateGqlClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Laravel\Telescope\Telescope;
use Pulse;
use RoachPHP\Roach;
use RoachPHP\Spider\Configuration\Overrides;

class Game extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:igdb_games
                            {slugs?* : One or more IGDB game slugs to scrape}
                            {--query= : Resolve a title to a slug via IGDB autocomplete, then scrape it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape game data from IGDB.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $slugs = $this->argument('slugs');

        if ($query = $this->option('query')) {
            if ($slug = $this->resolveSlug($query)) {
                $slugs[] = $slug;
            }
        }

        $slugs = array_values(array_unique(array_filter($slugs)));

        if (empty($slugs)) {
            $this->info('No slugs to scrape. Adios...');
            return Command::INVALID;
        }

        Pulse::stopRecording();
        Telescope::stopRecording();
        GameModel::disableSearchSyncing();
        Platform::disableSearchSyncing();
        Studio::disableSearchSyncing();

        config(['roach.client' => CurlImpersonateGqlClient::class]);

        Roach::startSpider(GameSpider::class, new Overrides(), ['slugs' => $slugs]);

        GameModel::enableSearchSyncing();
        Platform::enableSearchSyncing();
        Studio::enableSearchSyncing();
        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Resolve a game slug from a title.
     *
     * @param string $query
     * @return string|null
     */
    protected function resolveSlug(string $query): ?string
    {
        $response = Http::get(config('scraper.domains.igdb.autocomplete'), ['q' => $query]);

        if (!$response->successful()) {
            return null;
        }

        $suggestions = $response->json('game_suggest') ?? [];

        foreach ($suggestions as $suggestion) {
            if (strcasecmp($suggestion['name'] ?? '', $query) === 0) {
                return str($suggestion['url'])->afterLast('/')->value();
            }
        }

        $url = $suggestions[0]['url'] ?? null;

        return empty($url) ? null : str($url)->afterLast('/')->value();
    }
}
