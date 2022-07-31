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
    protected function schedule(Schedule $schedule): void
    {
        $logFile = 'laravel.log';
        $logFilePath = storage_path().'/logs/' . $logFile;

        /**********************************************/
        // Run queue worker every hour
        $schedule->command('queue:work --stop-when-empty --timeout=0')
            ->hourly()
            ->withoutOverlapping()
            ->sendOutputTo($logFilePath);

        /**********************************************/
        // Generate sitemap every day
//        $schedule->command('sitemap:generate')
//            ->onOneServer()
//            ->daily();

        /**********************************************/
        // Calculate anime ratings every day
        $schedule->command('calculate:anime_ratings')
            ->daily()
            ->onOneServer()
            ->sendOutputTo($logFilePath);

        /**********************************************/
        // Calculate anime library stats every day
        $schedule->command('calculate:anime_library_stats')
            ->daily()
            ->onOneServer()
            ->sendOutputTo($logFilePath);

        /**********************************************/
//        // Delete all users that did not confirm their email within 24 hrs every day
//        $schedule->command('delete:inactive_unconfirmed_users')
//            ->daily()
//            ->onOneServer()
//            ->sendOutputTo($logFilePath);

        /**********************************************/
        // Truncates login attempts every day
        $schedule->call(function() {
            LoginAttempt::truncate();
        })
            ->daily()
            ->onOneServer()
            ->sendOutputTo($logFilePath);

        /**********************************************/
        // Calculate episode ratings every week
        $schedule->command('calculate:episode_ratings')
            ->weekly()
            ->onOneServer()
            ->sendOutputTo($logFilePath);

        /**********************************************/
        // Calculate episode ratings every week
        $schedule->command('calculate:episode_stats')
            ->weekly()
            ->onOneServer()
            ->sendOutputTo($logFilePath);

        /**********************************************/
        // Delete all activity logs every week
        $schedule->command('activitylog:clean')
            ->weekly()
            ->onOneServer()
            ->sendOutputTo($logFilePath);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
