<?php

namespace App\Embeds;

final class FembedEmbed extends Embed
{
    /**
     * Render and return the embed.
     *
     * @param array $data
     * @return string
     */
    public function getEmbed(array $data = []): string
    {
        return view('embeds.fembed', [
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
        return 'https://www.fembed.com/v/' . $this->resource->code . '?autostart=true';
    }
}
