<?php

namespace App\Processors\AnimeFillerList;

use App\Models\Anime;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Support\Configurable;

class FillerProcessor implements ItemProcessorInterface
{
    use Configurable;

    /**
     * The current item.
     *
     * @var ItemInterface|null
     */
    private ?ItemInterface $item = null;

    public function processItem(ItemInterface $item): ItemInterface
    {
        $this->item = $item;
        $fillerID = $item->get('filler_id');
        $episodeNumber = $item->get('episode_number');
        $isFiller = $this->getIsFiller($item->get('filler_type'));

        logger()->channel('stderr')->info(($isFiller ? 'true: ' : 'false: ') . $item->get('filler_type') . ' episode: ' . $episodeNumber);
        logger()->channel('stderr')->info('🔄 [filler_id:' . $fillerID . '] Processing filler status');

        $anime = Anime::withoutGlobalScopes()
            ->firstWhere('filler_id', '=', $fillerID);

//        dd([
//            'filler_id' => $fillerID,
//            'image_url' => $imageUrl,
//        ]);

        if (empty($anime)) {
            logger()->channel('stderr')->warning('⚠️ [filler_id:' . $fillerID . '] Anime not found');
        } else {
            $episode = $anime->episodes()
                ->withoutGlobalScopes()
                ->firstWhere('number_total', '=', $episodeNumber);

            if (empty($episode)) {
                logger()->channel('stderr')->warning('⚠️ [filler_id:' . $fillerID . '] Episode `' . $episodeNumber . '` not found');
            } else {
                logger()->channel('stderr')->info('🛠️ [filler_id:' . $fillerID . '] Updating episode `' . $episodeNumber . '` filler status');
                $episode->update([
                    'is_filler' => $isFiller,
                ]);
                logger()->channel('stderr')->info('✅️ [filler_id:' . $fillerID . '] Done updating episode `' . $episodeNumber . '` filler status');
            }
        }

        logger()->channel('stderr')->info('✅️ [filler_id:' . $fillerID . '] Done processing `' . $episodeNumber . '` filler status');
        return $item;
    }

    /**
     * Determines whether the episode is a filler.
     *
     * @param string $fillerType
     * @return bool
     */
    private function getIsFiller(string $fillerType): bool
    {
        return str($fillerType)->lower()->contains('filler');
    }
}
