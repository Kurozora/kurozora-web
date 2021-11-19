<?php

namespace App\Console;

use App\Models\LoginAttempt;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /**********************************************/
        // Calculate anime ratings every day
        $schedule->command('calculate:anime_ratings')
            ->daily();

        /**********************************************/
        // Calculate media stats ratings every day
        $schedule->command('calculate:media_stats')
            ->daily();

        /**********************************************/
        // Delete all expired sessions every day
        $schedule->command('sessions:delete_expired')
            ->daily();

        /**********************************************/
        // Delete all users that did not confirm their email within 24 hrs every day
        $schedule->command('users:delete_inactive_unconfirmed')
            ->daily();

        /**********************************************/
        // Truncates login attempts every day
        $schedule->call(function() {
            LoginAttempt::truncate();
        })
            ->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
