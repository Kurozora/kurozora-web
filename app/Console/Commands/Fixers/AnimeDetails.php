<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use Illuminate\Console\Command;

class AnimeDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:anime_details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix anime details';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $animes = Anime::withoutGlobalScopes()
            ->where('mal_id', '!=', null)
            ->where('status_id', '!=', 4)
            ->whereDate('updated_at', '<', today())
            ->pluck('mal_id');

        $this->info('Fixing ' . $animes->count() . ' anime');

        if ($animes->count()) {
            $this->call('scrape:mal_anime', ['malID' => $animes->implode(',')]);
        }

        return Command::SUCCESS;
    }
}
