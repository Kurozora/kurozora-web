<?php

namespace App\Console\Commands\ELB;

use App\Models\MediaGenre;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMediaGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_media_genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports media genres from the ELB database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        MediaGenre::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $mediaGenres) {
                /** @var MediaGenre $mediaGenre */
                foreach ($mediaGenres as $mediaGenre) {
                    try {
                        MediaGenre::updateOrCreate([
                            'model_id' => $mediaGenre->model_id,
                            'model_type' => $mediaGenre->model_type,
                            'genre_id' => $mediaGenre->genre_id,
                        ], [
                            'id' => $mediaGenre->id,
                            'model_id' => $mediaGenre->model_id,
                            'model_type' => $mediaGenre->model_type,
                            'genre_id' => $mediaGenre->genre_id,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $mediaGenre->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $mediaGenre->id . PHP_EOL;
                }
            });

        return Command::SUCCESS;
    }
}
