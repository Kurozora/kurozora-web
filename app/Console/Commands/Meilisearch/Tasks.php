<?php

namespace App\Console\Commands\Meilisearch;

use Illuminate\Console\Command;
use MeiliSearch\Client;

class Tasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:tasks
            {index? : The name of the index.}
            {--s|status=enqueued : Choose from: enqueued, succeeded, or failed.}
            {--r|rows=50 : The number of rows to show.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List last 50 tasks globally, or of a given index.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Get options
        $index = $this->argument('index');
        $status = $this->option('status') ?? 'enqueued';
        $rows = $this->option('rows') ?? null;

        // Initialize Meilisearch client
        $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

        // Get results
        $results = $index ? $client->index($index)->getTasks()['results'] : $client->getTasks()['results'];
        $headers = array_keys($results[0]);
        $tasks = [];

        // Filter results according to options
        $results = array_filter($results, function($result) use ($status) {
            return $result['status'] == $status;
        });

        $this->info('Total: ' . count($results));

        $results = array_slice($results, 0, $rows);

        // Make sure the results aren't too long for the screen
        foreach ($results as $result) {
            array_walk($result, function(&$id) {
                $id = substr(json_encode($id), 0, 25);
            });
            $tasks[] = $result;
        }

        // Print the tasks
        $this->table($headers, $tasks);

        return Command::SUCCESS;
    }
}
