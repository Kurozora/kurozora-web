<?php

namespace App\Handlers;

use App\Contracts\LinkPreviewHandlerInterface;

abstract class LinkPreviewHandler implements LinkPreviewHandlerInterface
{
    /**
     * Get the unique key for the handler.
     *
     * @return string The unique key for the handler.
     */
    public function key(): string
    {
        return static::class;
    }
}
