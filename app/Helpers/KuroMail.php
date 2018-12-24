<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use \SendGrid\Mail;
use \SendGrid\Email;
use \SendGrid\Content;

class KuroMail {
    // Debug mode should be used during testing fase
    const DEBUG = false;

    // Email variables
    protected $api_key;

    protected $fromName = 'Kurozora';
    protected $from = 'no-reply@kurozora.app';

    protected $to = null;
    protected $toName = 'Kurozora User';

    protected $subject = 'E-mail from Kurozora';
    protected $content;

    public $response = null;

    /**
     * KuroMail constructor.
     */
    public function __construct() {
        $this->api_key = Config::get('mail.sendgrid-api-key');
    }

    /**
     * Determines where the email will be sent from
     *
     * @param $newFromEmail
     * @param null $newFromName
     * @return $this
     */
    public function setFrom($newFromEmail, $newFromName = null) {
        if(filter_var($newFromEmail, FILTER_VALIDATE_EMAIL)) {
            $this->from = $newFromEmail;

            if($newFromName != null)
                $this->fromName = $newFromName;
        }
        return $this;
    }

    /**
     * Determines to who the email will be sent
     *
     * @param $newToEmail
     * @param null $newToName
     * @return $this
     */
    public function setTo($newToEmail, $newToName = null) {
        if(filter_var($newToEmail, FILTER_VALIDATE_EMAIL)) {
            $this->to = $newToEmail;

            if($newToName != null)
                $this->toName = $newToName;
        }
        return $this;
    }

    /**
     * Sets the subject of the email
     *
     * @param $subjectStr
     * @return $this
     */
    public function setSubject($subjectStr) {
        $this->subject = $subjectStr;
        return $this;
    }

    /**
     * Sets the content of the email
     *
     * @param $contentStr
     * @return $this
     */
    public function setContent($contentStr) {
        $this->content = $contentStr;
        return $this;
    }

    /**
     * Makes SendGrid request to send the email
     *
     * @return $this
     */
    public function send() {
        if(self::DEBUG) {
            (new JSONResult())
                ->setError('Sent email with subject "' . $this->subject . '" to "' . $this->to . '"')
                ->show();

            return $this;
        }

        if($this->to != null) {
            $SendGridHandle = new \SendGrid($this->api_key);

            $SendGridFrom = new Email($this->fromName, $this->from);
            $SendGridTo = new Email($this->toName, $this->to);

            $SendGridContent = new Content('text/html', $this->content);

            $SendGridMail = new Mail($SendGridFrom, $this->subject, $SendGridTo, $SendGridContent);

            $this->response = $SendGridHandle->client->mail()->send()->post($SendGridMail);
        }
        return $this;
    }
}