<?php

namespace App\Jobs;

use App\Mail\ResetPassword;
use App\Mail\SendNewPassword;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendNewPasswordMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $user;
    protected $newPassword;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $newPassword
     */
    public function __construct(User $user, $newPassword)
    {
        $this->user = $user;
        $this->newPassword = $newPassword;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Send the mail
        Mail::to($this->user->email)
            ->send(new SendNewPassword($this->user, $this->newPassword));
    }
}
