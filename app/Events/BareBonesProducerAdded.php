<?php

namespace App\Events;

use App\Models\Studio;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BareBonesProducerAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The MAL id of the studio.
     *
     * @var string $malID
     */
    public string $malID;

    /**
     * Create a new event instance.
     *
     * @param Studio $studio
     */
    public function __construct(Studio $studio)
    {
        $this->malID = $studio->mal_id;
    }
}
