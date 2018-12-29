<?php

namespace App\Console;

use App\Anime;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\LoginAttempt;
use Illuminate\Support\Facades\Artisan;

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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /**********************************************/
        // Fetch base data for Anime every 10 mins
        $schedule->call(function() {
            // Find an Anime where the base episodes have not been fetched
            $foundItem = Anime::where('fetched_base_episodes', '=', false)->first();

            if($foundItem)
                Artisan::call('animes:fetch_base_episodes', ['id' => $foundItem->id]);

            // Find an Anime where the images have not been fetched
            $foundItem = Anime::where('fetched_images', '=', false)->first();

            if($foundItem)
                Artisan::call('animes:fetch_images', ['id' => $foundItem->id]);

            // Find an Anime where the details have not been fetched
            $foundItem = Anime::where('fetched_details', '=', false)->first();

            if($foundItem)
                Artisan::call('animes:fetch_details', ['id' => $foundItem->id]);
        })->cron('*/10 * * * *');

        /**********************************************/
        // Recalculate Anime ratings every 4 hours
        $schedule->command('ratings:calculate')->cron('0 */4 * * *');

        /**********************************************/
        // Delete all expired sessions every day
        $schedule->command('sessions:delete_expired')->daily();

        /**********************************************/
        // Delete all users that did not confirm their email within 24 hrs every day
        $schedule->command('users:delete_inactive_unconfirmed')->daily();

        /**********************************************/
        // Delete all inactive/old password resets every day
        $schedule->command('password_resets:delete_old')->daily();

        /**********************************************/
        // Truncates login attempts every day
        $schedule->call(function() {
        	LoginAttempt::truncate();
        })->daily();
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
