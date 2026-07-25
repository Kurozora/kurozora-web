<?php

namespace App\Support\Media;

use App\Enums\MediaCollection;
use Log;
use Process;
use Spatie\MediaLibrary\Support\File;

class ImageUploadTransformer
{
    /**
     * Mime types eligible for pre-upload conversion.
     *
     * @var array<int, string>
     */
    private const array ELIGIBLE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    /**
     * Target encoded bytes per output pixel.
     *
     * @var float
     */
    private const float TARGET_BYTES_PER_PIXEL = 0.082;

    /**
     * The minimum byte budget for an encoded image.
     *
     * @var int
     */
    private const int MINIMUM_TARGET_BYTES = 24000;

    /**
     * Fit-within, never-upscale dimension caps keyed by media collection name.
     *
     * @var array<string, array{width: int, height: int}>
     */
    private const array DIMENSION_CAPS = [
        MediaCollection::Poster => ['width' => 1000, 'height' => 1500],
        MediaCollection::Banner => ['width' => 1920, 'height' => 1920],
        MediaCollection::Profile => ['width' => 400, 'height' => 400],
    ];

    /**
     * Determines whether the file at the given path is eligible for pre-upload processing.
     *
     * @param string $filePath
     * @return bool
     */
    public static function isEligibleImage(string $filePath): bool
    {
        return is_file($filePath) && in_array(File::getMimeType($filePath), self::ELIGIBLE_MIME_TYPES, true);
    }

    /**
     * Converts the local file at the given path to WebP in place.
     *
     * @param string $filePath
     * @param string $collectionName
     * @return bool
     */
    public function transform(string $filePath, string $collectionName): bool
    {
        if (!self::isEligibleImage($filePath)) {
            return false;
        }

        $sourceDimensions = getimagesize($filePath);

        if ($sourceDimensions === false) {
            return false;
        }

        [$targetWidth, $targetHeight] = $this->fitWithinCap($sourceDimensions[0], $sourceDimensions[1], $collectionName);
        $targetBytes = max(self::MINIMUM_TARGET_BYTES, (int) round($targetWidth * $targetHeight * self::TARGET_BYTES_PER_PIXEL));
        $originalBytes = filesize($filePath);
        $convertedPath = $filePath . '.webp';

        $command = ['cwebp', '-quiet', '-size', (string) $targetBytes, '-pass', '10', '-m', '6', '-sharp_yuv'];

        if ($targetWidth !== $sourceDimensions[0] || $targetHeight !== $sourceDimensions[1]) {
            array_push($command, '-resize', (string) $targetWidth, (string) $targetHeight);
        }

        array_push($command, $filePath, '-o', $convertedPath);

        $conversion = Process::run($command);

        if (!$conversion->successful() || !is_file($convertedPath)) {
            Log::warning('Image upload conversion failed.', ['error' => trim($conversion->errorOutput())]);

            @unlink($convertedPath);

            return false;
        }

        if (filesize($convertedPath) >= $originalBytes) {
            unlink($convertedPath);

            return false;
        }

        rename($convertedPath, $filePath);

        return true;
    }

    /**
     * Replaces the given file name's extension with `.webp`.
     *
     * @param string $fileName
     * @return string
     */
    public static function webPFileName(string $fileName): string
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        return $extension === '' ? "{$fileName}.webp" : substr($fileName, 0, -strlen($extension)) . 'webp';
    }

    /**
     * Scales the given dimensions down to the collection's cap, preserving aspect ratio.
     *
     * @param int $width
     * @param int $height
     * @param string $collectionName
     * @return array{0: int, 1: int}
     */
    protected function fitWithinCap(int $width, int $height, string $collectionName): array
    {
        $dimensionCap = self::DIMENSION_CAPS[$collectionName] ?? null;

        if ($dimensionCap === null) {
            return [$width, $height];
        }

        $scale = min($dimensionCap['width'] / $width, $dimensionCap['height'] / $height, 1.0);

        return [max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale))];
    }
}
