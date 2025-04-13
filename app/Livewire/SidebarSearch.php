<?php

namespace App\Livewire;

class SidebarSearch extends BaseSearch
{
    /**
     * Whether search is enabled.
     *
     * @var bool $isSearchEnabled
     */
    public bool $isSearchEnabled = false;

    /**
     * The view to render.
     *
     * @var string $view
     */
    protected string $view = 'livewire.sidebar-search';
}
