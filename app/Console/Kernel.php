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
        // Run queue worker every hour
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
        // Calculate anime library stats every day
        $schedule->command('calculate:anime_library_stats')
            ->daily()
            ->name('Calculate anime library stats')
            ->onOneServer();

        /**********************************************/
        // Calculate anime views every day
        $schedule->command('calculate:anime_views')
            ->daily()
            ->name('Calculate anime views')
            ->onOneServer();

        /**********************************************/
        // Calculate game library stats every day
        $schedule->command('calculate:game_library_stats')
            ->daily()
            ->name('Calculate game library stats')
            ->onOneServer();

        /**********************************************/
        // Calculate game views every week
        $schedule->command('calculate:game_views')
            ->daily()
            ->name('Calculate game views')
            ->onOneServer();

        /**********************************************/
        // Calculate manga library stats every day
        $schedule->command('calculate:manga_library_stats')
            ->daily()
            ->name('Calculate manga library stats')
            ->onOneServer();

        /**********************************************/
        // Calculate manga views every week
        $schedule->command('calculate:manga_views')
            ->daily()
            ->name('Calculate manga views')
            ->onOneServer();

        /**********************************************/
        // Calculate character views every week
        $schedule->command('calculate:character_views')
            ->daily()
            ->name('Calculate character views')
            ->onOneServer();

        /**********************************************/
        // Calculate episode stats every week
        $schedule->command('calculate:episode_stats')
            ->daily()
            ->name('Calculate episode stats')
            ->onOneServer();

        /**********************************************/
        // Calculate episode views every week
        $schedule->command('calculate:episode_views')
            ->daily()
            ->name('Calculate episode views')
            ->onOneServer();

        /**********************************************/
        // Calculate person views every week
        $schedule->command('calculate:person_views')
            ->daily()
            ->name('Calculate person views')
            ->onOneServer();

        /**********************************************/
        // Calculate season views every week
        $schedule->command('calculate:season_views')
            ->daily()
            ->name('Calculate season views')
            ->onOneServer();

        /**********************************************/
        // Calculate song views every week
        $schedule->command('calculate:song_views')
            ->daily()
            ->name('Calculate song views')
            ->onOneServer();

        /**********************************************/
        // Calculate studio views every week
        $schedule->command('calculate:studio_views')
            ->daily()
            ->name('Calculate studio views')
            ->onOneServer();

        /**********************************************/
        // Calculate user views every week
        $schedule->command('calculate:user_views')
            ->daily()
            ->name('Calculate user views')
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
