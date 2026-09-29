<?php

namespace App\Traits\Spider;

use Exception;
use RoachPHP\Http\Response;

trait ParsesExternalLinks
{
    /**
     * Parse the external links listed on an entity's page.
     *
     * @param Response $response
     * @param string   $selector
     *
     * @return string[]
     */
    protected function cleanExternalLinks(Response $response, string $selector = 'div.external_links a[href]'): array
    {
        try {
            $links = $response->filter($selector)
                ->extract(['href']);
        } catch (Exception $exception) {
            return [];
        }

        return collect($links)
            ->map(fn ($link) => trim((string) $link))
            ->filter(fn (string $link) => str_starts_with($link, 'http'))
            ->reject(fn (string $link) => str_contains($link, 'myanimelist.net'))
            ->unique()
            ->values()
            ->all();
    }
}
