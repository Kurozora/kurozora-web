<?php

namespace App\Handlers;

use App\Enums\LinkPreviewType;
use App\Models\LinkPreview;
use Http;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\View\View;

class TwitterHandler extends LinkPreviewHandler
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
        return str_contains(parse_url($url, PHP_URL_HOST), 'twitter.com') ||
            str_contains(parse_url($url, PHP_URL_HOST), 'x.com');
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
        $apiUrl = $this->transform($url);

        $response = Http::timeout(5)
            ->withUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
            ->get($apiUrl);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();

        if (!isset($data['tweet'])) {
            return null;
        }

        $tweet = $data['tweet'];
        $screenName = $tweet['author']['screen_name'] ?? null;
        $name = $tweet['author']['name'] ?? null;
        $title = $screenName ? "$name (@$screenName)" : ($name ?? null);

        return LinkPreview::make([
            'handler' => $this->key(),
            'type' => LinkPreviewType::LINK,
            'title' => $title,
            'author' => $title,
            'description' => $tweet['text'] ?? null,
            'media_url' => $tweet['author']['avatar_url'] ?? null,
            'embed_html' => null,
            'provider' => parse_url($url, PHP_URL_HOST),
        ]);
    }

    private function transform(string $url): string
    {
        return preg_replace(
            '/https?:\/\/(www\.)?(twitter|x)\.com/',
            'https://api.fxtwitter.com',
            $url
        );
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
        return view('link-previews.twitter', [
            'url' => $preview->url,
            'author' => $preview->author,
            'title' => $preview->title,
            'description' => $preview->description,
            'media_url' => $preview->media_url
        ]);
    }
}
