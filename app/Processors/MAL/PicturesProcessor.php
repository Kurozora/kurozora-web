<?php

namespace App\Processors\MAL;

use App\Enums\MediaCollection;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Manga;
use App\Models\Person;
use App\Spiders\MAL\Models\PictureItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\CustomItemProcessor;
use Throwable;

final class PicturesProcessor extends CustomItemProcessor
{
    /**
     * @return array<int, class-string<ItemInterface>>
     */
    protected function getHandledItemClasses(): array
    {
        return [
            PictureItem::class
        ];
    }

    public function processItem(ItemInterface $item): ItemInterface
    {
        $type = $item->get('type');
        $malID = $item->get('id');
        $imageURLs = $item->get('imageURLs') ?? [];

        if (empty($imageURLs)) {
            return $item;
        }

        [$modelClass, $collection] = match ($type) {
            'anime' => [Anime::class, MediaCollection::Poster],
            'manga' => [Manga::class, MediaCollection::Poster],
            'character' => [Character::class, MediaCollection::Profile],
            'people' => [Person::class, MediaCollection::Profile],
            default => [null, null],
        };

        if (empty($modelClass)) {
            return $item;
        }

        $model = $modelClass::withoutGlobalScopes()
            ->firstWhere('mal_id', '=', $malID);

        if (empty($model)) {
            logger()->channel('stderr')->error('❌ [MAL_ID:' . strtoupper($type) . ':' . $malID . '] Missing model; skipping pictures.');
            return $item;
        }

        logger()->channel('stderr')->info('🖼 [MAL_ID:' . strtoupper($type) . ':' . $malID . '] Processing pictures');

        // Fingerprint existing images so the same picture is never stored twice
        $hashes = [];
        foreach ($model->getMedia($collection) as $media) {
            $hash = $media->getCustomProperty('hash') ?? $this->hashOf($media->getFullUrl());

            if ($hash === null) {
                continue;
            }

            if (!$media->hasCustomProperty('hash')) {
                $media->setCustomProperty('hash', $hash);
                $media->save();
            }

            $hashes[$hash] = true;
        }

        $name = $model->original_title ?? $model->full_name ?? $model->name ?? null;

        foreach ($imageURLs as $imageURL) {
            try {
                $response = Http::get($imageURL);
            } catch (Throwable $e) {
                continue;
            }

            if (!$response->successful()) {
                continue;
            }

            $contents = $response->body();
            $hash = md5($contents);

            if (isset($hashes[$hash])) {
                continue;
            }

            try {
                $model->addMediaFromString($contents)
                    ->usingName($name)
                    ->usingFileName(Str::uuid() . '.' . (pathinfo($imageURL, PATHINFO_EXTENSION) ?: 'jpg'))
                    ->withCustomProperties(['hash' => $hash])
                    ->toMediaCollection($collection);
            } catch (Throwable $e) {
                logger()->channel('stderr')->error('❌ [MAL_ID:' . strtoupper($type) . ':' . $malID . '] Failed adding picture: ' . $e->getMessage());
                continue;
            }

            $hashes[$hash] = true;
        }

        // Mark as scraped so backfills can skip it within their retention window.
        $model->touch();

        logger()->channel('stderr')->info('✅️ [MAL_ID:' . strtoupper($type) . ':' . $malID . '] Done processing pictures');
        return $item;
    }

    /**
     * Compute the content hash of the image at the given URL.
     *
     * @param string $url
     *
     * @return string|null
     */
    private function hashOf(string $url): ?string
    {
        try {
            $response = Http::get($url);
        } catch (Throwable $e) {
            return null;
        }

        return $response->successful() ? md5($response->body()) : null;
    }
}
