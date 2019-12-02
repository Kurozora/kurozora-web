<?php

namespace App\Events;

use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class MALImportFinished
{
    use Dispatchable, SerializesModels;

    public $user;
    public $results;
    public $behavior;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param $results
     * @param $behavior
     */
    public function __construct(User $user, $results, $behavior)
    {
        $this->user = $user;
        $this->results = $results;
        $this->behavior = $behavior;
    }
}
