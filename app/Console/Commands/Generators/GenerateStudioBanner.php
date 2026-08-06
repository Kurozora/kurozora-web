<?php

namespace App\Console\Commands\Generators;

use App\Enums\MediaCollection;
use App\Enums\StudioType;
use App\Models\Studio;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateStudioBanner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:studio_banner 
                            {studioID? : The id of the studio. Accepts an array of comma separated IDs}
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

        Studio::withoutGlobalScopes()
            ->with([
                'media',
                'anime' => function ($query) {
                    $query->whereHas('media')
                        ->with([
                            'media',
                            'mediaStat'
                        ])
                        ->orderBy('rating_average', 'desc')
                        ->limit(10); // Max limit we're interested in
                },
                'games' => function ($query) {
                    $query->whereHas('media')
                        ->with([
                            'media',
                            'mediaStat'
                        ])
                        ->orderBy('rating_average', 'desc')
                        ->limit(10); // Max limit we're interested in
                },
                'manga' => function ($query) {
                    $query->whereHas('media')
                        ->with([
                            'media',
                            'mediaStat'
                        ])
                        ->orderBy('rating_average', 'desc')
                        ->limit(10); // Max limit we're interested in
                },
            ])
            ->withCount([
                'anime',
                'games',
                'manga',
            ])
            ->when(!$force, function ($query) {
                $query->whereDoesntHave('media', function ($query) {
                    $query->where('collection_name', '=', MediaCollection::Banner);
                });
            })
            ->whereIn('id', $studioIDs)
            ->chunkById(100, function (Collection $studios) {
                $studios->each(function (Studio $studio) {
                    // Determine the number of anime the studio has.
                    $modelCount = match ($studio->type) {
                        StudioType::Anime() => $this->allowedCount($studio->anime_count),
                        StudioType::Game() => $this->allowedCount($studio->games_count),
                        StudioType::Manga() => $this->allowedCount($studio->manga_count),
                        default => $this->fail('Incorrect studio type'),
                    };

                    if (empty($modelCount)) {
                        $this->info('Studio doesn’t have enough ' . $studio->type->key . '. Exiting...');
                        return;
                    }

                    // Get the image urls from the models
                    $models = match ($studio->type) {
                        StudioType::Anime() => $studio->anime,
                        StudioType::Game() => $studio->games,
                        StudioType::Manga() => $studio->manga,
                        default => $this->fail('Incorrect studio type'),
                    };
                    $images = collect($models)->pluck('poster_image_url')
                        ->filter(function ($value) {
                            return !empty($value);
                        });
                    $imagesCount = $this->allowedCount($images->count());

                    if (empty($imagesCount)) {
                        $this->info('Not enough images. Exiting...');
                        return;
                    }

                    // Create the image
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
                });
            });

        return Command::SUCCESS;
    }

    /**
     * Return an allowed count based on the given model count.
     *
     * @param null|int $modelCount
     *
     * @return null|int
     */
    private function allowedCount(?int $modelCount): ?int
    {
        if ($modelCount >= 10) {
            return 10;
        } else if ($modelCount >= 7) {
            return 7;
        } else if ($modelCount >= 4) {
            return 4;
        }

        return null;
    }
}
