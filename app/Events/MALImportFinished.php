<?php

namespace App\Events;

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
     * @var string
     */
    public string $behavior;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param array $results
     * @param string $behavior
     */
    public function __construct(User $user, array $results, string $behavior)
    {
        $this->user = $user;
        $this->results = $results;
        $this->behavior = $behavior;
    }
}
