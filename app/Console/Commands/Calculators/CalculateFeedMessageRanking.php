<?php

namespace App\Console\Commands\Calculators;

use App\Enums\FeedVoteType;
use App\Models\FeedMessage;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

class CalculateFeedMessageRanking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:feed_message_ranking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates the ranking score of recent feed messages.';

    /**
     * Weight applied to the heart count.
     */
    private const float HEART_WEIGHT = 1.0;

    /**
     * Weight applied to the reply count.
     */
    private const float REPLY_WEIGHT = 2.0;

    /**
     * Weight applied to the re-share count.
     */
    private const float RE_SHARE_WEIGHT = 4.0;

    /**
     * Half-life of the score in hours.
     */
    private const float DECAY_HALF_LIFE_HOURS = 24.0;

    /**
     * Window of feed messages eligible for re-scoring, in days.
     */
    private const int ACTIVITY_WINDOW_DAYS = 14;

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Throwable
     */
    public function handle(): int
    {
        $chunkSize = 1000;
        $heartType = FeedVoteType::Heart()->description;
        $cutoff = now()->subDays(self::ACTIVITY_WINDOW_DAYS);

        $query = FeedMessage::withoutGlobalScopes()
            ->where('created_at', '>=', $cutoff)
            ->with([
                'loveReactant.reactionCounters.reactionType',
            ])
            ->withCount(['replies', 'reShares']);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No feed messages within the activity window.');
            return Command::SUCCESS;
        }

        $this->info('Calculating ranking for ' . $count . ' feed messages.');

        $bar = $this->output->createProgressBar($count);

        $query->chunkById($chunkSize, function (Collection $feedMessages) use ($heartType, $bar) {
            DB::transaction(function () use ($feedMessages, $heartType, $bar) {
                $feedMessages->each(function (FeedMessage $feedMessage) use ($heartType, $bar) {
                    $feedMessage->updateQuietly([
                        'ranking_score' => $this->scoreFor($feedMessage, $heartType),
                    ]);

                    $bar->advance();
                });
            });
        });

        $bar->finish();
        $this->newLine();

        return Command::SUCCESS;
    }

    /**
     * Returns the ranking score for the given feed message.
     *
     * @param FeedMessage $feedMessage
     * @param string      $heartType
     *
     * @return float
     */
    private function scoreFor(FeedMessage $feedMessage, string $heartType): float
    {
        $heartCount = $feedMessage->loveReactant?->reactionCounters
            ->firstWhere('reactionType.name', $heartType)
            ?->getCount() ?? 0;

        $weighted = (self::HEART_WEIGHT * $heartCount)
            + (self::REPLY_WEIGHT * (int) $feedMessage->replies_count)
            + (self::RE_SHARE_WEIGHT * (int) $feedMessage->re_shares_count);

        $ageHours = max(0.0, $feedMessage->created_at->diffInRealHours(now()));
        $decay = exp(-$ageHours / self::DECAY_HALF_LIFE_HOURS);

        return $weighted * $decay;
    }
}
