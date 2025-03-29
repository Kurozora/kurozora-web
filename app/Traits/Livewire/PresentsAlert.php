<?php

namespace App\Traits\Livewire;

trait PresentsAlert
{
    /**
     * Present an alert with the given title and message.
     *
     * @param string      $title
     * @param null|string $message
     *
     * @return void
     */
    protected function presentAlert(string $title, ?string $message): void
    {
        $this->dispatch(
            event: 'present-alert',
            title: $title,
            message: $message
        );
    }
}
