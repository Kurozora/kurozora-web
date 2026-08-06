<?php

namespace App\Livewire\Stickers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Index extends Component
{
    /**
     * The relative path to the WhatsApp sticker pack directory.
     *
     * @var string
     */
    protected string $whatsAppPackPath = 'stickers/whatsapp/kurochan';

    /**
     * The sticker entries decoded from the WhatsApp pack manifest.
     *
     * @var array<int, array{image_file: string, emojis: array, accessibility_text: string}>
     */
    #[Locked]
    public array $stickers = [];

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->stickers = $this->loadStickers();
    }

    /**
     * Load and decode the WhatsApp sticker pack manifest.
     *
     * @return array
     */
    protected function loadStickers(): array
    {
        $manifestPath = public_path($this->whatsAppPackPath . '/manifest.json');

        if (!is_file($manifestPath)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($manifestPath), true);

        return $decoded['stickers'] ?? [];
    }

    /**
     * Build the public URL for a sticker file inside the WhatsApp pack.
     *
     * @param string $fileName
     *
     * @return string
     */
    public function stickerUrl(string $fileName): string
    {
        return asset($this->whatsAppPackPath . '/' . $fileName);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.stickers.index');
    }
}
