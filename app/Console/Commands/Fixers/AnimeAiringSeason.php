<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class AnimeAiringSeason extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:anime_airing_season {year? : The year whose anime should have its season fixed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes anime airing season where possible';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $year = $this->argument('year') ?? now()->year;

        Anime::where([
            ['started_at', '!=', null],
        ])
            ->whereYear('started_at', '=', $year)
            ->chunkById(1000, function (Collection $animes) {
                try {
                    DB::beginTransaction();

                    $animes->each(function (Anime $anime) {
                        $anime->update([
                            'air_day' => $anime->started_at->dayOfWeek
                        ]);
                    });

                    DB::commit();
                } catch (Throwable $e) {
                    DB::rollBack();
                }
            });

        return Command::SUCCESS;
    }
}
