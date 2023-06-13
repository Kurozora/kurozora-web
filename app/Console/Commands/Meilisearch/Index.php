<?php

namespace App\Console\Commands\Meilisearch;

use Exception;
use Illuminate\Console\Command;
use MeiliSearch\Client;

class Index extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:index
            {index? : The name of the index.}
            {--c|create : Create an index.}
            {--d|delete : Delete an existing index.}
            {--u|update : Update an index’s sortable and filterable attributes.}
            {--filterable= : The filterable attributes to update.}
            {--sortable= : The sortable attributes to update.}
            {--s|stats : The stats of an index.}
            {--k|key=id : The name of primary key.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List, create, or delete indexes.';

    /**
     * The list headers of the table.
     *
     * @var array|string[]
     */
    protected array $listHeaders = [
        '#',
        'UID',
        'Created At',
        'Updated At',
        'Primary Key',
        'Sortable',
        'Filterable',
    ];

    /**
     * The stat headers of the table.
     *
     * @var array|string[]
     */
    protected array $statHeaders = [
        'Index',
        'Number of Documents',
        'Is Indexing',
        'Field Distribution'
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Initialize Meilisearch client
        $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

        // Get options
        $index = $this->argument('index');
        $create = $this->option('create');
        $delete = $this->option('delete');
        $update = $this->option('update');
        $filterable = $this->option('filterable');
        $sortable = $this->option('sortable');
        $stats = $this->option('stats');
        $key = $this->option('key');

        // Check if there are incompatibilities
        if ($stats && $create || $stats && $delete || $stats && $update) {
            $this->error('Specifying `stats` and other operations at the same time isn’t supported.');
            return Command::FAILURE;
        } else if ($create && $delete) {
            $this->error('Specifying `create` and `delete` at the same time isn’t supported.');
            return Command::FAILURE;
        } else if ($update && $delete) {
            $this->error('Specifying `delete` and `update` at the same time isn’t supported.');
            return Command::FAILURE;
        }

        try {
            // If create option is passed, create the given index
            if ($create) {
                if (empty($index)) {
                    $this->error('No index specified.');
                    return Command::FAILURE;
                }

                $creation_options = [];

                if ($key) {
                    $creation_options['primaryKey'] = $key;
                }

                $client->createIndex($index, $creation_options);

                $this->info('Created index: ' . $index);

                if (!$update) {
                    return Command::SUCCESS;
                }
            }

            // If delete option is passed, delete the given index
            if ($delete) {
                if (empty($index)) {
                    $this->error('No index specified.');
                    return Command::FAILURE;
                }

                $client->index($index)->delete();

                $this->info('Deleted index: ' . $index);
                return Command::SUCCESS;
            }

            if ($update) {
                if (empty($index)) {
                    $this->error('No index specified.');
                    return Command::FAILURE;
                }

                $this->updateSortableAttributes($client, $index, $sortable);
                $this->updateFilterableAttributes($client, $index, $filterable);

                $this->info('Updated index: ' . $index);
                return Command::SUCCESS;
            }

            if ($stats) {
                // Prepare the rows
                if (!$index) {
                    $allStats = $client->stats();
                    $statIndexes = $allStats['indexes'];

                    $this->info('Database Size: ' . size_shorten($allStats['databaseSize'], 2, true));
                    $this->info('Last Update: ' . $allStats['lastUpdate']);
                } else {
                    $indexStats = $client->index($index)->stats();
                    $statIndexes = [$index => $indexStats];
                }

                $rows = [];
                foreach ($statIndexes as $index => $statIndex) {
                    $row = ['index' => $index] + $statIndex;

                    array_walk($row, function(&$stat) {
                        if (is_array($stat)) {
                            foreach ($stat as $key => $value) {
                                $stat[$key] = sprintf('%-\'.42s%\'.42s', $key, $value);
                            }
                            $stat = implode(PHP_EOL, $stat);
                        }

                        if (is_bool($stat)) {
                            $stat = $stat ? 'True' : 'False';
                        }
                    });

                    $rows[] = $row;
                }

                $this->table($this->statHeaders, $rows);

                return Command::SUCCESS;
            }

            // Prepare the rows
            if ($index) {
                $index = $client->index($index);
                $attributes = ['#' => '1'] + $index->fetchRawInfo();
                $attributes[] = implode(PHP_EOL, $index->getSortableAttributes());
                $attributes[] = implode(PHP_EOL, $index->getFilterableAttributes());
                $rows = [$attributes];
            } else {
                $allIndexes = $client->getAllIndexes();
                $rows = [];

                foreach ($allIndexes as $key => $index) {
                    $row = ['#' => $key] + $index->fetchRawInfo();
                    $row[] = implode(PHP_EOL, $index->getSortableAttributes());
                    $row[] = implode(PHP_EOL, $index->getFilterableAttributes());
                    $rows[] = $row;
                }
            }

            $this->table($this->listHeaders, $rows);
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Update the given index's sortable attributes.
     *
     * @param Client $client
     * @param string $index
     * @param string|null $sortable
     * @return void
     */
    protected function updateSortableAttributes(Client $client, string $index, ?string $sortable):void
    {
        if (empty($sortable)) {
            return;
        }

        // Convert to array
        $index = $client->index($index);
        $sortable = explode(',', $sortable);
        $sortable = array_merge($sortable, $index->getSortableAttributes());
        $sortable = array_unique($sortable);

        // Update index
        $index->updateSortableAttributes($sortable);

        $this->info('Updated sortable attributes...');
    }

    /**
     * Update the given index's filterable attribute.
     *
     * @param Client $client
     * @param string $index
     * @param string|null $filterable
     * @return void
     */
    protected function updateFilterableAttributes(Client $client, string $index, ?string $filterable): void
    {
        // Return if nothing is passed
        if (empty($filterable)) {
            return;
        }

        // Convert to array
        $index = $client->index($index);
        $filterable = explode(',', $filterable);
        $filterable = array_merge($filterable, $index->getFilterableAttributes());
        $filterable = array_unique($filterable);

        // Update index
        $index->updateFilterableAttributes($filterable);

        $this->info('Updated filterable attributes...');
    }
}
