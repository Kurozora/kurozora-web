<?php

namespace App\Console\Commands\ELB;

use App\Models\Season;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportSeasons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_seasons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports seasons from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Season::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $seasons) {
                /** @var Season $season */
                foreach ($seasons as $season) {
                    try {
                        Season::updateOrCreate([
                            'anime_id' => $season->anime_id,
                            'number' => $season->number,
                        ], [
                            'id' => $season->id,
                            'anime_id' => $season->anime_id,
                            'number' => $season->number,
                            'title' => $season->title,
                            'synopsis' => $season->synopsis,
                            'ja' => [
                                'title' => $season->translate('ja')->title,
                                'synopsis' => $season->translate('ja')->synopsis,
                            ],
                            'first_aired' => $season->first_aired,
                            'last_aired' => $season->last_aired,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $season->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $season->id . PHP_EOL;
                }
            });

        return 0;
    }
}
