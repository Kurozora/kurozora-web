<?php

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Game;
use App\Models\LoginAttempt;
use App\Models\Manga;
use Illuminate\Support\Facades\Schedule;

/**********************************************/
// Run queue worker every minute
Schedule::command('queue:work --timeout=0')
    ->everyMinute()
    ->name('Queue worker')
    ->withoutOverlapping()
    ->runInBackground();

/**********************************************/
// Delete stale cache every two hours
Schedule::command('delete:stale_cache')
    ->everyTwoHours()
    ->name('Delete stale cache')
    ->onOneServer();

/**********************************************/
// Scrape upcoming anime twice a day
Schedule::command('scrape:mal_upcoming_anime')
    ->twiceDaily()
    ->name('Scrape upcoming anime')
    ->onOneServer();

/**********************************************/
// Scrape upcoming manga twice a day
Schedule::command('scrape:mal_upcoming_manga')
    ->twiceDaily(3, 15)
    ->name('Scrape upcoming anime')
    ->onOneServer();

/**********************************************/
// Scrape upcoming anime every six hours
Schedule::command('fix:anime_details')
    ->everySixHours()
    ->name('Fix anime details')
    ->onOneServer();

/**********************************************/
// Scrape upcoming manga every six hours
Schedule::command('fix:manga_details')
    ->everySixHours(59)
    ->name('Fix manga details')
    ->onOneServer();

/**********************************************/
// Generate sitemap every day
//Schedule::command('sitemap:generate')
//    ->daily()
//    ->name('Generate sitemap')
//    ->onOneServer()
//    ->runInBackground();

/**********************************************/
// Prune Telescope table
Schedule::command('telescope:prune --hours=48')
    ->daily()
    ->name('Pruning Telescope table')
    ->onOneServer();

/**********************************************/
// Calculate total rankings every day
Schedule::command('calculate:rankings', [
    'all'
])
    ->daily()
    ->name('Calculate total rankings')
    ->onOneServer();

/**********************************************/
// Calculate anime views every day
Schedule::command('calculate:views', [
    'all'
])
    ->dailyAt('1:00')
    ->name('Calculate views')
    ->onOneServer();

/**********************************************/
// Calculate episode stats every week
Schedule::command('calculate:episode_stats')
    ->dailyAt('2:00')
    ->name('Calculate episode stats')
    ->onOneServer();

/**********************************************/
// Prune all models that match their respective criteria every day
Schedule::command('model:prune')
    ->dailyAt('3:00')
    ->name('Prune models')
    ->onOneServer();

/**********************************************/
// Truncates login attempts every day
Schedule::call(function() {
    LoginAttempt::truncate();
})
    ->dailyAt('3:30')
    ->name('Clear login attempts')
    ->onOneServer();

/**********************************************/
// Delete all activity logs every week
Schedule::command('activitylog:clean', ['--days' => 7, '--force'])
    ->weeklyOn(0, '3:35')
    ->name('Clean activity log')
    ->onOneServer();

/**********************************************/
// Calculate anime ratings every week
Schedule::command('calculate:ratings', [
    Anime::class
])
    ->weeklyOn(0, '4:00')
    ->name('Calculate anime rating')
    ->onOneServer();

/**********************************************/
// Calculate manga ratings every week
Schedule::command('calculate:ratings', [
    Manga::class
])
    ->weeklyOn(0, '4:30')
    ->name('Calculate manga rating')
    ->onOneServer();

/**********************************************/
// Calculate game ratings every week
Schedule::command('calculate:ratings', [
    Game::class
])
    ->weeklyOn(0, '5:00')
    ->name('Calculate game rating')
    ->onOneServer();

/**********************************************/
// Calculate episode ratings every week
Schedule::command('calculate:ratings', [
    Episode::class
])
    ->weeklyOn(0, '5:30')
    ->name('Calculate episode rating')
    ->onOneServer();

/**********************************************/
// Calculate global ranking every week
Schedule::command('calculate:rankings -g')
    ->weeklyOn(0, '6:00')
    ->name('Calculate global rankings')
    ->onOneServer();

/**********************************************/
// Generate ReCAP every month except January and December
Schedule::command('generate:recaps', [
    'all',
    now()->year,
    now()->subMonth()->month
])
    ->cron('0 9 1 2,3,4,5,6,7,8,9,10,11 *')
    ->name('Generate monthly recaps')
    ->onOneServer();

/**********************************************/
// Generate yearly ReCAP every week in December
Schedule::command('generate:recaps', [
    'all',
    now()->year
])
    ->yearlyOn(12, 1, '9:00')
    ->fridays()
    ->name('Generate yearly recaps')
    ->onOneServer();

/**********************************************/
// Generate previous year's ReCAP every year on January
Schedule::command('generate:recaps', [
    'all',
    now()->subYear()->year
])
    ->yearlyOn(1, 1, '9:00')
    ->name('Generate previous year’s recaps')
    ->onOneServer();
