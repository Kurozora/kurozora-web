<?php

namespace App\Traits\Livewire;

trait PresentsSubscriptionSheet
{
    /**
     * Present the subscription sheet with the given title and message.
     *
     * @param string      $title
     * @param null|string $message
     * @param bool        $tipJarEnabled
     *
     * @return void
     */
    protected function presentSubscriptionSheet(string $title, ?string $message, bool $tipJarEnabled = false): void
    {
        $this->dispatch(
            event: 'present-subscription-sheet',
            title: $title,
            message: $message,
            tipJarEnabled: $tipJarEnabled
        );
    }
}
