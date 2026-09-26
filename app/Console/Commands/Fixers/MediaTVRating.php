<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Season;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Telescope\Telescope;
use Pulse;

class MediaTVRating extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:media_tv_rating';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix anime, manga, games, seasons, and episodes without a TV rating.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $chunkSize = 2000;

        foreach ([Anime::class, Manga::class, Game::class] as $modelClass) {
            $ratedCount = 0;

            $modelClass::withoutGlobalScopes()
                ->select(['id'])
                ->whereNull('tv_rating_id')
                ->chunkById($chunkSize, function (Collection $models) use ($modelClass, &$ratedCount) {
                    $modelClass::withoutGlobalScopes()
                        ->whereKey($models->modelKeys())
                        ->update(['tv_rating_id' => 1]);

                    $ratedCount += $models->count();

                    $this->info('Rated ' . $models->count() . ' ' . class_basename($modelClass) . ' up to ID ' . $models->last()->id);
                });

            $this->info(class_basename($modelClass) . ': rated ' . $ratedCount . ' without a TV rating');
        }

        $parentColumns = [
            Season::class => [Anime::class, 'anime_id'],
            Episode::class => [Season::class, 'season_id'],
        ];

        foreach ($parentColumns as $modelClass => [$parentClass, $parentColumn]) {
            $ratedCount = 0;

            $modelClass::withoutGlobalScopes()
                ->select(['id', $parentColumn])
                ->whereNull('tv_rating_id')
                ->chunkById($chunkSize, function (Collection $models) use ($modelClass, $parentClass, $parentColumn, &$ratedCount) {
                    $parentTVRatingIDs = $parentClass::withoutGlobalScopes()
                        ->whereKey($models->pluck($parentColumn)->unique()->values())
                        ->pluck('tv_rating_id', 'id');
                    $cases = '';

                    foreach ($models as $model) {
                        $cases .= ' WHEN ' . (int) $model->id . ' THEN ' . (int) ($parentTVRatingIDs[$model->{$parentColumn}] ?? 1);
                    }

                    $modelClass::withoutGlobalScopes()
                        ->whereKey($models->modelKeys())
                        ->update(['tv_rating_id' => DB::raw('CASE id' . $cases . ' END')]);

                    $ratedCount += $models->count();

                    $this->info('Rated ' . $models->count() . ' ' . class_basename($modelClass) . ' up to ID ' . $models->last()->id);
                });

            $this->info(class_basename($modelClass) . ': rated ' . $ratedCount . ' without a TV rating');
        }

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
