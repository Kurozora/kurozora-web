<?php

namespace App\Console\Commands\Deleters;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaStat;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Telescope\Telescope;
use Pulse;

class DeleteOrphanedMediaStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:orphaned_media_stats
                            {--dry-run : Report what would be deleted without deleting it.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete media stats whose model no longer exists.';

    /**
     * The models that own a media stat.
     *
     * @var string[]
     */
    protected const array OWNERS = [
        Anime::class,
        Character::class,
        Episode::class,
        Game::class,
        Manga::class,
        Person::class,
        Song::class,
        Studio::class,
    ];

    /**
     * The number of stats loaded per chunk.
     *
     * @var int
     */
    protected const int CHUNK_SIZE = 1000;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Dry run. Nothing will be deleted.');
        }

        $tables = [];

        foreach (self::OWNERS as $owner) {
            $model = new $owner;
            $tables[$model->getMorphClass()] = $model->getTable();
        }

        $orphaned = [];

        MediaStat::withoutGlobalScopes()
            ->select(['id', 'model_type', 'model_id'])
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($stats) use ($tables, &$orphaned) {
                foreach ($stats->groupBy('model_type') as $morphClass => $group) {
                    $table = $tables[$morphClass] ?? null;

                    if ($table === null) {
                        $orphaned[$morphClass] = array_merge($orphaned[$morphClass] ?? [], $group->pluck('id')->all());

                        continue;
                    }

                    $existing = DB::table($table)
                        ->whereIn('id', $group->pluck('model_id')->unique()->all())
                        ->pluck('id')
                        ->flip();

                    foreach ($group as $stat) {
                        if (!$existing->has($stat->model_id)) {
                            $orphaned[$morphClass][] = $stat->id;
                        }
                    }
                }
            });

        $total = 0;

        foreach ($orphaned as $morphClass => $ids) {
            $total += count($ids);
            $this->line(sprintf('  %-32s %6d', $morphClass, count($ids)));

            if (!isset($tables[$morphClass])) {
                $this->warn('  Unrecognised model type. Confirm before deleting.');
            }

            if (!$isDryRun) {
                foreach (array_chunk($ids, self::CHUNK_SIZE) as $chunk) {
                    MediaStat::withoutGlobalScopes()->whereIn('id', $chunk)->forceDelete();
                }
            }
        }

        $this->newLine();
        $this->info(($isDryRun ? 'Would delete: ' : 'Deleted: ') . $total . ' orphaned media stat(s).');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
