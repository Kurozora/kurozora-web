<?php

namespace App\Console\Commands\ELB;

use App\Models\TvRating;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportTvRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_tv_ratings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports TV ratings from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        TvRating::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $tvRatings) {
                /** @var TvRating $tvRating */
                foreach ($tvRatings as $tvRating) {
                    try {
                        TvRating::updateOrCreate([
                            'id' => $tvRating->id,
                            'name' => $tvRating->name,
                        ], [
                            'id' => $tvRating->id,
                            'name' => $tvRating->name,
                            'description' => $tvRating->description,
                            'weight' => $tvRating->weight
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $tvRating->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $tvRating->id . PHP_EOL;
                }
            });

        return 0;
    }
}
