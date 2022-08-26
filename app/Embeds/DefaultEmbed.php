<?php

namespace App\Embeds;

class DefaultEmbed extends Embed
{
    /**
     * Render and return the embed.
     *
     * @param array $data
     * @return string
     */
    public function getEmbed(array $data = []): string
    {
        return view('embeds.default', [
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
        return $this->resource->code;
    }
}
