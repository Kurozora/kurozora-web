<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReminderAnime extends Model
{
    const string CAL_NAME = 'Kurozora';
    const string CAL_DESCRIPTION = 'The Kurozora calendar group contains all reminders you have subscribed to in the Kurozora app.';
    const int|float CAL_REFRESH_INTERVAL = 60 * 24;
    const int CAL_FIRST_ALERT_MINUTES = 15;
    const int CAL_SECOND_ALERT_MINUTES = 10;
    const int|float CAL_THIRD_ALERT_DAY = 60 * 24;

    // Table name
    const string TABLE_NAME = 'user_reminder_animes';
    protected $table = self::TABLE_NAME;
}
