<?php

namespace App\Console\Commands\Generators;

use App\Enums\MediaCollection;
use App\Models\MediaStat;
use App\Models\Studio;
use Illuminate\Console\Command;

class GenerateStudioBanner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:studio_banner 
                            {studioID? : The id of the studio. Accepts an array of comma seperated IDs}
                            {--f|force : Force generating banner evn if it already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate studio banner.';

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
        $studioIDs = $this->argument('studioID');
        $force = $this->option('force');

        if (empty($studioIDs)) {
            $studioIDs = $this->ask('Studio ID');
        }

        $studioIDs = explode(',', $studioIDs);

        if (empty($studioIDs)) {
            $this->info('ID is empty. Exiting...');
            return Command::INVALID;
        }

        foreach ($studioIDs as $studioID) {
            $studio = Studio::withoutGlobalScopes()
                ->firstWhere('id', '=', $studioID);

            if (empty($studio)) {
                $this->info('Studio not found. Exiting...');
                return Command::INVALID;
            }

            if (empty($studio->getFirstMediaFullUrl(MediaCollection::Banner())) || $force) {
                // Determine the number of anime the studio has.
                $animeCount = $studio->anime()->count();

                if ($animeCount >= 10) {
                    $animeCount = 10;
                } else if ($animeCount >= 7) {
                    $animeCount = 7;
                } else if ($animeCount >= 4) {
                    $animeCount = 4;
                } else {
                    continue;
                }

                if (empty($animeCount)) {
                    $this->info('Studio has no anime. Exiting...');
                    continue;
                }

                // Get anime belonging to the studio
                // Sort by highest average rating
                // Grab the top `n` anime
                $studio->load(['anime.mediaStat' => function ($q) use ($animeCount, $studio) {
                    /** @var MediaStat[] $stats */
                    $stats = $q->orderBy('rating_average', 'desc')->limit($animeCount)->get();
                    $anime = [];

                    foreach ($stats as $stat) {
                        $anime[] = $stat->model;
                    }

                    // Get the image urls from the anime
                    $images = collect($anime)->pluck('poster_image_url')
                        ->filter(function ($value) {
                            return !empty($value);
                        });

                    // Create the image
                    if ($images->count() == $animeCount) {
                        $absoluteFilePath = getcwd() . '/storage/app/banners/' . $studio->id . '.webp';

                        $this->info('Creating file...');
                        $imageURL = create_studio_banner_from($images, $absoluteFilePath);

                        $this->info('Updating studio banner...');
                        $studio->updateImageMedia(MediaCollection::Banner(), $imageURL, null, [], 'webp');

                        if (file_exists($absoluteFilePath)) {
                            $this->info('Cleaning up files.');

                            if (unlink($absoluteFilePath)) {
                                $this->info('File has been deleted.');
                            } else {
                                $this->info('There was an error deleting the file: ' . $absoluteFilePath);
                            }
                        } else {
                            $this->info('Original file has ben deleted.');
                        }
                    }
                }]);
            }
        }

        return Command::SUCCESS;
    }
}
