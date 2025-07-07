<?php

namespace App\Services;

use App\Contracts\LinkPreviewHandlerInterface;
use App\Enums\LinkPreviewType;
use App\Models\LinkPreview;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LinkPreviewService
{
    /**
     * The array of URL handlers.
     *
     * @var array $handlers
     */
    protected array $handlers;

    /**
     * Create a new UrlHandleerService instance.
     */
    public function __construct()
    {
        $this->handlers = [
            // TODO: - Create handlers to add here
        ];
    }

    /**
     * Retrieves a handler by its key.
     *
     * @param string $key The key of the handler to retrieve.
     *
     * @return null|LinkPreviewHandlerInterface The handler if found, `null` otherwise.
     */
    public function getHandlerByKey(string $key): ?LinkPreviewHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->key() === $key) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * Resolves a URL to its link preview data.
     *
     * This method checks if the preview data is already cached in the database.
     * If not, it fetches the preview data from the URL using oEmbed, OpenGraph,
     * or meta tags, and stores it in the database.
     *
     * @param string $url The URL to resolve.
     *
     * @return LinkPreview The link preview data.
     *
     * @throws ConnectionException
     */
    public function resolve(string $url): LinkPreview
    {
        $url = $this->normalize($url);

        $preview = LinkPreview::where('url', $url)
            ->where('fetched_at', '>=', now()->subDays(7))
            ->first();

        if ($preview) {
            return $preview;
        }

        $data = $this->fetchPreview($url)
            ->toArray();

        return LinkPreview::updateOrCreate(
            [
                'url' => $url
            ],
            [
                ...$data,
                'fetched_at' => now()
            ]
        );
    }

    /**
     * Normalizes the URL by removing trailing slashes.
     *
     * @param string $url The URL to normalize.
     *
     * @return string The normalized URL.
     */
    protected function normalize(string $url): string
    {
        return rtrim($url, '/');
    }

    /**
     * Fetches the preview data for a given URL.
     *
     * @param string $url The URL to fetch the preview for.
     *
     * @return LinkPreview The link preview data.
     *
     * @throws ConnectionException
     */
    protected function fetchPreview(string $url): LinkPreview
    {
        $handledUrl = $this->tryHandle($url);

        if ($handledUrl !== null) {
            return $handledUrl;
        }

        // Priority: oEmbed > OpenGraph > meta tags
        $oembed = $this->tryOEmbed($url);
        if ($oembed) {
            return $oembed;
        }

        $response = Http::timeout(5)
            ->withUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->get($url);
        $html = $response->body();

        return $this->parseMetaTags($html, $url);
    }

    /**
     * Attempts to handle the given URL using registered handlers.
     *
     * @param string $url The URL to handle.
     *
     * @return null|LinkPreview The link preview data if the URL is handled, `null` otherwise.
     *
     * @throws ConnectionException
     */
    protected function tryHandle(string $url): ?LinkPreview
    {
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($url)) {
                $result = $handler->handle($url);

                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Attempts to fetch oEmbed data for the given URL.
     *
     * @param string $url The URL to fetch oEmbed data for.
     *
     * @return null|LinkPreview The link preview data if oEmbed is successful, `null` otherwise.
     *
     * @throws ConnectionException
     */
    protected function tryOEmbed(string $url): ?LinkPreview
    {
        $known = [
            'https://music.apple.com' => 'https://music.apple.com/api/oembed',
            'https://open.spotify.com' => 'https://open.spotify.com/oembed',
            'https://youtube.com' => 'https://youtube.com/oembed',
        ];

        foreach ($known as $match => $endpoint) {
            if (Str::startsWith($url, $match)) {
                $response = Http::get($endpoint, ['url' => $url]);

                if ($response->ok()) {
                    $json = $response->json();

                    return LinkPreview::make([
                        'type' => $json['type'] ?? LinkPreviewType::LINK,
                        'title' => $json['title'] ?? null,
                        'description' => $json['description'] ?? null,
                        'media_url' => $json['thumbnail_url'] ?? null,
                        'embed_html' => $json['html'] ?? null,
                        'provider' => $json['provider_name'] ?? parse_url($url, PHP_URL_HOST),
                    ]);
                }
            }
        }

        return null;
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
            ['property', 'og:description'],
            ['name', 'twitter:description'],
            ['name', 'description'],
        ]);

        $image = $getMetaContent([
            ['property', 'og:image'],
            ['name', 'twitter:image'],
        ]);

        $ogType = $getMetaContent([
            ['property', 'og:type'],
        ]);

        try {
            $key = str($ogType ?? 'link')->upper()->split('/\./')->first();
            $type = LinkPreviewType::fromKey($key)->value;
        } catch (InvalidEnumKeyException $e) {
            $type = LinkPreviewType::LINK;
        }

        return LinkPreview::make([
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'author' => $author,
            'media_url' => $image,
            'embed_html' => null,
            'provider' => parse_url($url, PHP_URL_HOST),
        ]);
    }
}
