<?php

namespace App\Events;

use App\Models\Person;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BareBonesPersonAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The MAL id of the person.
     *
     * @var string $malID
     */
    public string $malID;

    /**
     * Create a new event instance.
     *
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        $this->malID = $person->mal_id;
    }
}
