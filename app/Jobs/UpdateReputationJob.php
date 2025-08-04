<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ReputationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class UpdateReputationJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * The object containing the user data.
     *
     * @var User
     */
    protected User $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $score = app(ReputationService::class)
            ->calculate($this->user);

        $this->user->updateQuietly([
            'reputation_count' => (int) round($score)
        ]);
    }
}
