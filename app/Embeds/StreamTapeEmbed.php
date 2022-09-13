<?php

namespace App\Embeds;

final class StreamTapeEmbed extends Embed
{
    /**
     * Render and return the embed.
     *
     * @param array $data
     * @return string
     */
    public function getEmbed(array $data = []): string
    {
        return view('embeds.stream-tape', [
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
        return 'https://streamtape.com/e/' . $this->resource->code . '?autostart=true&color=255,147,0';
    }
}
