<?php

namespace App\Console\Commands;

use App\AnimeImages;
use ColorPalette;
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
     * @return mixed
     */
    public function handle()
    {
        // Request anime ID parameter
        $animeImageID = $this->argument('id');

        $animeImage = AnimeImages::find($animeImageID);

        // Specified anime image does not exists
        if($animeImage == null) {
            $this->error('The anime image was not found.');
            return false;
        }

        if($animeImage->background_color && !$this->option('force')) {
            $this->error('The colors were already generated for this anime image.');
            return false;
        }

        // Start generating
        $this->info('Start generating anime image colors...');

        // Set new data
        $colors = ColorPalette::getPalette($animeImage->url, 5, 1, null);

        for($i = 0; $i < count($colors); $i++) {
            switch ($i) {
                case 0:
                    $animeImage->background_color = $colors[$i];
                    break;
                case 1:
                    $animeImage->text_color_1 = $colors[$i];
                    break;
                case 2:
                    $animeImage->text_color_2 = $colors[$i];
                    break;
                case 3:
                    $animeImage->text_color_3 = $colors[$i];
                    break;
                case 4:
                    $animeImage->text_color_4 = $colors[$i];
                    break;
            }
        }

        // Save changes
        $animeImage->save();
        $this->info('Finished generating colors.');

        return true;
    }
}
