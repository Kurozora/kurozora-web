<?php

namespace App\Models;

use App\Enums\LinkPreviewType;
use App\Services\LinkPreviewService;
use Throwable;

class LinkPreview extends KModel
{
    // Table name
    const string TABLE_NAME = 'link_previews';
    protected $table = self::TABLE_NAME;

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'type' => LinkPreviewType::class,
            'fetched_at' => 'datetime',
        ];
    }

    /**
     * Render the link preview.
     *
     * @return string The rendered view for the link preview.
     *
     * @throws Throwable
     */
    public function render(): string
    {
        if ($this->handler) {
            $handler = app(LinkPreviewService::class)->getHandlerByKey($this->handler);

            if ($handler && method_exists($handler, 'render')) {
                return $handler->render($this)
                    ->render();
            }
        }

        return $this->renderDefault();
    }

    /**
     * Render the default link preview.
     *
     * @return string The rendered view for the default link preview.
     *
     * @throws Throwable
     */
    protected function renderDefault(): string
    {
        return view('link-previews.default', [
            'type' => $this->type,
            'url' => $this->url,
            'author' => $this->author,
            'title' => $this->title,
            'description' => $this->description,
            'media_url' => $this->media_url,
            'embed_html' => $this->embed_html,
        ])->render();
    }
}
