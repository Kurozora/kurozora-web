<?php

namespace App\Events;

use App\Models\Character;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BareBonesCharacterAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The MAL id of the character.
     *
     * @var string $malID
     */
    public string $malID;

    /**
     * Create a new event instance.
     *
     * @param Character $character
     */
    public function __construct(Character $character)
    {
        $this->malID = $character->mal_id;
    }
}
