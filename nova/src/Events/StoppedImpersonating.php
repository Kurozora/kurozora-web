<?php

namespace Laravel\Nova\Events;

class StoppedImpersonating
{
    /**
     * The impersonator user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $impersonator;

    /**
     * The impersonated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $impersonated;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $impersonator
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $impersonated
     * @return void
     */
    public function __construct($impersonator, $impersonated)
    {
        $this->impersonator = $impersonator;
        $this->impersonated = $impersonated;
    }
}
