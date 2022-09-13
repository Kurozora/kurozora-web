<?php

namespace App\Embeds;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Video;

abstract class Embed
{
    /**
     * The resource of the embed.
     *
     * @var Video
     */
    protected Video $resource;

    /**
     * List of artwork sizes.
     *
     * @var array|string[]
     */
    protected array $sizes = [
        '96x96',
        '128x128',
        '192x192',
        '256x256',
        '384x384',
        '512x512',
    ];

    /**
     * Create a new Embed instance.
     *
     * @param Video $video
     */
    public function __construct(Video $video)
    {
        $this->resource = $video;
    }

    /**
     * Render and return the embed.
     *
     * @param array $data
     * @return string
     */
    abstract public function getEmbed(array $data = []): string;

    /**
     * Get the embed link.
     *
     * @return string
     */
    abstract public function getUrl(): string;

    /**
     * Get the embed thumbnail.
     *
     * @return string
     */
    public function getPoster(): string
    {
        return match($this->resource->videoable_type) {
            Anime::class, Episode::class => $this->resource->videoable->banner_image_url ?? '',
            default => ''
        };
    }

    /**
     * Get the embed title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return match($this->resource->videoable_type) {
            Anime::class, Episode::class => $this->resource->videoable->title,
            default => __('Anime on Kurozora')
        };
    }

    /**
     * Get the embed artist.
     *
     * @return string
     */
    public function getArtist(): string
    {
        return config('app.name');
    }

    /**
     * Get the embed album.
     *
     * @return string
     */
    public function getAlbum(): string
    {
        return match($this->resource->videoable_type) {
            Anime::class => config('app.name'),
            Episode::class => $this->resource->videoable->season->anime->title,
            default => class_basename($this->resource->videoable_type)
        };
    }

    /**
     * Get the embed artworks.
     *
     * @return array
     */
    public function getArtworks(): array
    {
        switch($this->resource->videoable_type) {
            case Anime::class:
                /** @var Anime $anime */
                $anime = $this->resource->videoable;
                $artworks = [];

                foreach ($this->sizes as $size) {
                    $artworks[] = [
                        'src' => $anime->poster_image_url,
                        'sizes' => $size,
                        'type' => 'image/webp'
                    ];
                }

                return $artworks;
            case Episode::class:
                /** @var Anime $anime */
                $anime = $this->resource->videoable->season->anime;
                $artworks = [];

                foreach ($this->sizes as $size) {
                    $artworks[] = [
                        'src' => $anime->poster_image_url,
                        'sizes' => $size,
                        'type' => 'image/webp'
                    ];
                }

                return $artworks;
            default:
                return [];
        }
    }
}
