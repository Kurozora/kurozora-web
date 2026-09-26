<?php

namespace App\Processors\MAL;

use App\Enums\VideoSource;
use App\Enums\VideoType;
use App\Models\Anime;
use App\Models\Language;
use App\Models\Video;
use App\Spiders\MAL\Models\VideoItem;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;

final class VideoProcessor extends CustomItemProcessor
{
    /**
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            VideoItem::class
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $malID = $item->get('id');
        $videos = $item->get('videos') ?? [];

        if (empty($videos)) {
            return $item;
        }

        $anime = Anime::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);

        if (empty($anime)) {
            logger()->channel('stderr')->error('❌ [MAL_ID:ANIME:' . $malID . '] Missing anime; skipping videos.');
            return $item;
        }

        // Promotional videos carry no per-video language on MAL; default to Japanese.
        $language = Language::firstWhere('code', '=', 'ja');

        if (empty($language)) {
            logger()->channel('stderr')->error('❌ [MAL_ID:ANIME:' . $malID . '] Missing Japanese language; skipping videos.');
            return $item;
        }

        logger()->channel('stderr')->info('🎬 [MAL_ID:ANIME:' . $malID . '] Processing videos');

        foreach ($videos as $video) {
            Video::firstOrCreate([
                'videoable_type' => $anime->getMorphClass(),
                'videoable_id' => $anime->id,
                'code' => $video['code'],
            ], [
                'source' => VideoSource::YouTube,
                'language_id' => $language->id,
                'type' => $this->getVideoType($video['title'] ?? ''),
                'is_sub' => false,
                'is_dub' => false,
            ]);
        }

        logger()->channel('stderr')->info('✅️ [MAL_ID:ANIME:' . $malID . '] Done processing videos');
        return $item;
    }

    /**
     * Map MAL's video title to a video type.
     *
     * @param string $title
     *
     * @return int
     */
    private function getVideoType(string $title): int
    {
        return match (true) {
            str($title)->contains('CM') => VideoType::CommercialMessage,
            str($title)->contains('Teaser') => VideoType::Teaser,
            str($title)->contains('Trailer') => VideoType::Trailer,
            default => VideoType::PromotionalVideo,
        };
    }
}
