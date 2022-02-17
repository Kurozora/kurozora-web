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
        // Generate sitemap every day
//        $schedule->command('sitemap:generate')
//            ->daily();

        /**********************************************/
        // Calculate anime ratings every day
        $schedule->command('calculate:anime_ratings')
            ->daily();

        /**********************************************/
        // Calculate anime library stats every day
        $schedule->command('calculate:anime_library_stats')
            ->daily();

        /**********************************************/
        // Calculate episode ratings every week
        $schedule->command('calculate:episode_ratings')
            ->weekly();

        /**********************************************/
        // Delete all users that did not confirm their email within 24 hrs every day
        $schedule->command('users:delete_inactive_unconfirmed')
            ->daily();

        /**********************************************/
        // Delete all activity logs every week
        $schedule->command('activitylog:clean')
            ->weekly();

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
