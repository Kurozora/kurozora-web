<?php

namespace App\Handlers;

use App\Enums\LinkPreviewType;
use App\Models\LinkPreview;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use DOMDocument;
use DOMXPath;
use Http;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\View\View;

class TenorHandler extends LinkPreviewHandler
{
    /**
     * Determine if the URL can be handled.
     *
     * @param string $url The URL to check for handling.
     *
     * @return bool `true` if the URL can be handled, `false` otherwise.
     */
    public function canHandle(string $url): bool
    {
        return str_contains(parse_url($url, PHP_URL_HOST), 'tenor.com');
    }

    /**
     * Handle the given URL if necessary.
     *
     * @param string $url The URL to handle.
     *
     * @return null|LinkPreview The link preview data if the URL is handled, `null` otherwise.
     *
     * @throws ConnectionException
     */
    public function handle(string $url): ?LinkPreview
    {
        $response = Http::timeout(5)
            ->withUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->get($url);

        if (!$response->successful()) {
            return null;
        }

        $html = $response->body();

        return $this->parseMetaTags($html, $url);
    }

    /**
     * Parses the HTML to extract meta tags and return link preview data.
     *
     * @param string $html The HTML content of the page.
     * @param string $url  The URL of the page.
     *
     * @return LinkPreview The link preview data.
     */
    protected function parseMetaTags(string $html, string $url): LinkPreview
    {
        libxml_use_internal_errors(true); // Suppress malformed HTML warnings

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        // Helper to get content by property or name
        $getMetaContent = fn(array $selectors): ?string => collect($selectors)->map(function ($selector) use ($xpath) {
            [$attr, $value] = $selector;
            $query = '//meta[@' . $attr . "='" . $value . "']";
            $node = $xpath->query($query)?->item(0);
            return $node?->getAttribute('content');
        })->first(fn($val) => !empty($val));

        $author = $getMetaContent([
            ['property', 'og:site_name'],
            ['name', 'author'],
        ]);

        $title = $getMetaContent([
            ['property', 'og:title'],
            ['name', 'twitter:title'],
        ]) ?? $xpath->query('//title')?->item(0)?->nodeValue;

        $description = $getMetaContent([
            ['name', 'twitter:description'],
            ['name', 'description'],
            ['property', 'og:description'],
        ]);

        $mediaUrl = $getMetaContent([
            ['property', 'og:video'],
            ['name', 'twitter:player:stream'],
        ]);

        $metaType = $getMetaContent([
            ['property', 'og:type'],
        ]);

        try {
            $key = str($metaType ?? 'link')
                ->upper()
                ->split('/\./')
                ->first();
            $type = LinkPreviewType::fromKey($key)->value;
        } catch (InvalidEnumKeyException $e) {
            $type = LinkPreviewType::LINK;
        }

        return LinkPreview::make([
            'handler' => $this->key(),
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'author' => $author,
            'media_url' => $mediaUrl,
            'embed_html' => null,
            'provider' => parse_url($url, PHP_URL_HOST),
        ]);
    }

    /**
     * Render the link preview.
     *
     * @param LinkPreview $preview The link preview data to render.
     *
     * @return Application|Factory|View The rendered view for the link preview.
     */
    public function render(LinkPreview $preview):  Application|Factory|View
    {
        return view('link-previews.default', [
            'url' => $preview->url,
            'author' => $preview->author,
            'type' => $preview->type,
            'title' => $preview->title,
            'description' => $preview->description,
            'media_url' => $preview->media_url,
            'embed_html' => $preview->embed_html,
        ]);
    }
}
