<?php

namespace App\Jobs;

use App\Helpers\KuroMail;
use App\PasswordReset;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
     * @return void
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
        // Get data for the email
        $emailData = [
            'title' => 'Password reset',
            'username' => $this->user->username,
            'ip' => $this->passwordReset->ip,
            'reset_url' => url('/reset/' . $this->passwordReset->token)
        ];

        // Send the email
        (new KuroMail())
            ->setTo($this->user->email)
            ->setSubject('Reset your password')
            ->setContent(view('email.password_reset_notification', $emailData)->render())
            ->send();

        return true;
    }
}
