<?php

namespace App\Console\Commands;

use App\Models\AnimeImages;
use Illuminate\Console\Command;

class GenerateColorsFromImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'anime_images:generate_colors {id : The ID of the anime image} {--f|force : Force generate colors if already generated before}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates background and text colors for the given anime image.';

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
    public function handle()
    {
        // Request anime ID parameter
        $animeImageID = $this->argument('id');

        $animeImage = AnimeImages::find($animeImageID);

        // Specified anime image does not exists
        if($animeImage == null) {
            $this->error('The anime image was not found.');
            return 0;
        }

        if($animeImage->background_color && !$this->option('force')) {
            $this->error('The colors were already generated for this anime image.');
            return 0;
        }

        // Start generating
        $this->info('Start generating anime image colors...');

        // Set new data
        AnimeImages::generateColorsFor($animeImage);

        // Save changes
        $animeImage->save();
        $this->info('Finished generating colors.');

        return 1;
    }
}
