<?php

namespace App\Jobs;

use App\Events\UserStateChanged;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Publishes a user's state change to their devices.
 */
class PublishUserStateChange implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job stays unique.
     *
     * @var int $uniqueFor
     */
    public int $uniqueFor = 60;

    /**
     * The id of the user whose state changed.
     *
     * @var int $userID
     */
    protected int $userID;

    /**
     * Create a new job instance.
     *
     * @param int $userID
     */
    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    /**
     * The unique id of the job.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return (string) $this->userID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $user = User::find($this->userID);

        if ($user === null) {
            return;
        }

        UserStateChanged::dispatch($user->getKey(), (int) $user->state_version);
    }
}
