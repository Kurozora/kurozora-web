<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Episode;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OEmbedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        switch ($this->resource::class) {
            case Episode::class:
                /** @var Episode $episode */
                $episode = $this->resource;
                return [
                    'type' => 'video',
                    'version' => '1.0',
                    'cache_age' => 3600,
                    'provider_name' => config('app.name'),
                    'provider_url' => config('app.url'),
                    'title' => $episode->title,
                    'author_name' => $episode->anime->title,
                    'author_url' => route('anime.details', $episode->anime),
                    'thumbnail_url' => $episode->getFirstMediaFullUrl(MediaCollection::Banner()),
                    'thumbnail_width' => 853,
                    'thumbnail_height' => 480,
                    'width' => 853,
                    'height' => 480,
                    'html' => str(view('components.embeds.episode', [
                        'url' => route('embed.episodes', $episode),
                        'title' => $episode->title,
                    ]))->trim()
                ];
            case Song::class:
                /** @var Song $song */
                $song = $this->resource;
                return [
                    'type' => 'rich',
                    'version' => '1.0',
                    'cache_age' => 3600,
                    'provider_name' => config('app.name'),
                    'provider_url' => config('app.url'),
                    'title' => $song->title,
                    'width' => 492,
                    'height' => 164,
                    'html' => str(view('components.embeds.song', [
                        'url' => route('embed.songs', $song),
                        'title' => $song->title,
                    ]))->trim()
                ];
            default:
                break;
        }

        return [];
    }
}
