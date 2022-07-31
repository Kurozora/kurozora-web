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
//        $logFile = 'laravel.log';
//        $logFilePath = storage_path().'/logs/' . $logFile;

        /**********************************************/
        // Run queue worker every minute
        $schedule->command('queue:work --timeout=0')
            ->everyMinute()
            ->name('Queue worker')
            ->withoutOverlapping()
            ->runInBackground();

        /**********************************************/
        // Generate sitemap every day
//        $schedule->command('sitemap:generate')
//            ->daily()
//            ->name('Generate sitemap')
//            ->onOneServer()
//            ->runInBackground();

        /**********************************************/
        // Calculate anime ratings every day
        $schedule->command('calculate:anime_ratings')
            ->daily()
            ->name('Calculate anime rating')
            ->onOneServer();

        /**********************************************/
        // Calculate anime library stats every day
        $schedule->command('calculate:anime_library_stats')
            ->daily()
            ->name('Calculate anime library stats')
            ->onOneServer();

        /**********************************************/
//        // Delete all users that did not confirm their email within 24 hrs every day
//        $schedule->command('delete:inactive_unconfirmed_users')
//            ->daily()
//            ->name('Clear inactive unconfirmed users')
//            ->onOneServer();

        /**********************************************/
        // Truncates login attempts every day
        $schedule->call(function() {
            LoginAttempt::truncate();
        })
            ->daily()
            ->name('Clear login attempts')
            ->onOneServer();

        /**********************************************/
        // Calculate episode ratings every week
        $schedule->command('calculate:episode_ratings')
            ->weekly()
            ->name('Calculate episode rating')
            ->onOneServer();

        /**********************************************/
        // Calculate episode ratings every week
        $schedule->command('calculate:episode_stats')
            ->weekly()
            ->name('Calculate anime stats')
            ->onOneServer();

        /**********************************************/
        // Delete all activity logs every week
        $schedule->command('activitylog:clean')
            ->weekly()
            ->name('Clean activity log')
            ->onOneServer();
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
