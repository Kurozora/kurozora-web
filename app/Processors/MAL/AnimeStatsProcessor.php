<?php

namespace App\Processors\MAL;

use App\Models\Anime;
use App\Models\MediaRating;
use App\Spiders\MAL\Models\AnimeStatItem;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;

final class AnimeStatsProcessor extends CustomItemProcessor
{
    /**
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            AnimeStatItem::class
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');
        logger()->channel('stderr')->info('🔄 [MAL_ID:ANIME:' . $malID . '] Processing stats');

        $anime = Anime::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);
        $mediaStat = $anime->mediaStat;
        $scores = $this->cleanScores($item->get('scores') ?? []);
        $scoreAverage = $this->convertScoreAverage($scores, $item->get('scoreAverage') ?? 0.0);
        $scoreCount = $item->get('scoreCount') ?? $this->ratingCount($scores);

//        dd([
//            'scores' => $scores,
//            'scoreCount' => $scoreCount,
//            'scoreAverage' => $scoreAverage
//        ]);

        if ($mediaStat->rating_average <= 0) {
            logger()->channel('stderr')->info('🛠 [MAL_ID:ANIME:' . $malID . '] Updating stats attributes');

            $mediaStat->updateOrInsert([
                'model_type'        => $anime->getMorphClass(),
                'model_id'          => $anime->id,
            ], [
                'rating_1'          => $scores['rating_1'] ?? 0,
                'rating_2'          => $scores['rating_2'] ?? 0,
                'rating_3'          => $scores['rating_3'] ?? 0,
                'rating_4'          => $scores['rating_4'] ?? 0,
                'rating_5'          => $scores['rating_5'] ?? 0,
                'rating_6'          => $scores['rating_6'] ?? 0,
                'rating_7'          => $scores['rating_7'] ?? 0,
                'rating_8'          => $scores['rating_8'] ?? 0,
                'rating_9'          => $scores['rating_9'] ?? 0,
                'rating_10'         => $scores['rating_10'] ?? 0,
                'rating_average'    => $scoreAverage,
                'rating_count'      => $scoreCount,
            ]);

            logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done updating stats attributes');
        }

        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done processing stats');
        return $item;
    }

    /**
     * Cleans the scores.
     *
     * @param array $scores
     * @return array<int, string>
     */
    private function cleanScores(array $scores): array
    {
        $cleanScores = [];

        foreach ($scores as $scoreLabel => $score) {
            $cleanScores[$scoreLabel] = (int) str($score)
                ->remove('(')
                ->remove(' votes)')
                ->value();
        }

        return $cleanScores;
    }

    /**
     * Sums the number of ratings.
     *
     * @param array $scores
     * @return int
     */
    private function ratingCount(array $scores): int
    {
        $totalCount = ($scores['rating_1'] ?? 0) + ($scores['rating_2'] ?? 0) +
        ($scores['rating_3'] ?? 0) + ($scores['rating_4'] ?? 0) +
        ($scores['rating_5'] ?? 0) + ($scores['rating_6'] ?? 0) +
        ($scores['rating_7'] ?? 0) + ($scores['rating_8'] ?? 0) +
        ($scores['rating_9'] ?? 0) + ($scores['rating_10'] ?? 0);

        return $totalCount;
    }

    /**
     * Converts scale-10 rating to scale-5.
     *
     * @param array $scores
     * @param float $scoreAverage
     * @return float
     */
    private function convertScoreAverage(array $scores, float $scoreAverage): float
    {
        if ($scoreAverage === 0.0) {
            return 0.0;
        }

        $totalRatingCount = $this->ratingCount($scores);
        $basicAverageRating = $scoreAverage / 10 * 5;
        $meanRating = MediaRating::where('model_type', Anime::class)
            ->avg('rating');
        $weightedRating = ($totalRatingCount / ($totalRatingCount + Anime::MINIMUM_RATINGS_REQUIRED)) * $basicAverageRating + (Anime::MINIMUM_RATINGS_REQUIRED / ($totalRatingCount + Anime::MINIMUM_RATINGS_REQUIRED)) * $meanRating;

        return $weightedRating;
    }
}
