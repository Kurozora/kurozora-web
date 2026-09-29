<?php

namespace App\Console\Commands\Fixers;

use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PersonNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:person_names
                            {--created-before=2024-06-27 : Only re-scrape people created before this date}
                            {--after-id=0 : Resume after this person id}
                            {--limit=0 : Maximum number of people to re-scrape}
                            {--chunk=50 : People per spider run}
                            {--dry-run : Print the plan without scraping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-scrapes people whose names predate the MyAnimeList name order fix.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $createdBefore = (string) $this->option('created-before');
        $afterID = (int) $this->option('after-id');
        $limit = (int) $this->option('limit');
        $chunk = max(1, (int) $this->option('chunk'));
        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Dry run. Nothing will be scraped.');
        }

        $query = Person::withoutGlobalScopes()
            ->whereNotNull('mal_id')
            ->where('created_at', '<', $createdBefore)
            ->where('id', '>', $afterID)
            ->orderBy('id');

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No people to re-scrape. Adios...');

            return Command::SUCCESS;
        }

        $this->info($total . ' person/people created before ' . $createdBefore . ' after id ' . $afterID . '.');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $people = $query->get(['id', 'mal_id', 'first_name', 'last_name']);
        $lastID = 0;
        $scraped = 0;

        foreach ($people->chunk($chunk) as $batch) {
            $malIDs = $batch->pluck('mal_id')->implode(',');
            $lastID = $batch->last()->id;

            $this->line('  ' . $batch->first()->id . ' - ' . $lastID . '  (' . $batch->count() . ')');

            if ($isDryRun) {
                continue;
            }

            Artisan::call('scrape:mal_person', ['malID' => $malIDs]);
            $scraped += $batch->count();
        }

        $this->newLine();
        $this->info(($isDryRun ? 'Would re-scrape: ' : 'Re-scraped: ') . ($isDryRun ? $people->count() : $scraped) . '.');

        if ($lastID > 0) {
            $this->info('Resume the next run with --after-id=' . $lastID . '.');
        }

        return Command::SUCCESS;
    }
}
