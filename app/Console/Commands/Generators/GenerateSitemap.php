<?php

namespace App\Console\Commands\Generators;

use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\Character;
use App\Models\Episode;
use App\Models\ExploreCategory;
use App\Models\Game;
use App\Models\GameCast;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\MangaCast;
use App\Models\MediaSong;
use App\Models\MediaStaff;
use App\Models\MediaStudio;
use App\Models\Person;
use App\Models\Season;
use App\Models\Song;
use App\Models\Studio;
use App\Models\Theme;
use App\Models\User;
use DateTimeInterface;
use DB;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
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
        // Prepare sitemap index
        $sitemapIndex = SitemapIndex::create();

        //========== Explore Category sitemap ==========//
        $this->info('- Generating explore categories...');
        ExploreCategory::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['slug', 'updated_at'])
            ->chunk(20000, function (Collection $exploreCategories, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(ExploreCategory::TABLE_NAME, $page, $exploreCategories, $sitemapIndex);
            });

        //========== Anime sitemap ==========//
        $this->info('- Generating anime...');
        Anime::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['slug', 'updated_at'])
            ->chunk(20000, function (Collection $anime, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Anime::TABLE_NAME, $page, $anime, $sitemapIndex);
            });

        //========== Anime Cast sitemap ==========//
        $this->info('- Generating anime cast...');
        DB::statement("SET SQL_MODE=''");
        AnimeCast::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->with([
                'anime' => function ($query) {
                    $query->select(['id', 'slug'])
                        ->withoutGlobalScopes();
                },
            ])
            ->select(['anime_id', 'updated_at'])
            ->groupBy('anime_id')
            ->chunk(20000, function (Collection $animeCast, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(AnimeCast::TABLE_NAME, $page, $animeCast, $sitemapIndex);
            });
        DB::statement('SET SQL_MODE=only_full_group_by');

        //========== Characters sitemap ==========//
        $this->info('- Generating characters...');
        Character::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['slug', 'updated_at'])
            ->chunk(20000, function (Collection $characters, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Character::TABLE_NAME, $page, $characters, $sitemapIndex);
            });

        //========== Episodes sitemap ==========//
        $this->info('- Generating episodes...');
        Episode::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['id', 'updated_at'])
            ->chunk(20000, function (Collection $episodes, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Episode::TABLE_NAME, $page, $episodes, $sitemapIndex);
            });

        //========== Games sitemap ==========//
        $this->info('- Generating games...');
        Game::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['slug', 'updated_at'])
            ->chunk(20000, function (Collection $games, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Game::TABLE_NAME, $page, $games, $sitemapIndex);
            });

        //========== Game Cast sitemap ==========//
        $this->info('- Generating game cast...');
        DB::statement("SET SQL_MODE=''");
        GameCast::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->with([
                'game' => function ($query) {
                    $query->select(['id', 'slug'])
                        ->withoutGlobalScopes();
                },
            ])
            ->select(['game_id', 'updated_at'])
            ->groupBy('game_id')
            ->chunk(20000, function (Collection $gameCast, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(GameCast::TABLE_NAME, $page, $gameCast, $sitemapIndex);
            });
        DB::statement('SET SQL_MODE=only_full_group_by');

        //========== Genres sitemap ==========//
        $this->info('- Generating genres...');
        Genre::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['slug', 'updated_at'])
            ->chunk(20000, function (Collection $genres, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Genre::TABLE_NAME, $page, $genres, $sitemapIndex);
            });

        //========== Themes sitemap ==========//
        $this->info('- Generating themes...');
        Theme::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['slug', 'updated_at'])
            ->chunk(20000, function (Collection $themes, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Theme::TABLE_NAME, $page, $themes, $sitemapIndex);
            });

        //========== Manga sitemap ==========//
        $this->info('- Generating manga...');
        Manga::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['slug', 'updated_at'])
            ->chunk(20000, function (Collection $manga, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Manga::TABLE_NAME, $page, $manga, $sitemapIndex);
            });

        //========== Manga Cast sitemap ==========//
        $this->info('- Generating manga cast...');
        DB::statement("SET SQL_MODE=''");
        MangaCast::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->with([
                'manga' => function ($query) {
                    $query->select(['id', 'slug'])
                        ->withoutGlobalScopes();
                },
            ])
            ->select(['manga_id', 'updated_at'])
            ->groupBy('manga_id')
            ->chunk(20000, function (Collection $mangaCast, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(MangaCast::TABLE_NAME, $page, $mangaCast, $sitemapIndex);
            });
        DB::statement('SET SQL_MODE=only_full_group_by');

        //========== Media Songs sitemap ==========//
        $this->info('- Generating media songs...');
        DB::statement("SET SQL_MODE=''");
        MediaSong::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->with([
                'model' => function ($query) {
                    $query->select(['id', 'slug'])
                        ->withoutGlobalScopes();
                },
            ])
            ->select(['model_type', 'model_id', 'updated_at'])
            ->groupBy(['model_type', 'model_id'])
            ->chunk(20000, function (Collection $mediaSongs, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(MediaSong::TABLE_NAME, $page, $mediaSongs, $sitemapIndex);
            });
        DB::statement('SET SQL_MODE=only_full_group_by');

        //========== Media Staff sitemap ==========//
        $this->info('- Generating media staff...');
        DB::statement("SET SQL_MODE=''");
        MediaStaff::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->with([
                'model' => function ($query) {
                    $query->select(['id', 'slug'])
                        ->withoutGlobalScopes();
                },
            ])
            ->select(['model_type', 'model_id', 'updated_at'])
            ->groupBy(['model_type', 'model_id'])
            ->chunk(20000, function (Collection $mediaStaff, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(MediaStaff::TABLE_NAME, $page, $mediaStaff, $sitemapIndex);
            });
        DB::statement('SET SQL_MODE=only_full_group_by');

        //========== Media Studios sitemap ==========//
        $this->info('- Generating media studios...');
        DB::statement("SET SQL_MODE=''");
        MediaStudio::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->with([
                'model' => function ($query) {
                    $query->select(['id', 'slug'])
                        ->withoutGlobalScopes();
                },
            ])
            ->select(['model_type', 'model_id', 'updated_at'])
            ->groupBy(['model_type', 'model_id'])
            ->chunk(20000, function (Collection $mediaStudios, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(MediaStudio::TABLE_NAME, $page, $mediaStudios, $sitemapIndex);
            });
        DB::statement('SET SQL_MODE=only_full_group_by');

        //========== People sitemap ==========//
        $this->info('- Generating people...');
        Person::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['slug', 'updated_at'])
            ->chunk(20000, function (Collection $people, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Person::TABLE_NAME, $page, $people, $sitemapIndex);
            });

        //========== Season Episodes sitemap ==========//
        $this->info('- Generating season...');
        Season::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['id', 'updated_at'])
            ->chunk(20000, function (Collection $seasons, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Season::TABLE_NAME, $page, $seasons, $sitemapIndex);
            });

        //========== Studios sitemap ==========//
        $this->info('- Generating songs...');
        Song::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['id', 'updated_at'])
            ->chunk(20000, function (Collection $songs, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Song::TABLE_NAME, $page, $songs, $sitemapIndex);
            });

        //========== Studios sitemap ==========//
        $this->info('- Generating studios...');
        Studio::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['slug', 'updated_at'])
            ->chunk(20000, function (Collection $studios, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(Studio::TABLE_NAME, $page, $studios, $sitemapIndex);
            });

        //========== Users sitemap ==========//
        $this->info('- Generating users...');
        User::withoutGlobalScopes()
            ->withoutEagerLoads()
            ->select(['slug', 'updated_at'])
            ->chunk(20000, function (Collection $users, int $page) use ($sitemapIndex) {
                $this->generateSitemapFor(User::TABLE_NAME, $page, $users, $sitemapIndex);
            });

        //========== Sitemap Index ==========//
        $this->info('- Generating sitemap index...');
        $sitemapIndex->writeToFile(public_path('sitemaps/sitemap_index.xml'));

        $this->info('- Done -');
        return Command::SUCCESS;
    }

    /**
     * @param string       $table
     * @param int          $page
     * @param Collection   $models
     * @param SitemapIndex $sitemapIndex
     *
     * @return void
     */
    private function generateSitemapFor(string $table, int $page, Collection $models, SitemapIndex $sitemapIndex): void
    {
        $path = 'sitemaps/' . $table . '_' . $page . '_sitemap.xml';

        $this->info($path);

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';

        foreach ($models as $model) {
            $tag = $model->toSitemapTag();
            $sitemap .= '<url>';
            $sitemap .= '<loc>' . $tag->url . '</loc>';
            $sitemap .= '<changefreq>' . $tag->changeFrequency . '</changefreq>';
            $sitemap .= '<lastmod>' . $tag->lastModificationDate->format(DateTimeInterface::ATOM) . '</lastmod>';
            $sitemap .= '</url>';
        }

        $sitemap .= '</urlset>';

        file_put_contents(public_path($path), $sitemap);

        $sitemapIndex->add($path);
    }
}
