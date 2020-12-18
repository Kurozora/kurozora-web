<?php

namespace App\Jobs;

use App\Mail\ResetPassword;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $passwordReset;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param PasswordReset $passwordReset
     */
    public function __construct(User $user, PasswordReset $passwordReset)
    {
        $this->user = $user;
        $this->passwordReset = $passwordReset;
    }

    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $this->send();
    }

    /**
     * Sends the mail
     *
     * @return bool
     * @throws \Throwable
     */
    protected function send() {
        // Send the mail
        Mail::to($this->user->email)
            ->send(new ResetPassword($this->user, $this->passwordReset));

        return true;
    }
}
