<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Telescope\Telescope;
use Pulse;

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
        Pulse::stopRecording();
        Telescope::stopRecording();

        $chunkSize = 2000;
        $year = $this->argument('year') ?? now()->year;

        Anime::where([
            ['started_at', '!=', null],
        ])
            ->whereYear('started_at', '=', $year)
            ->chunkById($chunkSize, function (Collection $animes) {
                DB::transaction(function () use ($animes) {
                    $ids = [];
                    $cases = '';

                    foreach ($animes as $anime) {
                        $ids[] = (int) $anime->id;
                        $cases .= ' WHEN ' . (int) $anime->id . ' THEN ' . (int) $anime->started_at->dayOfWeek;
                    }

                    if (empty($ids)) {
                        return;
                    }

                    Anime::withoutGlobalScopes()
                        ->whereIn('id', $ids)
                        ->update(['air_day' => DB::raw('CASE id' . $cases . ' END')]);
                });
            });

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
