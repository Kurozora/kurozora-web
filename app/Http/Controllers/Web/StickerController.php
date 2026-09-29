<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class StickerController extends Controller
{
    /**
     * The public path of the WhatsApp sticker pack.
     *
     * @var string WHATSAPP_PACK_PATH
     */
    const string WHATSAPP_PACK_PATH = 'stickers/whatsapp/kurochan';

    /**
     * Show the sticker packs.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        $manifestPath = public_path(self::WHATSAPP_PACK_PATH . '/manifest.json');
        $stickers = is_file($manifestPath)
            ? (json_decode((string) file_get_contents($manifestPath), true)['stickers'] ?? [])
            : [];

        return view('stickers.index', [
            'stickers' => $stickers,
            'stickerUrl' => fn (string $fileName): string => asset(self::WHATSAPP_PACK_PATH . '/' . $fileName),
        ]);
    }
}
