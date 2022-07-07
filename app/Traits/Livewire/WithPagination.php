<?php

namespace App\Traits\Livewire;

trait WithPagination
{
    use \Livewire\WithPagination;

    /**
     * The number of results per page.
     *
     * @var int $perPage
     */
    public int $perPage = 25;
}
