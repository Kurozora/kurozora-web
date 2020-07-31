<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserReminderAnime extends Model
{
    const CAL_NAME = 'Kurozora';
    const CAL_DESCRIPTION = 'The Kurozora calendar group contains all reminders you have subscribed to in the Kurozora app.';
    const CAL_REFRESH_INTERVAL = 60 * 24;
    const CAL_FIRST_ALERT_MINUTES = 15;
    const CAL_SECOND_ALERT_MINUTES = 10;
    const CAL_THIRD_ALERT_DAY = 60 * 24;

    // Table name
    const TABLE_NAME = 'user_reminder_animes';
    protected $table = self::TABLE_NAME;
}
