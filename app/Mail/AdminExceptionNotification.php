<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminExceptionNotification extends Mailable
{
    use Queueable, SerializesModels;

    protected $exceptionDump;
    protected $exceptionClass;
    protected $exceptionLine;
    protected $exceptionFile;

    /**
     * Create a new message instance.
     *
     * @param $exceptionDump
     * @param $exceptionClass
     * @param $exceptionLine
     * @param $exceptionFile
     */
    public function __construct($exceptionDump, $exceptionClass, $exceptionLine, $exceptionFile)
    {
        $this->exceptionDump = $exceptionDump;
        $this->exceptionClass = $exceptionClass;
        $this->exceptionLine = $exceptionLine;
        $this->exceptionFile = $exceptionFile;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this
            ->subject($this->makeSubject())
            ->view('email.admin_exception_email')
            ->with([
                'exception' => $this->exceptionDump
            ]);
    }

    /**
     * Creates the subject for the exception email
     *
     * @return string
     */
    protected function makeSubject(): string
    {
        // Get date
        $curDate = Carbon::now();
        $formattedDate = $curDate->format('d-m-Y H:i');

        // Get exception type
        $exceptionType = 'LIVE exception';

        if (config('app.debug'))
            $exceptionType = 'local exception';

        // Return the subject
        return '[' . $exceptionType . ':' . $formattedDate . '] "' . $this->exceptionClass . '" on line ' . $this->exceptionLine . ' @ ' . $this->exceptionFile;
    }
}
