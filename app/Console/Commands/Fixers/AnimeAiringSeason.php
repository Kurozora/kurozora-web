<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

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

        Anime::where('started_at', '!=', null)
            ->whereYear('started_at', '=', $year)
            ->chunk(1000, function (Collection $animes) {
                /** @var Anime $anime */
                foreach ($animes as $anime) {
                    print $anime->started_at->month . '|' . $anime->generateAiringSeason() . PHP_EOL;
                    $anime->update([
                        'air_season' => $anime->generateAiringSeason()
                    ]);
                }
            });

        return Command::SUCCESS;
    }
}
