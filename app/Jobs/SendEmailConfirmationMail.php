<?php

namespace App\Jobs;

use App\Helpers\KuroMail;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendEmailConfirmationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        $this->sendEmailConfirmation();
    }

    /**
     * Sends an email confirmation mail to the user
     *
     * @return bool
     * @throws \Throwable
     */
    protected function sendEmailConfirmation() {
        // Create email data
        $emailData = [
            'title'             => 'Your Kurozora account registration',
            'username'          => $this->user->username,
            'confirmation_url'  => url('/confirmation/' . $this->user->email_confirmation_id)
        ];

        // Send email
        (new KuroMail())
            ->setTo($this->user->email)
            ->setSubject($emailData['title'])
            ->setContent(
                view('email.confirmation_email', $emailData)->render()
            )
            ->send();

        return true;
    }
}
