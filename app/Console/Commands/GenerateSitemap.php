<?php

namespace App\Console\Commands;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Person;
use App\Models\Season;
use App\Models\Studio;
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
    protected $signature = 'sitemap:generate';

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

        //========== Anime sitemap ==========//
        $this->info('- Generating anime...');
        Anime::select(['slug'])
            ->chunk(20000, function ($anime, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/anime_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($anime)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Characters sitemap ==========//
        $this->info('- Generating characters...');
        Character::select(['slug'])
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
        Episode::select(['id'])
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
        Genre::select(['slug'])
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
        Genre::select(['slug'])
            ->chunk(20000, function($genres, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/themes_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($genres)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== People sitemap ==========//
        $this->info('- Generating people...');
        Person::select(['slug'])
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
        Season::select(['id'])
            ->chunk(20000, function($seasons, int $page) use ($sitemapIndex) {
                $path = 'sitemaps/season_episodes_' . $page . '_sitemap.xml';
                $this->info($path);
                Sitemap::create()
                    ->add($seasons)
                    ->writeToFile(public_path($path));
                $sitemapIndex->add($path);
            });

        //========== Studios sitemap ==========//
        $this->info('- Generating studios...');
        Studio::select(['slug'])
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
        User::select(['slug'])
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
        $sitemapIndex->writeToFile(public_path('sitemaps/sitemap.xml'));

        $this->info('- Done -');
        return Command::SUCCESS;
    }
}
