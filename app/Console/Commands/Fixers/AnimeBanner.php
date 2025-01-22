<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class AnimeBanner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:anime_banner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes anime banners.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Anime::withoutGlobalScopes()
            ->where('tvdb_id', '!=', null)
            ->whereHas('media', function ($query) {
                return $query->where('collection_name', '=', 'banner');
            }, '=', 0)
            ->chunk(100, function (Collection $animes) {
                $this->info('Fixing ' . $animes->count() . ' banners');

                /** @var Anime $anime */
                foreach ($animes as $anime) {
                    $this->call('scrape:tvdb_banner', ['tvdbID' => $anime->tvdb_id]);
                }
            });

        return Command::SUCCESS;
    }
}
