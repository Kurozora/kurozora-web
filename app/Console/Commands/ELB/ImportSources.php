<?php

namespace App\Console\Commands\ELB;

use App\Models\Source;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportSources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_sources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports sources from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Source::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $sources) {
                /** @var Source $source */
                foreach ($sources as $source) {
                    try {
                        Source::updateOrCreate([
                            'id' => $source->id,
                            'name' => $source->name,
                        ], [
                            'id' => $source->id,
                            'name' => $source->name,
                            'description' => $source->description,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $source->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $source->id . PHP_EOL;
                }
            });

        return 0;
    }
}
