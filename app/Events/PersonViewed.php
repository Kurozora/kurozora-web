<?php

namespace App\Events;

use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonViewed
{
    use Dispatchable, SerializesModels;

    /**
     * @var Person $person
     */
    public Person $person;

    /**
     * Create a new event instance.
     *
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        $this->person = $person;
    }
}
