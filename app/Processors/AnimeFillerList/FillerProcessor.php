<?php

namespace App\Processors\AnimeFillerList;

use App\Enums\EpisodeFillerKind;
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
        $fillerKind = EpisodeFillerKind::fromFillerType($item->get('filler_type'));

        logger()->channel('stderr')->info($fillerKind->description . ': ' . $item->get('filler_type') . ' episode: ' . $episodeNumber);
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
                    'filler_kind' => $fillerKind,
                ]);
                logger()->channel('stderr')->info('✅️ [filler_id:' . $fillerID . '] Done updating episode `' . $episodeNumber . '` filler status');
            }
        }

        logger()->channel('stderr')->info('✅️ [filler_id:' . $fillerID . '] Done processing `' . $episodeNumber . '` filler status');
        return $item;
    }
}
