<?php

namespace App\Jobs;

use App\Mail\AdminExceptionNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendAdminExceptionMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $exceptionDump;
    protected $exceptionClass;
    protected $exceptionLine;

    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param Exception $exception
     */
    public function __construct(Exception $exception)
    {
        $this->exceptionDump = $exception->getMessage();
        $this->exceptionClass = get_class($exception);
        $this->exceptionLine = $exception->getLine();
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
        // Send an email to admin(s)
        $adminEmail = config('app.admin_email');

        if($adminEmail === null)
            return false;

        // Send the mail
        Mail::to($adminEmail)
            ->send(new AdminExceptionNotification($this->exceptionDump, $this->exceptionClass, $this->exceptionLine));

        return true;
    }
}
