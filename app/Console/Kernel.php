<?php

namespace App\Console;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Game;
use App\Models\LoginAttempt;
use App\Models\Manga;
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
        // Delete stale cache every two hours
        $schedule->command('delete:stale_cache')
            ->everyTwoHours()
            ->name('Delete stale cache')
            ->onOneServer();

        /**********************************************/
        // Scrape upcoming anime twice a day
        $schedule->command('scrape:mal_upcoming_anime')
            ->twiceDaily()
            ->name('Scrape upcoming anime')
            ->onOneServer();

        /**********************************************/
        // Scrape upcoming manga twice a day
        $schedule->command('scrape:mal_upcoming_manga')
            ->twiceDaily(3, 15)
            ->name('Scrape upcoming anime')
            ->onOneServer();

        /**********************************************/
        // Scrape upcoming anime every six hours
        $schedule->command('fix:anime_details')
            ->everySixHours()
            ->name('Fix anime details')
            ->onOneServer();

        /**********************************************/
        // Scrape upcoming manga every six hours
        $schedule->command('fix:manga_details')
            ->everySixHours()
            ->name('Fix manga details')
            ->onOneServer();

        /**********************************************/
        // Generate sitemap every day
//        $schedule->command('sitemap:generate')
//            ->daily()
//            ->name('Generate sitemap')
//            ->onOneServer()
//            ->runInBackground();

        /**********************************************/
        // Prune Telescope table
        $schedule->command('telescope:prune --hours=48')
            ->daily()
            ->name('Pruning Telescope table')
            ->onOneServer();

        /**********************************************/
        // Calculate total rankings every day
        $schedule->command('calculate:rankings', ['models' => 'all'])
            ->daily()
            ->name('Calculate total rankings')
            ->onOneServer();

        /**********************************************/
        // Calculate anime views every day
        $schedule->command('calculate:views', ['model' => 'all'])
            ->daily()
            ->name('Calculate views')
            ->onOneServer();

        /**********************************************/
        // Calculate episode stats every week
        $schedule->command('calculate:episode_stats')
            ->daily()
            ->name('Calculate episode stats')
            ->onOneServer();

        /**********************************************/
        // Prune all models that match their respective criteria every day
        $schedule->command('model:prune')
            ->daily()
            ->name('Prune models')
            ->onOneServer();

        /**********************************************/
        // Truncates login attempts every day
        $schedule->call(function() {
            LoginAttempt::truncate();
        })
            ->daily()
            ->name('Clear login attempts')
            ->onOneServer();

        /**********************************************/
        // Delete all activity logs every week
        $schedule->command('activitylog:clean')
            ->weekly()
            ->name('Clean activity log')
            ->onOneServer();

        /**********************************************/
        // Calculate anime ratings every week
        $schedule->command('calculate:ratings', ['model' => Anime::class])
            ->weekly()
            ->name('Calculate anime rating')
            ->onOneServer();

        /**********************************************/
        // Calculate manga ratings every week
        $schedule->command('calculate:ratings', ['model' => Manga::class])
            ->weekly()
            ->name('Calculate manga rating')
            ->onOneServer();

        /**********************************************/
        // Calculate game ratings every week
        $schedule->command('calculate:ratings', ['model' => Game::class])
            ->weekly()
            ->name('Calculate game rating')
            ->onOneServer();

        /**********************************************/
        // Calculate episode ratings every week
        $schedule->command('calculate:ratings', ['model' => Episode::class])
            ->weekly()
            ->name('Calculate episode rating')
            ->onOneServer();

        /**********************************************/
        // Calculate global ranking every week
        $schedule->command('calculate:rankings -g')
            ->weekly()
            ->name('Calculate global rankings')
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
