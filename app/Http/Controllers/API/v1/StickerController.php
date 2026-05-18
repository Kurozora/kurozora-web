<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StickerController extends Controller
{
    /**
     * Returns the Kuro-chan WhatsApp sticker pack as a pasteboard-ready JSON bundle.
     *
     * @return JsonResponse
     * @throws FileNotFoundException
     */
    public function whatsAppBundle(): JsonResponse
    {
        $packPath = $this->whatsAppPackPath();
        $manifestPath = $packPath . '/manifest.json';
        abort_unless(is_file($manifestPath), 404);

        // Get manifest
        $manifest = json_decode(File::get($manifestPath), true);

        if (!is_array($manifest)) {
            throw new HttpException(500, __('Sticker pack manifest is malformed.'));
        }

        // Get tray
        $trayPath = $packPath . '/' . ($manifest['tray_image_file'] ?? 'tray.png');

        if (!is_file($trayPath)) {
            throw new HttpException(500, __('Sticker pack tray image is missing.'));
        }

        // Get stickers
        $stickers = collect($manifest['stickers'] ?? [])
            ->map(function (array $sticker) use ($packPath): array {
                $assetPath = $packPath . '/' . ($sticker['image_file'] ?? '');

                if (!is_file($assetPath)) {
                    throw new HttpException(500, __('Sticker asset is missing.'));
                }

                return [
                    'image_data' => base64_encode(File::get($assetPath)),
                    'emojis' => $sticker['emojis'] ?? [],
                    'accessibility_text' => $sticker['accessibility_text'] ?? '',
                ];
            })
            ->values()
            ->all();

        // Bundle up
        $bundle = [
            'identifier' => $manifest['identifier'] ?? null,
            'name' => $manifest['name'] ?? null,
            'publisher' => $manifest['publisher'] ?? null,
            'publisher_email' => $manifest['publisher_email'] ?? null,
            'publisher_website' => $manifest['publisher_website'] ?? null,
            'privacy_policy_website' => $manifest['privacy_policy_website'] ?? null,
            'license_agreement_website' => $manifest['license_agreement_website'] ?? null,
            'tray_image' => base64_encode(File::get($trayPath)),
            'animated_sticker_pack' => $manifest['animated_sticker_pack'] ?? false,
            'ios_app_store_link' => $manifest['ios_app_store_link'] ?? null,
            'android_play_store_link' => $manifest['android_play_store_link'] ?? null,
            'stickers' => $stickers,
        ];

        return response()->json(array_filter($bundle, fn($value) => $value !== null));
    }

    /**
     * Returns the on-disk path to the Kuro-chan WhatsApp sticker pack.
     *
     * @return string
     */
    private function whatsAppPackPath(): string
    {
        return public_path('stickers/whatsapp/kurochan');
    }
}
