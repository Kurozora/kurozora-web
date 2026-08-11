<?php

namespace App\Traits\Livewire;

trait WithKotodamaFlash
{
    /**
     * The flash message.
     *
     * @var string|null
     */
    public ?string $flash = null;

    /**
     * Clears the flash message.
     *
     * @return void
     */
    public function dismissFlash(): void
    {
        $this->flash = null;
    }
}
