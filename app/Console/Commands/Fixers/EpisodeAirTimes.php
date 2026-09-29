<?php

namespace App\Console\Commands\Fixers;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Season;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Telescope\Telescope;
use Pulse;

class EpisodeAirTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:episode_air_times
                            {id? : The ID of the anime whose episodes should be fixed}
                            {--dry-run : List the episodes that would change without saving them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the air time of single-season anime episodes to match their anime\'s air time.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $animeID = $this->argument('id');
        $isDryRun = (bool) $this->option('dry-run');

        Pulse::stopRecording();
        Telescope::stopRecording();

        $chunkSize = 500;
        $fixedCount = 0;
        $datedEpisodes = function ($query) {
            $query->whereNotNull(Episode::TABLE_NAME . '.started_at')
                ->whereNull(Episode::TABLE_NAME . '.deleted_at')
                ->whereNull(Season::TABLE_NAME . '.deleted_at');
        };

        Anime::withoutGlobalScopes()
            ->select(['id', 'original_title', 'air_time'])
            ->whereNull('deleted_at')
            ->whereNotNull('air_time')
            ->when($animeID, function ($query, $animeID) {
                $query->whereKey($animeID);
            })
            ->whereHas('seasons', function ($query) {
                $query->whereNull(Season::TABLE_NAME . '.deleted_at');
            }, '=', 1)
            ->whereHas('episodes', function ($query) use ($datedEpisodes) {
                $datedEpisodes($query);
                $query->whereRaw('TIME(CONVERT_TZ(' . Episode::TABLE_NAME . ".started_at, '+00:00', '+09:00')) <> " . Anime::TABLE_NAME . '.air_time');
            })
            ->with(['episodes' => function ($query) use ($datedEpisodes) {
                $datedEpisodes($query);
                $query->select([
                    Episode::TABLE_NAME . '.id',
                    Episode::TABLE_NAME . '.number_total',
                    Episode::TABLE_NAME . '.started_at',
                    Episode::TABLE_NAME . '.ended_at',
                ]);
            }])
            ->chunkById($chunkSize, function (Collection $animes) use ($isDryRun, &$fixedCount) {
                $ids = [];
                $startedAtCases = '';
                $endedAtCases = '';
                $changes = [];

                /** @var Anime $anime */
                foreach ($animes as $anime) {
                    /** @var Episode $episode */
                    foreach ($anime->episodes as $episode) {
                        $startedAt = $episode->started_at->copy()->setTimezone('Asia/Tokyo');
                        $fixedStartedAt = $startedAt->copy()->setTimeFromTimeString($anime->air_time);

                        if ($fixedStartedAt->equalTo($startedAt)) {
                            continue;
                        }

                        $shiftInSeconds = (int) $startedAt->diffInSeconds($fixedStartedAt);
                        $fixedEndedAt = $episode->ended_at?->copy()->addSeconds($shiftInSeconds);

                        $ids[] = (int) $episode->id;
                        $startedAtCases .= ' WHEN ' . (int) $episode->id . " THEN '" . $fixedStartedAt->copy()->utc()->toDateTimeString() . "'";
                        $endedAtCases .= ' WHEN ' . (int) $episode->id . ' THEN ' . ($fixedEndedAt ? "'" . $fixedEndedAt->utc()->toDateTimeString() . "'" : 'NULL');
                        $changes[] = sprintf('%s episode %d: %s to %s', $anime->original_title, $episode->number_total, $startedAt->format('D Y-m-d H:i T'), $fixedStartedAt->format('H:i T'));
                    }
                }

                if (empty($ids)) {
                    return;
                }

                if (!$isDryRun) {
                    Episode::withoutGlobalScopes()
                        ->whereIn('id', $ids)
                        ->update([
                            'started_at' => DB::raw('CASE id' . $startedAtCases . ' END'),
                            'ended_at' => DB::raw('CASE id' . $endedAtCases . ' END'),
                        ]);
                }

                foreach ($changes as $change) {
                    $this->info(($isDryRun ? 'Would fix ' : 'Fixed ') . $change);
                }

                $fixedCount += count($ids);
            });

        $this->info(($isDryRun ? 'Would fix ' : 'Fixed ') . $fixedCount . ' episodes.');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
