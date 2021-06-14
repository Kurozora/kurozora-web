<?php

namespace App\Events;

use App\Enums\MALImportBehavior;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MALImportFinished
{
    use Dispatchable, SerializesModels;

    /**
     * The user whose MAL import has finished.
     *
     * @var User
     */
    public User $user;

    /**
     * The results of the import.
     *
     * @var array
     */
    public array $results;

    /**
     * The behavior of the import.
     *
     * @var MALImportBehavior
     */
    public MALImportBehavior $behavior;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param array $results
     * @param MALImportBehavior $behavior
     */
    public function __construct(User $user, array $results, MALImportBehavior $behavior)
    {
        $this->user = $user;
        $this->results = $results;
        $this->behavior = $behavior;
    }
}
