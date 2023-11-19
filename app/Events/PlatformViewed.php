<?php

namespace App\Events;

use App\Models\Platform;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlatformViewed
{
    use Dispatchable, SerializesModels;

    /**
     * @var Platform $platform
     */
    public Platform $platform;

    /**
     * Create a new event instance.
     *
     * @param Platform $platform
     */
    public function __construct(Platform $platform)
    {
        $this->platform = $platform;
    }
}
