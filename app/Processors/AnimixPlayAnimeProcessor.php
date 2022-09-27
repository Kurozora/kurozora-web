<?php

namespace App\Processors;

use App\Enums\VideoSource;
use App\Enums\VideoType;
use App\Models\Anime;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Support\Configurable;

class AnimixPlayAnimeProcessor implements ItemProcessorInterface
{
    use Configurable;

    /**
     * The current item.
     *
     * @var ItemInterface|null
     */
    private ?ItemInterface $item = null;

    /**
     * The available attributes.
     *
     * @var string[]
     */
    private array $attributes = [
        'Synonyms',
        'Japanese',
        'English',
        'German',
        'Spanish',
        'French',
        'Type',
        'Episodes',
        'Status',
        'Aired',
        'Premiered',
        'Broadcast',
        'Producers',
        'Licensors',
        'Studios',
        'Source',
        'Genres',
        'Themes',
        'Demographic',
        'Duration',
        'Rating',
        'Score',
        'Ranked',
        'Popularity',
        'Members',
        'Favorites',
    ];

    public function processItem(ItemInterface $item): ItemInterface
    {
        $slug = $this->getSlug($item->get('uri'));
        $episodeNumber = $this->getEpisodeNumber($item->get('uri'));
        $this->item = $item;

        logger()->channel('stderr')->info('🔄 Processing ' . $slug . ' episode ' . $episodeNumber);

        $anime = Anime::withoutGlobalScopes()
            ->firstWhere('animix_id', '=', $slug);
        $videoUrl = $this->getCleanVideoUrl($item->get('video_url'));

//        dd([
//            'slug' => $slug,
//            '$episode_number' => $episodeNumber,
//            'video_url' => $videoUrl,
//        ]);

        if (empty($anime)) {
            logger()->channel('stderr')->info('⚠️ Anime with `' . $slug . '` slug not found.');
        } else {
            $episode = $anime->episodes()
                ->withoutGlobalScopes()
                ->firstWhere('number_total', '=', $episodeNumber);
            $video = $episode->videos()
                ->firstWhere([
                    ['language_id', '=', 73], // Japanese
                    ['source', '=', VideoSource::Default],
                    ['is_sub', '=', true]
                ]);

            if (empty($video)) {
                logger()->channel('stderr')->info('🖨️ Creating ' . $slug . ' episode ' . $episodeNumber . ' video');
                $video = $episode->videos()->create([
                    'language_id' => 73, // Japanese
                    'source' => VideoSource::Default,
                    'type' => VideoType::Default,
                    'is_sub' => true,
                    'is_dub' => false,
                    'code' => $videoUrl,
                ]);
                logger()->channel('stderr')->info('✅️ Done creating `' . $slug . '` episode `' . $episodeNumber . '` video');
            } else {
                logger()->channel('stderr')->info('🛠️Updating video url');
                $video->update([
                    'code' => $videoUrl,
                ]);
                logger()->channel('stderr')->info('✅️ Done updating video url');
            }
        }

        logger()->channel('stderr')->info('✅️ Done processing ' . $slug);
        return $item;
    }

    /**
     * Returns the slug of the anime the episode belongs to.
     *
     * @param string $uri
     * @return ?string
     */
    private function getSlug(string $uri): ?string
    {
        $values = explode('/', $uri);
        $valuesCount = count($values);
        $cleanSlug = $valuesCount >= 3 ? $values[2] : null;
        return empty($cleanSlug) ? null : $cleanSlug;
    }

    /**
     * Returns the episode number.
     *
     * @param string $uri
     * @return ?string
     */
    private function getEpisodeNumber(string $uri): ?string
    {
        $values = explode('/', $uri);
        $valuesCount = count($values);
        $episode = '1';

        if ($valuesCount >= 4) {
            $episode = str_replace('ep', '', $values[3]);
        }

        return $episode;
    }

    /**
     * Returns the actual url of the video.
     *
     * @param string $url
     * @return ?string
     */
    private function getCleanVideoUrl(string $url): ?string
    {
        $url = parse_url($url);
        $fragment = explode('#', $url['fragment']);
        $cleanViewUrl = base64_decode($fragment[0]);
        return empty($cleanViewUrl) ? null : $cleanViewUrl;
    }
}
