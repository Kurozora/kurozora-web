<?php

namespace App;

use App\Helpers\KuroMail;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    // The length of a reset token
    const TOKEN_LENGTH = 30;

    // For how many hours is the password reset valid
    const VALID_HOURS = 24;

    // Table name
    const TABLE_NAME = 'user_password_reset';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['user_id', 'ip', 'token'];

    /**
     * Returns a token to use with password reset links
     *
     * @return string
     */
    public static function genToken()
    {
        return str_random(self::TOKEN_LENGTH);
    }

    /**
     * Generates a new temporary password
     *
     * @return string
     */
    public static function genTempPassword() {
        return str_random(10);
    }

    /**
     * Sends an email notification to the user about their password reset
     *
     * @return bool
     * @throws \Throwable
     */
    public function sendEmailNotification()
    {
        $user = User::find($this->user_id);

        if (!$user) return false;

        // Get data for the email
        $emailData = [
            'title' => 'Password reset',
            'username' => $user->username,
            'ip' => $this->ip,
            'reset_url' => url('/reset/' . $this->token)
        ];

        // Send the email
        (new KuroMail())
            ->setTo($user->email)
            ->setSubject('Reset your password')
            ->setContent(view('email.password_reset_notification', $emailData)->render())
            ->send();

        return true;
    }
}
