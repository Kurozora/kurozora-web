<?php

namespace App\Console\Commands\Scrapers\MAL;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use App\Jobs\ProcessMALImport;
use App\Models\User;
use App\Services\LibraryImportParser;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use UnexpectedValueException;

abstract class BaseLibraryList extends Command
{
    /**
     * The number of entries MAL returns per page.
     *
     * @var int
     */
    protected const int PAGE_SIZE = 300;

    /**
     * The MAL list status that includes every entry.
     *
     * @var int
     */
    protected const int ALL_STATUSES = 7;

    /**
     * The library the list is imported into.
     *
     * @return UserLibraryKind
     */
    abstract protected function libraryKind(): UserLibraryKind;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $malUsername = $this->argument('username') ?: $this->ask('MAL username');
        $userID = $this->argument('user') ?: $this->ask('Kurozora user ID');
        $user = User::find($userID);

        if (empty($user)) {
            $this->error('No user found with ID ' . $userID . '.');
            return Command::FAILURE;
        }

        try {
            $listEntries = $this->getListFor($malUsername);
        } catch (RequestException $exception) {
            $this->error('MAL responded with HTTP ' . $exception->response->status() . '. The list may be private, or the user may not exist.');
            return Command::FAILURE;
        } catch (ConnectionException|UnexpectedValueException $exception) {
            $this->error($exception->getMessage());
            return Command::FAILURE;
        }

        $libraryKind = $this->libraryKind();

        if (empty($listEntries)) {
            $this->warn($malUsername . '\'s ' . strtolower($libraryKind->description) . ' list is empty.');
            return Command::SUCCESS;
        }

        $entries = LibraryImportParser::parseList($listEntries, $libraryKind);
        $behavior = $this->option('overwrite') ? ImportBehavior::Overwrite() : ImportBehavior::Merge();

        dispatch(new ProcessMALImport($user, $entries, $libraryKind, ImportService::MAL(), $behavior));

        $this->info('Queued ' . count($entries) . ' entries for import into ' . $user->username . '\'s ' . strtolower($libraryKind->description) . ' library.');
        return Command::SUCCESS;
    }

    /**
     * Get every entry in the given user's list.
     *
     * @param string $username
     *
     * @return array
     * @throws ConnectionException
     * @throws RequestException
     */
    protected function getListFor(string $username): array
    {
        $listURL = str(match ($this->libraryKind()->value) {
            UserLibraryKind::Manga => config('scraper.domains.mal.mangalist.json'),
            default => config('scraper.domains.mal.animelist.json'),
        })
            ->replace(':x', rawurlencode($username))
            ->value();
        $listEntries = [];
        $page = 1;

        do {
            $this->line('[] Getting page: ' . $page);
            $this->waitForRateLimit();

            $pageEntries = Http::get($listURL, [
                'status' => self::ALL_STATUSES,
                'offset' => ($page - 1) * self::PAGE_SIZE,
            ])
                ->throw()
                ->json();

            if (!is_array($pageEntries)) {
                throw new UnexpectedValueException('MAL returned an unexpected response for page ' . $page . '.');
            }

            array_push($listEntries, ...$pageEntries);
            $page++;
        } while (count($pageEntries) === self::PAGE_SIZE);

        return $listEntries;
    }

    /**
     * Blocks until the shared MAL rate limit allows another request.
     *
     * @return void
     */
    protected function waitForRateLimit(): void
    {
        $rateLimit = config('scraper.rate_limits.mal');

        while (RateLimiter::tooManyAttempts($rateLimit['key'], (int) $rateLimit['max_attempts'])) {
            sleep(max(RateLimiter::availableIn($rateLimit['key']), 1));
        }

        RateLimiter::hit($rateLimit['key'], (int) $rateLimit['decay_seconds']);
    }
}
