<?php

namespace App\Processors\MAL;

use App\Models\Manga;
use App\Models\MediaStat;
use App\Spiders\MAL\Models\MangaStatItem;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;

final class MangaStatsProcessor extends CustomItemProcessor
{
    /**
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            MangaStatItem::class
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');
        logger()->channel('stderr')->info('🔄 [MAL_ID:MANGA:' . $malID . '] Processing stats');

        $manga = Manga::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);
        $mediaStat = $manga->mediaStat;
        $scores = $this->cleanScores($item->get('scores') ?? []);
        $scoreAverage = $this->convertScoreAverage($item->get('scoreAverage') ?? 0.0);
        $scoreCount = $item->get('scoreCount') ?? 0;

//        dd([
//            'scores' => $scores,
//            'scoreCount' => $scoreCount,
//            'scoreAverage' => $scoreAverage
//        ]);

        if (empty($mediaStat)) {
            logger()->channel('stderr')->info('🖨 [MAL_ID:MANGA:' . $malID . '] Creating stats');
            MediaStat::withoutGlobalScopes()
                ->create([
                    'model_type'        => $manga->getMorphClass(),
                    'model_id'          => $manga->id,
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
            logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done creating stats');
        } else if ($mediaStat->rating_average <= 0) {
            logger()->channel('stderr')->info('🛠 [MAL_ID:MANGA:' . $malID . '] Updating stats attributes');

            $mediaStat->update([
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

            logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done updating stats attributes');
        }

        logger()->channel('stderr')->info('✅️ [MAL_ID:MANGA:' . $malID . '] Done processing stats');
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
     * Converts scale-10 rating to scale-5.
     *
     * @param float $scoreAverage
     * @return float
     */
    private function convertScoreAverage(float $scoreAverage): float
    {
        if ($scoreAverage === 0.0) {
            return 0.0;
        }

        return $scoreAverage / 10 * 5;
    }
}
