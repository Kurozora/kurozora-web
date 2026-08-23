<?php

namespace App\Console\Commands\Scrapers\YouTube;

use App\Enums\VideoSource;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Laravel\Telescope\Telescope;
use Pulse;
use Throwable;

class VideoPublishedDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:youtube_video_published_dates
                            {--limit=0 : The number of videos to process. 0 processes all of them.}
                            {--concurrency=8 : The number of requests in flight at once.}
                            {--refresh : Also process videos that already have a published date.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape the published date of YouTube videos.';

    /**
     * The watch page a video's published date is read from.
     */
    private const string WATCH_URL = 'https://www.youtube.com/watch?v=';

    /**
     * The browser the requests identify as.
     */
    private const string USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

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

        $query = Video::where('source', '=', VideoSource::YouTube)
            ->when(!$this->option('refresh'), function ($query) {
                $query->whereNull('published_at');
            });

        $total = $query->clone()->count();

        if ($limit > 0) {
            $total = min($limit, $total);
        }

        if ($total === 0) {
            $this->info('No videos to process. Exiting...');

            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $processed = 0;
        $resolved = 0;
        $failed = 0;
        $this->throttledBatches = 0;

        $query->chunkById(self::CHUNK_SIZE, function (Collection $videos) use (&$processed, &$resolved, &$failed, $bar, $concurrency, $limit) {
            foreach ($videos->chunk($concurrency) as $batch) {
                if ($limit > 0 && $processed >= $limit) {
                    return false;
                }

                if ($limit > 0) {
                    $batch = $batch->take($limit - $processed);
                }

                $batch = $batch->values();

                if ($this->throttledBatches >= self::THROTTLE_TOLERANCE) {
                    return false;
                }

                foreach ($this->publishedDates($batch) as $index => $publishedAt) {
                    $processed++;

                    if ($publishedAt === null) {
                        $failed++;
                    } else {
                        $batch[$index]->update(['published_at' => $publishedAt]);
                        $resolved++;
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

        $this->info('Resolved: ' . $resolved . '. Unresolved: ' . $failed . '.');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Returns the date each of the given videos was published.
     *
     * @param Collection $videos
     *
     * @return array<int, ?Carbon>
     */
    private function publishedDates(Collection $videos): array
    {
        $responses = $this->fetch($videos);

        return $videos->keys()
            ->map(function (int $index) use ($videos, $responses) {
                return $this->publishedDate($videos[$index]->code, $responses[$index] ?? null);
            })
            ->all();
    }

    /**
     * Fetches the watch page of each given video, retrying the ones YouTube throttles.
     *
     * @param Collection $videos
     *
     * @return array<int, mixed>
     */
    private function fetch(Collection $videos): array
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
                    return $pool->withHeaders([
                        'User-Agent' => self::USER_AGENT,
                        'Accept-Language' => 'en-US,en;q=0.9',
                    ])
                        ->timeout(30)
                        ->get(self::WATCH_URL . $videos[$index]->code);
                })->all();
            });

            $throttled = [];

            foreach (array_values($pending) as $position => $index) {
                $response = $responses[$position] ?? null;

                if ($response instanceof Response && $response->status() === 429) {
                    $throttled[] = $index;

                    continue;
                }

                $results[$index] = $response;
            }

            $pending = $throttled;
        }

        if (!empty($pending)) {
            $this->throttledBatches++;
        }

        foreach ($pending as $index) {
            $results[$index] = null;
        }

        return $results;
    }

    /**
     * Reads the published date out of a watch page.
     *
     * @param string $code
     * @param mixed  $response
     *
     * @return ?Carbon
     */
    private function publishedDate(string $code, mixed $response): ?Carbon
    {
        if (!$response instanceof Response) {
            logger()->channel('stderr')->error('❌ [YOUTUBE:' . $code . '] Request failed.');

            return null;
        }

        if (!$response->successful()) {
            logger()->channel('stderr')->error('❌ [YOUTUBE:' . $code . '] status:' . $response->status());

            return null;
        }

        $date = str($response->body())
            ->match('/"(?:uploadDate|publishDate)":"([^"]+)"/');

        if ($date->isEmpty()) {
            logger()->channel('stderr')->warning('⚠️ [YOUTUBE:' . $code . '] No published date; the video is unavailable or the page changed.');

            return null;
        }

        try {
            return Carbon::parse($date->value())
                ->setTimezone(config('app.timezone'));
        } catch (Throwable $throwable) {
            logger()->channel('stderr')->error('❌ [YOUTUBE:' . $code . '] ' . $throwable->getMessage());

            return null;
        }
    }
}
