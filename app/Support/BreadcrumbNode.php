<?php

namespace App\Support;

final class BreadcrumbNode
{
    /**
     * The visible label.
     *
     * @var string $label
     */
    public readonly string $label;

    /**
     * The destination URL.
     *
     * @var string $url
     */
    public readonly string $url;

    /**
     * The parent node, if any.
     *
     * @var ?BreadcrumbNode $parent
     */
    public readonly ?BreadcrumbNode $parent;

    /**
     * Build a node.
     *
     * @param string $label
     * @param string $url
     * @param ?BreadcrumbNode $parent
     */
    public function __construct(string $label, string $url, ?BreadcrumbNode $parent = null)
    {
        $this->label = $label;
        $this->url = $url;
        $this->parent = $parent;
    }

    /**
     * The root node representing the homepage.
     *
     * @return self
     */
    public static function home(): self
    {
        return new self(config('app.name'), route('home'));
    }
}
