<?php

namespace App\Console\Commands\Generators;

use App\Models\Media;
use App\Support\Media\ImageAttributeGenerator;
use App\Support\Media\ImageUploadTransformer;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Log;
use Pulse;
use Storage;
use Throwable;

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
    protected $description = 'Generate image attributes and convert stored images to WebP.';

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

        $transformer = new ImageUploadTransformer;
        $attributeGenerator = new ImageAttributeGenerator;

        $count = Media::where('custom_properties', 'like', '[%]')
            ->orWhere('mime_type', 'not like', '%webp%')
            ->count();
        $bar = $this->output->createProgressBar($count);

        Media::where('custom_properties', 'like', '[%]')
            ->orWhere('mime_type', 'not like', '%webp%')
            ->chunkById(100, function ($medias) use ($bar, $transformer, $attributeGenerator) {
                $medias->each(function ($media) use ($bar, $transformer, $attributeGenerator) {
                    try {
                        $this->process($media, $transformer, $attributeGenerator);
                    } catch (Throwable $throwable) {
                        Log::warning('Image backfill failed.', [
                            'media_id' => $media->id,
                            'error' => $throwable->getMessage(),
                        ]);
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

    /**
     * Converts the given media's stored file to WEBP and refreshes its generated attributes.
     *
     * @param Media $media
     * @param ImageUploadTransformer $transformer
     * @param ImageAttributeGenerator $attributeGenerator
     * @return void
     */
    protected function process(Media $media, ImageUploadTransformer $transformer, ImageAttributeGenerator $attributeGenerator): void
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'media-backfill');

        try {
            file_put_contents($temporaryPath, Storage::disk($media->disk)->get($media->getPathRelativeToRoot()));

            $updates = [];

            if ($transformer->transform($temporaryPath, $media->collection_name)) {
                Storage::disk($media->disk)->put($media->getPathRelativeToRoot(), file_get_contents($temporaryPath));

                $updates['file_name'] = ImageUploadTransformer::webPFileName($media->file_name);
                $updates['mime_type'] = 'image/webp';
                $updates['size'] = filesize($temporaryPath);
            }

            if (ImageUploadTransformer::isEligibleImage($temporaryPath)) {
                $colors = $attributeGenerator->colorsFor($temporaryPath);
                $dimensions = $attributeGenerator->dimensionsFor($temporaryPath);

                $updates['custom_properties'] = array_merge($colors, $media->custom_properties, $dimensions);
            }

            if ($updates !== []) {
                $media->update($updates);
            }
        } finally {
            @unlink($temporaryPath);
        }
    }
}
