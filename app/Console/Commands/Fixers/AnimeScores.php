<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use App\Models\MediaStat;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AnimeScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:anime_scores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes anime scores where possible';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Anime::withoutGlobalScopes()
            ->without(['genres', 'media', 'mediaStat', 'tv_rating', 'translations'])
            ->join(MediaStat::TABLE_NAME, MediaStat::TABLE_NAME . '.model_id', '=', Anime::TABLE_NAME . '.id')
            ->where([
                [MediaStat::TABLE_NAME . '.model_type', '=', Anime::class],
                [MediaStat::TABLE_NAME . '.rating_average', '=', 0],
                [MediaStat::TABLE_NAME . '.rating_1', '=', 0],
                [MediaStat::TABLE_NAME . '.rating_2', '=', 0],
                [MediaStat::TABLE_NAME . '.rating_3', '=', 0],
                [MediaStat::TABLE_NAME . '.rating_4', '=', 0],
                [MediaStat::TABLE_NAME . '.rating_5', '=', 0],
                [MediaStat::TABLE_NAME . '.rating_6', '=', 0],
                [MediaStat::TABLE_NAME . '.rating_7', '=', 0],
                [MediaStat::TABLE_NAME . '.rating_8', '=', 0],
                [MediaStat::TABLE_NAME . '.rating_9', '=', 0],
                [MediaStat::TABLE_NAME . '.rating_10', '=', 0],
            ])
            ->select([Anime::TABLE_NAME . '.id as AS', 'mal_id'])
            ->chunkById(5, function (Collection $animes) use (&$skip) {
                $this->call('scrape:mal_anime', ['malID' => $animes->pluck('mal_id')->join(',')]);
            }, Anime::TABLE_NAME . '.id', 'AS');

        return Command::SUCCESS;
    }
}
