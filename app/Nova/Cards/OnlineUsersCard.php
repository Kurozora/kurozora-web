<?php

namespace App\Nova\Cards;

use Laravel\Nova\Card;

class OnlineUsersCard extends Card
{
    /**
     * The width of the card.
     *
     * @var string
     */
    public $width = '1/3';

    /**
     * The Vue component name registered by the online-users-card package.
     *
     * @var string
     */
    public $component = 'online-users-card';

    /**
     * Create a new card instance, seeded with the runtime config the Vue component needs.
     */
    public function __construct()
    {
        parent::__construct();

        $this->withMeta([
            'seed_url' => route('admin.presence.seed'),
            'channel' => 'admin-presence-stats',
            'reverb' => [
                'key' => $this->reverbValue('VITE_REVERB_APP_KEY', config('broadcasting.connections.reverb.key')),
                'host' => $this->reverbValue('VITE_REVERB_HOST', config('broadcasting.connections.reverb.options.host')),
                'port' => (int) $this->reverbValue('VITE_REVERB_PORT', config('broadcasting.connections.reverb.options.port', 443)),
                'scheme' => $this->reverbValue('VITE_REVERB_SCHEME', config('broadcasting.connections.reverb.options.scheme', 'https')),
            ],
        ]);
    }

    /**
     * Returns the client-facing Reverb setting, preferring the VITE_ variant when present.
     *
     * @param string                $key
     * @param string|int|null       $fallback
     *
     * @return string|int|null
     */
    private function reverbValue(string $key, string|int|null $fallback): string|int|null
    {
        $value = env($key);

        return $value !== null && $value !== '' ? $value : $fallback;
    }
}
