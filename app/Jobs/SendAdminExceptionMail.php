<?php

namespace App\Jobs;

use App\Helpers\KuroMail;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Config;

class SendAdminExceptionMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $exceptionDump;
    protected $exceptionClass;

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

        // Get date
        $curDate = Carbon::now();
        $formattedDate = $curDate->format('d-m-Y H:i');

        // Get exception type
        $exceptionType = 'LIVE exception';

        if(Config::get('app.debug'))
            $exceptionType = 'local exception';

        // Format subject
        $subject = '[' . $exceptionType . ':' . $formattedDate . '] ' . $this->exceptionClass;

        // Send the mail
        (new KuroMail())
            ->setTo($adminEmail)
            ->setSubject($subject)
            ->setContent(view('email.admin_exception_email', ['exception' => $this->exceptionDump])->render())
            ->send();

        return true;
    }
}
