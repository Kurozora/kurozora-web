<?php

namespace App\Events;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnimeImportFinished
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
     * The service of the import.
     *
     * @var ImportService
     */
    public ImportService $service;

    /**
     * The behavior of the import.
     *
     * @var ImportBehavior
     */
    public ImportBehavior $behavior;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param array $results
     * @param ImportService $service
     * @param ImportBehavior $behavior
     */
    public function __construct(User $user, array $results, ImportService $service, ImportBehavior $behavior)
    {
        $this->user = $user;
        $this->results = $results;
        $this->service = $service;
        $this->behavior = $behavior;
    }
}
