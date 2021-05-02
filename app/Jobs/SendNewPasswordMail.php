<?php

namespace App\Jobs;

use App\Mail\SendNewPassword;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewPasswordMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of tries.
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * The user to whom the new password will be sent.
     *
     * @var User
     */
    protected User $user;

    /**
     * The new password of the user.
     *
     * @var string
     */
    protected string $newPassword;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $newPassword
     */
    public function __construct(User $user, string $newPassword)
    {
        $this->user = $user;
        $this->newPassword = $newPassword;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Send the mail
        Mail::to($this->user->email)
            ->send(new SendNewPassword($this->user, $this->newPassword));
    }
}
