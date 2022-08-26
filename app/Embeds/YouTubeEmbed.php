<?php

namespace App\Embeds;

final class YouTubeEmbed extends Embed
{
    /**
     * Render and return the embed.
     *
     * @param array $data
     * @return string
     */
    public function getEmbed(array $data = []): string
    {
        return view('embeds.youtube', [
            'url' => $this->getUrl(),
            'poster' => $this->getPoster(),
            'title' => $this->getTitle(),
            'artist' => $this->getArtist(),
            'album' => $this->getAlbum(),
            'artworks' => $this->getArtworks(),
        ], $data)->render();
    }

    /**
     * Get the embed link.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return 'https://www.youtube-nocookie.com/embed/' . $this->resource->code . '?autoplay=1&iv_load_policy=3&rel=0&start=0&origin=' . config('app.url') . '&modestbranding=1&playsinline=1';
    }
}
