<?php

namespace App\Embeds;

final class FileMoonEmbed extends Embed
{
    /**
     * Render and return the embed.
     *
     * @param array $data
     * @return string
     */
    public function getEmbed(array $data = []): string
    {
        return view('embeds.file-moon', [
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
        return 'https://filemoon.sx/e/' . $this->resource->code . '?autostart=true&color=255,147,0';
    }
}
