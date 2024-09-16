<?php

namespace App\Events;

use App\Models\Anime;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BareBonesProducerAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The MAL id of the anime.
     *
     * @var string $malID
     */
    public string $malID;

    /**
     * Create a new event instance.
     *
     * @param Anime $anime
     */
    public function __construct(Anime $anime)
    {
        $this->malID = $anime->mal_id;
    }
}
