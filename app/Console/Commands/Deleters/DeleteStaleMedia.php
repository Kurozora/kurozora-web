<?php

namespace App\Console\Commands\Deleters;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Telescope\Telescope;
use Pulse;
use Throwable;

class DeleteStaleMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:stale_media
                            {--dry-run : Report what would be deleted without deleting it.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes media whose model no longer exists.';

    /**
     * The number of media deleted per chunk.
     *
     * @var int
     */
    protected const int CHUNK_SIZE = 500;

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

        $table = (new Media)->getTable();
        $morphClasses = DB::table($table)->distinct()->pluck('model_type');
        $stale = [];

        foreach ($morphClasses as $morphClass) {
            $ownerTable = $this->tableFor($morphClass);

            $query = DB::table($table)->where('model_type', '=', $morphClass);

            if ($ownerTable !== null) {
                $query->whereNotExists(function ($existing) use ($ownerTable, $table) {
                    $existing->select(DB::raw(1))
                        ->from($ownerTable)
                        ->whereColumn($ownerTable . '.id', '=', $table . '.model_id');
                });
            }

            $ids = $query->pluck('id');

            if ($ids->isEmpty()) {
                continue;
            }

            $stale[$morphClass] = $ids;
            $this->line(sprintf('  %-40s %6d', $morphClass, $ids->count()));

            if ($ownerTable === null) {
                $this->warn('  Unresolvable model type. Confirm before deleting.');
            }
        }

        $this->newLine();
        $total = collect($stale)->map->count()->sum();

        if ($isDryRun || $total === 0) {
            $this->info(($isDryRun ? 'Would delete: ' : 'Deleted: ') . $total . ' stale media.');

            Pulse::startRecording();
            Telescope::startRecording();

            return Command::SUCCESS;
        }

        $deleted = 0;
        $failed = 0;

        foreach ($stale as $ids) {
            foreach ($ids->chunk(self::CHUNK_SIZE) as $chunk) {
                // Deleting through the model removes the stored file too.
                foreach (Media::withoutGlobalScopes()->whereIn('id', $chunk)->get() as $media) {
                    try {
                        $media->delete();
                        $deleted++;
                    } catch (Throwable $throwable) {
                        $failed++;
                        logger()->channel('stderr')->error('Media ' . $media->id . ': ' . $throwable->getMessage());
                    }
                }
            }
        }

        $this->info('Deleted: ' . $deleted . ' stale media.');

        if ($failed > 0) {
            $this->warn($failed . ' media could not be deleted. See the log.');
        }

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Resolve the table backing a morph class.
     *
     * @param string $morphClass
     * @return string|null
     */
    protected function tableFor(string $morphClass): ?string
    {
        $class = Model::getActualClassNameForMorph($morphClass);

        if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
            return null;
        }

        return (new $class)->getTable();
    }
}
