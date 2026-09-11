<?php

namespace App\Jobs\Concerns;

use App\Events\LibraryImportProgressed;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reports a library import's progress to the owning user's devices.
 */
trait ReportsLibraryImportProgress
{
    /**
     * The number of entries the import will process.
     *
     * @var int $importProgressTotal
     */
    protected int $importProgressTotal = 0;

    /**
     * The number of entries processed so far.
     *
     * @var int $importProgressProcessed
     */
    protected int $importProgressProcessed = 0;

    /**
     * When progress was last published, as a Unix timestamp.
     *
     * @var float $importProgressBroadcastAt
     */
    protected float $importProgressBroadcastAt = 0.0;

    /**
     * Begins reporting an import of the given number of entries.
     *
     * @param int $total
     *
     * @return void
     */
    protected function startImportProgress(int $total): void
    {
        $this->importProgressTotal = $total;
        $this->importProgressProcessed = 0;
        $this->importProgressBroadcastAt = 0.0;

        if ($total > 0) {
            $this->broadcastImportProgress();
        }
    }

    /**
     * Reports one more processed entry.
     *
     * @return void
     */
    protected function advanceImportProgress(): void
    {
        $this->importProgressProcessed++;

        $isFinal = $this->importProgressProcessed >= $this->importProgressTotal;
        $interval = (float) config('library.import_progress_interval_seconds', 1);

        if (!$isFinal && microtime(true) - $this->importProgressBroadcastAt < $interval) {
            return;
        }

        $this->broadcastImportProgress();
    }

    /**
     * Publishes the current progress.
     *
     * @return void
     */
    private function broadcastImportProgress(): void
    {
        $this->importProgressBroadcastAt = microtime(true);

        try {
            LibraryImportProgressed::dispatch($this->user->getKey(), $this->importProgressProcessed, $this->importProgressTotal);
        } catch (Throwable $exception) {
            Log::warning('Library import progress broadcast failed.', [
                'user_id' => $this->user->getKey(),
                'processed' => $this->importProgressProcessed,
                'total' => $this->importProgressTotal,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
