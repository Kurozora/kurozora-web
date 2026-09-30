<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Enums\UserLibraryKind;

class AnimeList extends BaseLibraryList
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_animelist
                            {username? : The MyAnimeList username the anime list belongs to}
                            {user? : The ID of the Kurozora user the anime list is imported into}
                            {--overwrite : Replace the user\'s anime library instead of merging into it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports a MyAnimeList user\'s anime list into a Kurozora user\'s anime library.';

    /**
     * The library the list is imported into.
     *
     * @return UserLibraryKind
     */
    protected function libraryKind(): UserLibraryKind
    {
        return UserLibraryKind::Anime();
    }
}
