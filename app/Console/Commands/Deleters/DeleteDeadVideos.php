<?php

namespace App\Console\Commands\Deleters;

use App\Enums\VideoSource;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Laravel\Telescope\Telescope;
use Pulse;

class DeleteDeadVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:dead_videos
                            {--limit=0 : The number of videos to check. 0 checks all of them.}
                            {--concurrency=10 : The number of requests in flight at once.}
                            {--dry-run : Report what would be deleted without deleting it.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete videos whose YouTube source no longer exists.';

    /**
     * The endpoint a video's availability is read from.
     */
    private const string OEMBED_URL = 'https://www.youtube.com/oembed?format=json&url=';

    /**
     * The watch page a video is looked up by.
     */
    private const string WATCH_URL = 'https://www.youtube.com/watch?v=';

    /**
     * The number of videos read from the database at a time.
     */
    private const int CHUNK_SIZE = 500;

    /**
     * The number of times a throttled batch is retried before it is left for the next run.
     */
    private const int MAX_ATTEMPTS = 4;

    /**
     * The seconds waited after the first throttled batch, doubling on each retry.
     */
    private const int BACKOFF_SECONDS = 30;

    /**
     * The number of batches that may exhaust their retries before the command gives up.
     */
    private const int THROTTLE_TOLERANCE = 3;

    /**
     * The number of batches that exhausted their retries against a throttling YouTube.
     *
     * @var int $throttledBatches
     */
    private int $throttledBatches = 0;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $concurrency = max(1, (int) $this->option('concurrency'));
        $limit = (int) $this->option('limit');
        $isDryRun = (bool) $this->option('dry-run');

        $query = Video::where('source', '=', VideoSource::YouTube);
        $total = $query->clone()->count();

        if ($limit > 0) {
            $total = min($limit, $total);
        }

        if ($total === 0) {
            $this->info('No videos to check. Exiting...');

            return Command::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn('Dry run. Nothing will be deleted.');
        }

        $bar = $this->output->createProgressBar($total);
        $checked = 0;
        $this->throttledBatches = 0;
        $statuses = [];
        $deleted = 0;

        $query->chunkById(self::CHUNK_SIZE, function (Collection $videos) use (&$checked, &$statuses, &$deleted, $bar, $concurrency, $limit, $isDryRun) {
            foreach ($videos->chunk($concurrency) as $batch) {
                if ($limit > 0 && $checked >= $limit) {
                    return false;
                }

                if ($limit > 0) {
                    $batch = $batch->take($limit - $checked);
                }

                $batch = $batch->values();

                if ($this->throttledBatches >= self::THROTTLE_TOLERANCE) {
                    return false;
                }

                foreach ($this->availability($batch) as $index => $status) {
                    $video = $batch[$index];
                    $checked++;
                    $statuses[$status] = ($statuses[$status] ?? 0) + 1;

                    if ($status === 404) {
                        logger()->channel('stderr')->info('🗑 [YOUTUBE:' . $video->code . '] Gone; removing video ' . $video->id . '.');

                        if (!$isDryRun) {
                            $video->delete();
                        }

                        $deleted++;
                    }

                    $bar->advance();
                }
            }

            return true;
        });

        $bar->finish();
        $this->newLine(2);

        if ($this->throttledBatches >= self::THROTTLE_TOLERANCE) {
            $this->error('Stopped early: YouTube kept answering 429. Retry later, or lower --concurrency.');
        }

        $this->report($statuses, $deleted, $isDryRun);

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Returns the status YouTube reports for each of the given videos.
     *
     * @param Collection $videos
     *
     * @return int[]
     */
    private function availability(Collection $videos): array
    {
        $pending = $videos->keys()->all();
        $results = [];

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS && !empty($pending); $attempt++) {
            if ($attempt > 0) {
                $seconds = self::BACKOFF_SECONDS * (2 ** ($attempt - 1));
                $this->newLine();
                $this->warn('Throttled by YouTube. Waiting ' . $seconds . 's before retrying ' . count($pending) . ' videos.');
                sleep($seconds);
            }

            $responses = Http::pool(function (Pool $pool) use ($pending, $videos) {
                return collect($pending)->map(function (int $index) use ($pool, $videos) {
                    return $pool->timeout(15)
                        ->head(self::OEMBED_URL . urlencode(self::WATCH_URL . $videos[$index]->code));
                })->all();
            });

            $throttled = [];

            foreach (array_values($pending) as $position => $index) {
                $response = $responses[$position] ?? null;
                $status = $response instanceof Response ? $response->status() : 0;

                if ($status === 429) {
                    $throttled[] = $index;

                    continue;
                }

                $results[$index] = $status;
            }

            $pending = $throttled;
        }

        if (!empty($pending)) {
            $this->throttledBatches++;
        }

        foreach ($pending as $index) {
            $results[$index] = 429;
        }

        return $results;
    }

    /**
     * Prints what each status was answered by, and how many videos went away.
     *
     * @param array $statuses
     * @param int   $deleted
     * @param bool  $isDryRun
     *
     * @return void
     */
    private function report(array $statuses, int $deleted, bool $isDryRun): void
    {
        ksort($statuses);

        $rows = [];
        foreach ($statuses as $status => $count) {
            $rows[] = [
                match ($status) {
                    200 => 'Available',
                    400 => 'Malformed code',
                    401, 403 => 'Embedding restricted',
                    429 => 'Throttled, not checked',
                    404 => 'Gone',
                    0 => 'Unreachable',
                    default => 'Other',
                },
                $status,
                $count,
            ];
        }

        $this->table(['Result', 'Status', 'Videos'], $rows);
        $this->info(($isDryRun ? 'Would delete: ' : 'Deleted: ') . $deleted . '.');
    }
}
