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
        $imageUrl = $item->get('image_url');

        logger()->channel('stderr')->info('🔄 [tvdb_id:' . $tvdbID . '] Processing banner');

        $anime = Anime::withoutGlobalScopes()
            ->firstWhere('tvdb_id', '=', $tvdbID);

//        dd([
//            'tvdb_id' => $tvdbID,
//            'image_url' => $imageUrl,
//        ]);

        if (empty($anime)) {
            logger()->channel('stderr')->warning('⚠️ [tvdb_id:' . $tvdbID . '] Anime not found');
        } else {
            logger()->channel('stderr')->info('🖨️ [tvdb_id:' . $tvdbID . '] Creating banner');
            if ($response = ResmushIt::compress($imageUrl)) {
                try {
                    $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
                    $anime->updateBannerImage($response, $anime->original_title, [], $extension);
                    logger()->channel('stderr')->info('✅️ [tvdb_id:' . $tvdbID . '] Done creating banner');
                } catch (Exception $e) {
                    logger()->channel('stderr')->error('❌️ [tvdb_id:' . $tvdbID . '] ' . $e->getMessage());
                }
            } else {
                logger()->channel('stderr')->error('❌️ [tvdb_id:' . $tvdbID . '] Resmush failed.');
            }
        }

        logger()->channel('stderr')->info('✅️ [tvdb_id:' . $tvdbID . '] Done processing');
        return $item;
    }
}
