<?php

namespace App\Console\Commands\Fixers;

use App\Models\MediaRating;
use App\Support\LowEffortReviewDetector;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Telescope\Telescope;
use Pulse;

class ReviewLowEffortFlags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:review_low_effort_flags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reclassifies media_ratings.is_low_effort by re-running the detector over every written review';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $scanned = 0;
        $demoted = 0;
        $restored = 0;

        MediaRating::whereNotNull('description')
            ->select(['id', 'description', 'is_low_effort'])
            ->chunkById(1000, function (Collection $mediaRatings) use (&$scanned, &$demoted, &$restored) {
                $lowEffortIDs = [];
                $ordinaryIDs = [];

                foreach ($mediaRatings as $mediaRating) {
                    $scanned++;
                    $isLowEffort = LowEffortReviewDetector::detect($mediaRating->description);

                    if ($isLowEffort === $mediaRating->is_low_effort) {
                        continue;
                    }

                    if ($isLowEffort) {
                        $lowEffortIDs[] = $mediaRating->id;
                    } else {
                        $ordinaryIDs[] = $mediaRating->id;
                    }
                }

                $demoted += $this->apply(true, $lowEffortIDs);
                $restored += $this->apply(false, $ordinaryIDs);
            });

        Pulse::startRecording();
        Telescope::startRecording();

        $this->info('Scanned ' . $scanned . ' reviews. Demoted ' . $demoted . ', restored ' . $restored . '.');

        return Command::SUCCESS;
    }

    /**
     * Writes the verdict to the given reviews.
     *
     * @param bool  $isLowEffort
     * @param array $ids
     *
     * @return int
     */
    protected function apply(bool $isLowEffort, array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        // Reclassifying is not a user edit, so `updated_at` is left where it is.
        MediaRating::whereIn('id', $ids)
            ->toBase()
            ->update(['is_low_effort' => $isLowEffort]);

        return count($ids);
    }
}
