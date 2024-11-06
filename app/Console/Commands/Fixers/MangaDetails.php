<?php

namespace App\Console\Commands\Fixers;

use App\Models\Manga;
use Illuminate\Console\Command;

class MangaDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:manga_details';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix manga details';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $mangas = Manga::withoutGlobalScopes()
            ->where('mal_id', '!=', null)
            ->where('status_id', '!=', 9)
            ->whereDate('updated_at', '<', today())
            ->pluck('mal_id');

        $this->info('Fixing ' . $mangas->count() . ' manga');

        if ($mangas->count()) {
            $this->call('scrape:mal_manga', ['malID' => $mangas->implode(',')]);
        }

        return Command::SUCCESS;
    }
}
