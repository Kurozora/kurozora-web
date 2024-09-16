<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Storage;

class ConvertImageToWebPJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The object containing the media data.
     *
     * @var Media
     */
    protected Media $media;

    /**
     * Create a new job instance.
     *
     * @var Media $media
     * @return void
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // Get the old paths, and prepare the new paths.
        $oldImageFullPath = $this->media->getFullUrl();
        $oldExtension = pathinfo($oldImageFullPath, PATHINFO_EXTENSION);
        $newImageFullPath = str($oldImageFullPath)->replaceLast('.' . $oldExtension, '.webp');
        $newImageName = pathinfo($newImageFullPath, PATHINFO_FILENAME) . '.webp';

        // Convert image to WebP
        /**
         * If file size is bigger than 0.5 MB in bytes, compress with a lower quality.
         * The image still looks good but the filesize will be smaller.
         * For smaller sized images we use a higher quality, so we don't lose the last two pixels.
         */
        $quality = $this->media->size > 500000 ? '25' : '75';
        $image = Image::make($oldImageFullPath);
        $image->encode('webp', $quality);

        // Save media to storage
        /**
         * Since the file is renamed on disk when changing `file_name`, this gives an error when a file with the same name exists.
         * So we keep the old name and extension, and we let Media-Library rename the file to "xxx.webp" when we change `file_name`.
         * In the end we save a "webp" encoded image with the extension of the old image.
         * Hacky? Yes, fuck Media-Library for not making file/disk syncing optional.
         */
        $imageFolder = $this->media->id;
        $oldImageName = $this->media->file_name;
        $oldFilePath = $imageFolder . '/' . $oldImageName;

        if (config('filesystems.default') != 's3') {
            Storage::put($oldFilePath, $image);
        } else {
            Storage::disk('s3')->put($oldFilePath, $image);
        }

        // Update media data
        $this->media->update([
            'file_name' => $newImageName,
            'mime_type' => $image->mime,
            'size' => Storage::size($oldFilePath) // Since "xxx.webp" doesn't exist yet. Fucking mental gymnastics.
        ]);

        // Remove conversions folder when local
        if (config('filesystems.default') != 's3') {
            /**
             * Unnecessary folder created by Media-Library when renaming the file. Ffs.
             */
            Storage::deleteDirectory($imageFolder . '/conversions');
        }
    }
}
