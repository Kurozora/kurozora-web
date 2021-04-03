<?php

namespace Laravel\Nova\Testing\Browser\Pages;

class UserIndex extends Index
{
    /**
     * Create a new page instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('users');
    }
}
