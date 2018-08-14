<?php

/**
 * @author Musa Semou <mussesemou99@gmail.com>
 */

namespace App\Helpers;

use \SendGrid\Mail;
use \SendGrid\Email;
use \SendGrid\Content;

/**
    Class that helps send mail through SendGrid
**/
class KuroMail {
    protected $api_key = 'XXXXX';
    const DEBUG = false;

    protected $fromName = 'Kurozora';
    protected $from = 'no-reply@kurozora.app';

    protected $to = null;
    protected $toName = 'Kurozora User';

    protected $subject = 'E-mail from Kurozora';
    protected $content;

    public $response = null;

    /**
        Get the SG API key
    **/
    public function __construct() {
        $this->api_key = env('SENDGRID_API_KEY', 'ErrorKey');
    }

    /**
        Defines the email address that will send this email
    **/
    public function setFrom($newFromEmail, $newFromName = null) {
        if(filter_var($newFromEmail, FILTER_VALIDATE_EMAIL)) {
            $this->from = $newFromEmail;

            if($newFromName != null)
                $this->fromName = $newFromName;
        }
        return $this;
    }

    /**
        Defines to who the email will be sent
    **/
    public function setTo($newToEmail, $newToName = null) {
        if(filter_var($newToEmail, FILTER_VALIDATE_EMAIL)) {
            $this->to = $newToEmail;

            if($newToName != null)
                $this->toName = $newToName;
        }
        return $this;
    }

    /**
        Sets the subject of the email
    **/
    public function setSubject($subjectStr) {
        $this->subject = $subjectStr;
        return $this;
    }

    /**
        Sets the content of this email
    **/
    public function setContent($contentStr) {
        $this->content = $contentStr;
        return $this;
    }

    /**
        Sends the email
    **/
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