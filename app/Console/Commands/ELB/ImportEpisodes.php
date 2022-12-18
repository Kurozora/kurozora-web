<?php

namespace App\Console\Commands\ELB;

use App\Models\Episode;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_episodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports episodes from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Adding episodes.');

        Episode::on('elb')
            ->orderBy('id')
            ->where('id', '>', 20000)
            ->chunk(1000, function (Collection $episodes) {
                /** @var Episode $episode */
                foreach ($episodes as $episode) {
                    try {
                        Episode::updateOrCreate([
                            'season_id' => $episode->season_id,
                            'number' => $episode->number,
                        ], [
                            'id' => $episode->id,
                            'season_id' => $episode->season_id,
                            'number' => $episode->number,
                            'number_total' => $episode->number_total,
                            'title' => $episode->title,
                            'synopsis' => $episode->synopsis,
                            'ja' => [
                                'title' => $episode->translate('ja')->title,
                                'synopsis' => $episode->translate('ja')->synopsis,
                            ],
                            'duration' => $episode->duration,
                            'is_filler' => $episode->is_filler,
                            'is_nsfw' => $episode->is_nsfw,
                            'is_special' => $episode->is_special,
                            'is_premiere' => $episode->is_premiere,
                            'is_finale' => $episode->is_finale,
                            'is_verified' => $episode->is_verified,
                            'started_at' => $episode->started_at,
                            'ended_at' => $episode->ended_at
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $episode->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $episode->id . PHP_EOL;
                }
            });

        $this->info('Adding episode to episode relation.');

        Episode::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $episodes) {
                /** @var Episode $episode */
                foreach ($episodes as $episode) {
                    try {
                        Episode::updateOrCreate([
                            'season_id' => $episode->season_id,
                            'number' => $episode->number,
                        ], [
                            'next_episode_id' => $episode->next_episode_id,
                            'previous_episode_id' => $episode->previous_episode_id,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $episode->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $episode->id . PHP_EOL;
                }
            });

        return 0;
    }
}
