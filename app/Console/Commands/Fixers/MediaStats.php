<?php

namespace App\Console\Commands\Fixers;

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

class MediaStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:media_stats
                            {--dry-run : Report what is missing without writing anything.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the media stat every model with the trait should have.';

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
     * The number of rows written per chunk.
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
            $this->warn('Dry run. Nothing will be written.');
        }

        $statTable = (new MediaStat)->getTable();
        $created = 0;
        $restored = 0;

        $this->line(sprintf('  %-12s %10s %10s', 'Model', 'Missing', 'Trashed'));

        foreach (self::OWNERS as $owner) {
            $model = new $owner;
            $table = $model->getTable();
            $morphClass = $model->getMorphClass();

            $missing = DB::table($table)
                ->whereNull($table . '.deleted_at')
                ->whereNotExists(function ($stat) use ($statTable, $table, $morphClass) {
                    $stat->select(DB::raw(1))
                        ->from($statTable)
                        ->where($statTable . '.model_type', '=', $morphClass)
                        ->whereColumn($statTable . '.model_id', '=', $table . '.id');
                })
                ->pluck($table . '.id');

            $trashed = DB::table($table)
                ->join($statTable, function ($join) use ($statTable, $table, $morphClass) {
                    $join->on($statTable . '.model_id', '=', $table . '.id')
                        ->where($statTable . '.model_type', '=', $morphClass);
                })
                ->whereNull($table . '.deleted_at')
                ->whereNotNull($statTable . '.deleted_at')
                ->pluck($statTable . '.id');

            $this->line(sprintf('  %-12s %10d %10d', class_basename($owner), $missing->count(), $trashed->count()));

            if ($isDryRun) {
                continue;
            }

            $now = now();

            foreach ($missing->chunk(self::CHUNK_SIZE) as $chunk) {
                DB::table($statTable)->insertOrIgnore($chunk->map(fn ($id) => [
                    'model_type' => $morphClass,
                    'model_id' => $id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());

                $created += $chunk->count();
            }

            foreach ($trashed->chunk(self::CHUNK_SIZE) as $chunk) {
                DB::table($statTable)->whereIn('id', $chunk)->update(['deleted_at' => null, 'updated_at' => $now]);

                $restored += $chunk->count();
            }
        }

        $this->newLine();
        $this->info($isDryRun ? 'Dry run complete.' : 'Created ' . $created . ' and restored ' . $restored . ' media stat(s).');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }
}
