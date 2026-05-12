<?php

namespace App\Console\Commands\Calculators;

use App\Models\Episode;
use App\Models\MediaStat;
use App\Models\UserWatchedEpisode;
use DB;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;
use Throwable;

class CalculateEpisodeStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:episode_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate stats for episodes with sufficient data.';

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
     * @return int
     * @throws Throwable
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $watchRowsPerBatch = 50_000;
        $lastEpisodeId = 0;
        $batchNumber = 0;
        $totalEpisodes = 0;
        $modelType = new Episode()->getMorphClass();
        $mediaStatTable = MediaStat::TABLE_NAME;
        $watchedTable = UserWatchedEpisode::TABLE_NAME;

        $this->info('Aggregating watch counts for: ' . Episode::class);

        while (true) {
            $upperEpisodeId = DB::table($watchedTable)
                ->where('episode_id', '>', $lastEpisodeId)
                ->orderBy('episode_id')
                ->offset($watchRowsPerBatch - 1)
                ->limit(1)
                ->value('episode_id');

            $isFinalBatch = $upperEpisodeId === null;

            $sql = sprintf(
                'INSERT INTO `%s` (`model_type`, `model_id`, `model_count`, `created_at`, `updated_at`)
                 SELECT ?, `episode_id`, COUNT(*), NOW(), NOW()
                 FROM `%s`
                 WHERE `episode_id` > ?%s
                 GROUP BY `episode_id`
                 ON DUPLICATE KEY UPDATE
                     `model_count` = VALUES(`model_count`),
                     `updated_at` = VALUES(`updated_at`)',
                $mediaStatTable,
                $watchedTable,
                $isFinalBatch ? '' : ' AND `episode_id` <= ?'
            );

            $bindings = $isFinalBatch
                ? [$modelType, $lastEpisodeId]
                : [$modelType, $lastEpisodeId, $upperEpisodeId];

            $affected = DB::transaction(fn () => DB::affectingStatement($sql, $bindings));

            $batchNumber++;
            $totalEpisodes += $affected;
            $this->info(sprintf(
                'Batch %d: episode_id %d → %s (%d rows affected)',
                $batchNumber,
                $lastEpisodeId,
                $isFinalBatch ? 'end' : (string) $upperEpisodeId,
                $affected
            ));

            if ($isFinalBatch) {
                break;
            }

            $lastEpisodeId = $upperEpisodeId;
        }

        $this->info(sprintf(
            'Done. %d episode rows affected across %d batches.',
            $totalEpisodes,
            $batchNumber
        ));

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
