<?php

namespace App\Contracts;

use App\Models\LinkPreview;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

interface LinkPreviewHandlerInterface
{
    /**
     * Get the unique key for the handler.
     *
     * @return string The unique key for the handler.
     */
    public function key(): string;

    /**
     * Determine if the URL can be handled.
     *
     * @param string $url The URL to check for handling.
     *
     * @return bool `true` if the URL can be handled, `false` otherwise.
     */
    public function canHandle(string $url): bool;

    /**
     * Handle the given URL if necessary.
     *
     * @param string $url The URL to handle.
     *
     * @return null|LinkPreview The link preview data if the URL is handled, `null` otherwise.
     */
    public function handle(string $url): ?LinkPreview;

    /**
     * Render the link preview.
     *
     * @param LinkPreview $preview The link preview data to render.
     *
     * @return Application|Factory|View The rendered view for the link preview.
     */
    public function render(LinkPreview $preview): Application|Factory|View;
}
