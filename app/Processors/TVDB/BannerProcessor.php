<?php

namespace App\Processors\TVDB;

use App\Helpers\ResmushIt;
use App\Models\Anime;
use Exception;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Support\Configurable;

class BannerProcessor implements ItemProcessorInterface
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
        $tvdbID = $item->get('tvdb_id');
        $imageURLs = $item->get('image_urls');

//        dd([
//            'tvdb_id' => $tvdbID,
//            'image_url' => $imageUrl,
//        ]);

        logger()->channel('stderr')->info('🔄 [tvdb_id:' . $tvdbID . '] Processing banner');

        $anime = Anime::withoutGlobalScopes()
            ->where('tvdb_id', '=', $tvdbID)
            ->whereHas('media', function ($query) {
                return $query->where('collection_name', '=', 'banner');
            }, '=', 0);
        $animeCount = $anime->count();

        if ($animeCount) {
            logger()->channel('stderr')->info('🖨️ [tvdb_id:' . $tvdbID . '] Creating banner');
            $randomKey = array_rand($imageURLs, $animeCount);

            $anime->each(function (Anime $anime, $key) use ($randomKey, $tvdbID, $imageURLs) {
                $imageURL = count($randomKey) >= $key ? $imageURLs[$randomKey[$key]] : $imageURLs[$randomKey];

                if ($response = ResmushIt::compress($imageURL)) {
                    try {
                        $extension = pathinfo($imageURL, PATHINFO_EXTENSION);
                        $anime->updateBannerImage($response, $anime->original_title, [], $extension);
                        logger()->channel('stderr')->info('✅️ [tvdb_id:' . $tvdbID . '] Done creating banner');
                    } catch (Exception $e) {
                        logger()->channel('stderr')->error('❌️ [tvdb_id:' . $tvdbID . '] ' . $e->getMessage());
                    }
                } else {
                    logger()->channel('stderr')->error('❌️ [tvdb_id:' . $tvdbID . '] Resmush failed.');
                }
            });
        } else {
            logger()->channel('stderr')->warning('⚠️ [tvdb_id:' . $tvdbID . '] Anime not found');
        }

        logger()->channel('stderr')->info('✅️ [tvdb_id:' . $tvdbID . '] Done processing');
        return $item;
    }
}
