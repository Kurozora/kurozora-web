<?php

namespace App\Embeds;

class Mp4UploadEmbed extends Embed
{
    /**
     * Render and return the embed.
     *
     * @param array $data
     * @return string
     */
    public function getEmbed(array $data = []): string
    {
        return view('embeds.mp4upload', [
            'url' => $this->getUrl()
        ], $data)->render();
    }

    /**
     * Get the embed link.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return 'https://www.mp4upload.com/embed-' . $this->resource->code . '.html?autostart=true&color=255,147,0';
    }
}
