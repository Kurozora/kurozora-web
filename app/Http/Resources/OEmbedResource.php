<?php

namespace App\Http\Resources;

use App\Models\Episode;
use App\Models\Song;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class OEmbedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
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
                    'author_name' => $episode->season->anime->title,
                    'author_url' => route('anime.details', $episode->season->anime),
                    'thumbnail_url' => $episode->banner_image_url,
                    'thumbnail_width' => 1920,
                    'thumbnail_height' => 1080,
                    'width' => 1920,
                    'height' => 1080,
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
                    'width' => 456,
                    'height' => 152,
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
