<?php

namespace App\Console\Commands;

use App\Anime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculateAnimeRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ratings:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates the average rating for Anime items';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Update the average rating and rating count
        DB::table('animes')->update(['average_rating' => DB::raw('IFNULL((SELECT AVG(rating) FROM anime_ratings WHERE anime_id = animes.id), 0)')]);
        DB::table('animes')->update(['rating_count' => DB::raw('IFNULL((SELECT COUNT(rating) FROM anime_ratings WHERE anime_id = animes.id), 0)')]);

        // Console output
        $aniCount = Anime::count();
        $this->info(sprintf('Updated rating information for %d Anime %s.', $aniCount, str_plural('item', $aniCount)));
    }
}
