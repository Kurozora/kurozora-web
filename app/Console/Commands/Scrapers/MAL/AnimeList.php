<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Enums\UserLibraryStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class AnimeList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mal_animelist {username? : the MyAnimeList username the anime list belongs to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $malUsername = $this->argument('username');

        if (empty($malUsername)) {
            $malUsername = $this->ask('MAL username');
        }

        $page = 1;
        $animes = [];

        // Get library items
        while ($response = $this->getLibraryFor($malUsername, $page)) {
            foreach ($response as $anime) {
                $animeArray = [];

                $animeArray['series_animedb_id'] = $anime->anime_id;
                $animeArray['my_status'] = $anime->status;
                $animeArray['my_score'] = $this->convertMALRating($anime->score ?? 0);
                $animeArray['my_start_date'] = $this->convertMALDate($anime->start_date_string ?? '0000-00-00');
                $animeArray['my_finish_date'] = $this->convertMALDate($anime->finish_date_string ?? '0000-00-00' );

                $animes[] = $animeArray;
            }

            if (count($response) < 300) {
                break;
            } else {
                $page++;
            }
        }

        dd($animes);
        return Command::SUCCESS;
    }

    /**
     * Get the library response for the given user.
     *
     * @param string $username
     * @param int    $page
     *
     * @return mixed
     */
    private function getLibraryFor(string $username, int $page): mixed
    {
        // Prepare URL
        $libraryURL = str(config('scraper.domains.mal.animelist.json'))
            ->replace(':x', $username)
            ->value();

        $this->line('getting page: ' . $page);

        // Get library items
        return Http::get($libraryURL, [
            'offset' => ($page - 1) * 300,
        ])
            ->object();
    }

    /**
     * Converts a MAL status string to our library status.
     *
     * @param int $malStatus
     * @return ?int
     */
    protected function convertMALStatus(int $malStatus): ?int
    {
        return match ($malStatus) {
            1 => UserLibraryStatus::InProgress,
            2 => UserLibraryStatus::OnHold,
            3 => UserLibraryStatus::Planning,
            4 => UserLibraryStatus::Dropped,
            6 => UserLibraryStatus::Completed,
            default => null,
        };
    }

    /**
     * Converts and returns Kurozora specific rating.
     *
     * @param int $malRating
     * @return int
     */
    protected function convertMALRating(int $malRating): int
    {
        if ($malRating == 0) {
            return $malRating;
        }

        return round($malRating) * 0.5;
    }

    /**
     * Converts and returns Carbon dates from given string.
     *
     * @param string $malDate
     * @return Carbon|null
     */
    protected function convertMALDate(string $malDate): ?Carbon
    {
        if ($malDate === '0000-00-00') {
            return now();
        }

        $dateComponents = explode('-', $malDate);
        return Carbon::createFromDate($dateComponents[2], $dateComponents[1], $dateComponents[0]);
    }
}
