<?php

namespace App\Console\Commands\Generators;

use App\Jobs\ConvertImageToWebPJob;
use App\Jobs\GenerateImageAttributesJob;
use App\Models\Media;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;

class GenerateImageAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:image-attr
                            {id? : the id(s) of the image}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a given image’s attributes.';

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
        Pulse::stopRecording();
        Telescope::stopRecording();

        $count = Media::where('custom_properties', 'like', '[%]')
            ->orWhere('mime_type', 'not like', '%webp%')
            ->count();
        $bar = $this->output->createProgressBar($count);

        Media::where('custom_properties', 'like', '[%]')
            ->orWhere('mime_type', 'not like', '%webp%')
            ->chunkById(100, function ($medias) use ($bar) {
                $medias->each(function ($media) use ($bar) {
                    new GenerateImageAttributesJob($media)
                        ->handle();

                    if ($media->mime_type != 'image/webp') {
                        new ConvertImageToWebPJob($media)
                            ->handle();
                    }

                    $bar->advance();
                    usleep(300);
                });
            });

        $bar->finish();

        Pulse::startRecording();
        Telescope::startRecording();
        return Command::SUCCESS;
    }
}
