<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use Illuminate\Console\Command;

class AnimeEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:anime_episodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix anime episodes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $animes = Anime::withoutGlobalScopes()
            ->where('tvdb_id', '!=', null)
            ->whereHas('episodes', null, '=', 0)
            ->pluck('tvdb_id');

        $this->info('Fixing ' . $animes->count() . ' anime episodes');

        $this->call('scrape:tvdb_episode', ['tvdbID' => $animes->implode(',')]);

        return Command::SUCCESS;
    }
}
