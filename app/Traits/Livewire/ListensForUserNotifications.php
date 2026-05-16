<?php

namespace App\Traits\Livewire;

trait ListensForUserNotifications
{
    /**
     * Builds Echo private-channel listener entries for the three notification lifecycle events.
     *
     * Pass a single method name to route every event to the same handler,
     * or a `[lifecycle => method]` map (with `created`, `read`, `deleted` keys)
     * to bind each event individually.
     *
     * @param string|array<string, string> $handler
     *
     * @return array<string, string>
     */
    protected function userNotificationListeners(string|array $handler): array
    {
        if (!auth()->check()) {
            return [];
        }

        $channel = 'echo-private:users.' . auth()->id();
        $events = ['created', 'read', 'deleted'];
        $map = is_string($handler) ? array_fill_keys($events, $handler) : $handler;

        $listeners = [];

        foreach ($map as $event => $method) {
            $listeners[$channel . ',.notification.' . $event] = $method;
        }

        return $listeners;
    }
}
