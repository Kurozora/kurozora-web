<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Enums\UserLibraryKind;

class MangaList extends BaseLibraryList
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_mangalist
                            {username? : The MyAnimeList username the manga list belongs to}
                            {user? : The ID of the Kurozora user the manga list is imported into}
                            {--overwrite : Replace the user\'s manga library instead of merging into it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports a MyAnimeList user\'s manga list into a Kurozora user\'s manga library.';

    /**
     * The library the list is imported into.
     *
     * @return UserLibraryKind
     */
    protected function libraryKind(): UserLibraryKind
    {
        return UserLibraryKind::Manga();
    }
}
