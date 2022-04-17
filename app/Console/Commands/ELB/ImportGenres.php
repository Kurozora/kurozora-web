<?php

namespace App\Console\Commands\ELB;

use App\Models\Genre;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports genres from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Genre::on('elb')
            ->orderBy('id')
            ->chunk(1000, function (Collection $genres) {
                /** @var Genre $genre */
                foreach ($genres as $genre) {
                    try {
                        Genre::updateOrCreate([
                            'id' => $genre->id,
                        ], [
                            'id' => $genre->id,
                            'mal_id' => $genre->mal_id,
                            'tv_rating_id' => $genre->tv_rating_id,
                            'slug' => $genre->slug,
                            'name' => $genre->name,
                            'color' => $genre->color,
                            'description' => $genre->description,
                            'is_nsfw' => $genre->is_nsfw,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $genre->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $genre->id . PHP_EOL;
                }
            });

        return 0;
    }
}
