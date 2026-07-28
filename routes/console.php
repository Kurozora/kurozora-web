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
// Re-score feed messages within the activity window every fifteen minutes
Schedule::command('calculate:feed_message_ranking')
    ->everyFifteenMinutes()
    ->name('Calculate feed message ranking')
    ->withoutOverlapping()
    ->onOneServer();

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
// Top up the Kotodama word bank from the catalog every Monday at 03:40
Schedule::command('kotodama:generate-words')
    ->weeklyOn(1, '3:40')
    ->name('Generate Kotodama words')
    ->withoutOverlapping()
    ->onOneServer();

/**********************************************/
// Schedule tomorrow's Kotodama puzzles every day at 00:10
Schedule::command('kotodama:schedule')
    ->dailyAt('0:10')
    ->name('Schedule tomorrow\'s Kotodama puzzles')
    ->onOneServer();

/**********************************************/
// Sweep abandoned daily Kotodama games every day at 00:20
Schedule::command('kotodama:sweep-abandoned')
    ->dailyAt('0:20')
    ->name('Sweep abandoned Kotodama games')
    ->onOneServer();

/**********************************************/
// Notify users whose moderation timeout has just expired
Schedule::command('timeouts:notify-expired')
    ->everyFiveMinutes()
    ->name('Notify expired timeouts')
    ->withoutOverlapping()
    ->onOneServer();

/**********************************************/
// Generate sitemaps every day at 02:30
Schedule::command('generate:sitemaps')
    ->dailyAt('2:30')
    ->name('Generate sitemaps')
    ->onOneServer()
    ->runInBackground();

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
// Calculate episode stats every day
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
// Verify Apple Root CA pinned fingerprints still match Apple's published copy every day
Schedule::command('refresh:apple_root_certs')
    ->dailyAt('3:15')
    ->name('Verify Apple Root CA pins')
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
// Delete expired spammer blocks every day at 03:45
Schedule::command('delete:expired_spammer_blocks')
    ->dailyAt('3:45')
    ->name('Delete expired spammer blocks')
    ->onOneServer();

/**********************************************/
// Delete stale library sync tombstones every day at 03:50
Schedule::command('delete:stale_library_tombstones')
    ->dailyAt('3:50')
    ->name('Delete stale library tombstones')
    ->onOneServer();

/**********************************************/
// Calculate user reputation every week
Schedule::command('calculate:user_reputation')
    ->weeklyOn(0)
    ->name('Calculate user reputation')
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
// Delete stale link previews every week
Schedule::command('delete:stale_link_previews')
    ->weeklyOn(1, '3:00')
    ->name('Delete stale link previews')
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
