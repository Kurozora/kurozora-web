<?php

namespace App\Events;

use App\Models\Character;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CharacterViewed
{
    use Dispatchable, SerializesModels;

    /**
     * @var Character $character
     */
    public Character $character;

    /**
     * Create a new event instance.
     *
     * @param Character $character
     */
    public function __construct(Character $character)
    {
        $this->character = $character;
    }
}
