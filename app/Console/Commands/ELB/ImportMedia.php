<?php

namespace App\Console\Commands\ELB;

use App\Models\Media;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ImportMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:elb_media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports media from the ELB database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Media::on('elb')
            ->orderBy('id')
            ->where('id', '>=', 23718)
            ->chunk(1000, function (Collection $medias) {
                /** @var Media $media */
                foreach ($medias as $media) {
                    try {
                        print 'Added: ' . $media->id . PHP_EOL;
                        // Remove pain in the ass observer
                        $eventDispatcher = Media::getEventDispatcher();
                        $eventDispatcher->forget('eloquent.updating: App\Models\Media');

                        Media::updateOrCreate([
                            'id' => $media->id,
                            'model_id' => $media->model_id,
                            'model_type' => $media->model_type,
                            'uuid' => $media->uuid,
                        ], [
                            'id' => $media->id,
                            'model_id' => $media->model_id,
                            'model_type' => $media->model_type,
                            'uuid' => $media->uuid,
                            'collection_name' => $media->collection_name,
                            'name' => $media->name,
                            'file_name' => $media->file_name,
                            'mime_type' => $media->mime_type,
                            'disk' => 'public', //$media->disk,
                            'conversions_disk' => 'public', //$media->conversions_disk,
                            'size' => $media->size,
                            'manipulations' => $media->manipulations,
                            'custom_properties' => $media->custom_properties,
                            'generated_conversions' => $media->generated_conversions,
                            'responsive_images' => $media->responsive_images,
                            'order_column' => $media->order_column,
                        ]);
                    } catch (Exception $exception) {
                        print 'Exception at: ' . $media->id . PHP_EOL;
                        print $exception->getMessage() . PHP_EOL;
                    }

                    print 'Added: ' . $media->id . PHP_EOL;
                }
            });

        return Command::SUCCESS;
    }
}
