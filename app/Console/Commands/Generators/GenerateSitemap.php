<?php

namespace App\Console\Commands\Generators;

use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\Character;
use App\Models\Episode;
use App\Models\ExploreCategory;
use App\Models\Genre;
use App\Models\MediaSong;
use App\Models\MediaStaff;
use App\Models\MediaStudio;
use App\Models\Person;
use App\Models\Season;
use App\Models\Song;
use App\Models\Studio;
use App\Models\Theme;
use App\Models\User;
use DB;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;

class GenerateSitemap extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'generate:sitemaps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        DB::disableQueryLog();

        // Prepare sitemap index
        $sitemapIndex = SitemapIndex::create();

        //========== Explore Category sitemap ==========//
        $this->info('- Generating explore categories...');
        ExploreCategory::withoutGlobalScopes()
            ->select(['slug'])
            ->chunk(20000, function ($exploreCategory, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/explore_category_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($exploreCategory)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Anime sitemap ==========//
        $this->info('- Generating anime...');
        Anime::withoutGlobalScopes()
            ->select(['slug'])
            ->chunk(20000, function ($anime, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/anime_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($anime)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Anime Cast sitemap ==========//
        $this->info('- Generating anime cast...');
        DB::statement("SET SQL_MODE=''");
        AnimeCast::withoutGlobalScopes()
            ->with('anime')
            ->select(['anime_id'])
            ->groupBy('anime_id')
            ->chunk(20000, function ($animeCast, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/anime_cast_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($animeCast)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });
        DB::statement('SET SQL_MODE=only_full_group_by');

        //========== Anime Songs sitemap ==========//
        $this->info('- Generating anime songs...');
        DB::statement("SET SQL_MODE=''");
        MediaSong::withoutGlobalScopes()
            ->with('model')
            ->select(['anime_id'])
            ->groupBy('anime_id')
            ->chunk(20000, function ($mediaSongs, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/media_songs_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($mediaSongs)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });
        DB::statement('SET SQL_MODE=only_full_group_by');

        //========== Characters sitemap ==========//
        $this->info('- Generating characters...');
        Character::withoutGlobalScopes()
            ->select(['slug'])
            ->chunk(20000, function ($characters, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/characters_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($characters)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Episodes sitemap ==========//
        $this->info('- Generating episodes...');
        Episode::withoutGlobalScopes()
            ->select(['id'])
            ->chunk(20000, function($episodes, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/episodes_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($episodes)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Genres sitemap ==========//
        $this->info('- Generating genres...');
        Genre::withoutGlobalScopes()
            ->select(['slug'])
            ->chunk(20000, function($genres, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/genres_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($genres)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Themes sitemap ==========//
        $this->info('- Generating themes...');
        Theme::withoutGlobalScopes()
            ->select(['slug'])
            ->chunk(20000, function($genres, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/themes_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($genres)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Media Staff sitemap ==========//
        $this->info('- Generating anime staff...');
        DB::statement("SET SQL_MODE=''");
        MediaStaff::withoutGlobalScopes()
            ->with('model')
            ->select(['model_type', 'model_id'])
            ->groupBy(['model_type', 'model_id'])
            ->chunk(20000, function ($mediaStaff, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/media_staff_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($mediaStaff)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });
        DB::statement('SET SQL_MODE=only_full_group_by');

        //========== Media Studios sitemap ==========//
        $this->info('- Generating anime studios...');
        DB::statement("SET SQL_MODE=''");
        MediaStudio::withoutGlobalScopes()
            ->with('model')
            ->select(['model_type', 'model_id'])
            ->groupBy(['model_type', 'model_id'])
            ->chunk(20000, function ($mediaStudios, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/media_studios_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($mediaStudios)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });
        DB::statement('SET SQL_MODE=only_full_group_by');

        //========== People sitemap ==========//
        $this->info('- Generating people...');
        Person::withoutGlobalScopes()
            ->select(['slug'])
            ->chunk(20000, function($people, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/people_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($people)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Season Episodes sitemap ==========//
        $this->info('- Generating season...');
        Season::withoutGlobalScopes()
            ->select(['id'])
            ->chunk(20000, function($seasons, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/season_episodes_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($seasons)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Studios sitemap ==========//
        $this->info('- Generating songs...');
        Song::withoutGlobalScopes()
            ->select(['id'])
            ->chunk(20000, function($songs, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/songs_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($songs)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Studios sitemap ==========//
        $this->info('- Generating studios...');
        Studio::withoutGlobalScopes()
            ->select(['slug'])
            ->chunk(20000, function($studios, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/studios_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($studios)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Users sitemap ==========//
        $this->info('- Generating users...');
        User::withoutGlobalScopes()
            ->select(['slug'])
            ->chunk(20000, function($users, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/users_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($users)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Sitemap Index ==========//
        $this->info('- Generating sitemap index...');
        $sitemapIndex->writeToFile(public_path('sitemaps/sitemap_index.xml'));

        $this->info('- Done -');
        return Command::SUCCESS;
    }
}
