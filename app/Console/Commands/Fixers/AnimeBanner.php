<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use Artisan;
use Illuminate\Console\Command;

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
        $animes = Anime::withoutGlobalScopes()
            ->where('tvdb_id', '!=', null)
            ->whereHas('media', function ($query) {
                return $query->where('collection_name', '=', 'banner');
            }, '=', 0)
            ->pluck('tvdb_id_id')
//            ->implode(',');
            ->count();
        dd($animes);

        Artisan::call('scrape:tvdb_banners', ['tvdbID' => $animes]);

        return Command::SUCCESS;
    }
}
