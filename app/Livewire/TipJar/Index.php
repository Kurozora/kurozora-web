<?php

namespace App\Livewire\TipJar;

use App\Enums\StoreProductType;
use App\Models\StoreProduct;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    /**
     * The active tip products, cheapest first.
     *
     * @return Collection
     */
    public function getTipProductsProperty(): Collection
    {
        return StoreProduct::where('is_active', true)
            ->where('type', StoreProductType::Consumable)
            ->orderBy('price_usd_milliunits')
            ->get();
    }

    /**
     * The tip rows ready for display, cheapest first.
     *
     * @return array
     */
    public function getTipRowsProperty(): array
    {
        return $this->tipProducts
            ->map(fn (StoreProduct $storeProduct) => [
                'emoji' => $this->emojiFor($storeProduct),
                'name' => $storeProduct->name,
                'secondary' => $this->descriptionFor($storeProduct),
                'buttonLabel' => '$' . number_format($storeProduct->price_usd_milliunits / 1000, 2),
            ])
            ->all();
    }

    /**
     * The introduction text of the tip jar.
     *
     * @return string
     */
    public function getHeaderSecondaryProperty(): string
    {
        return implode("\n\n", [
            __('The :x app is built by a small team—me and my little sister! We may be small, but we’re mighty, and we pour our hearts into making this app as useful and delightful as possible.', ['x' => config('app.name')]),
            __('We rely on your support to develop :x. If you find it to be useful to you, please consider supporting us by leaving a tip in the :x Tip Jar. We would like to keep working on and improving :x, so any amount is incredibly appreciated. Please know that even if you don’t tip we’re still grateful that you use this app.', ['x' => config('app.name')]),
            __('Any amount unlocks Pro 🚀'),
        ]);
    }

    /**
     * The Pro features ready for display.
     *
     * @return array
     */
    public function getFeaturesProperty(): array
    {
        return [
            [
                'image' => asset('images/static/in-app_purchases/icons.png'),
                'title' => __('Stylish App Icons'),
                'description' => __('Make your home screen stand out with premium and limited time app icons.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/chimes.jpg'),
                'title' => __('Startup Chimes'),
                'description' => __('Immerse yourself in the world of anime from the very start with serene chimes and iconic anime sounds.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/gifs.png'),
                'title' => __('Get Animated'),
                'description' => __('Upgrade your profile with a gif image that captures your unique style.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/username.jpg'),
                'title' => __('Change Your Identity'),
                'description' => __('Switch things up every now an then with a fresh username that truly represents you.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/character_count_500.jpg'),
                'title' => __('Up to :x Characters', ['x' => 500]),
                'description' => __('Have more to say? Express yourself fully with a 500 character limit for your feed messages.'),
            ],
            [
                'image' => asset('images/static/in-app_purchases/pro_badge.jpg'),
                'title' => __('Unlock Pro Badge'),
                'description' => __('Elevate your status in the :x community with the prestigious Pro badge next to your username, and show your support for :x.', ['x' => config('app.name')]),
            ],
            [
                'image' => asset('images/static/in-app_purchases/support.jpg'),
                'title' => __('Support the Community'),
                'description' => __('Your contribution helps with maintaining the servers, paying for software licenses, and fund events and activities.'),
            ],
        ];
    }

    /**
     * The emoji of the tip product.
     *
     * @param StoreProduct $storeProduct
     *
     * @return string
     */
    protected function emojiFor(StoreProduct $storeProduct): string
    {
        return match ($this->tipKeyFor($storeProduct)) {
            'wolfTip' => '🐺',
            'tigerTip' => '🐯',
            'demonTip' => '👺',
            'dragonTip' => '🐲',
            'godTip' => '🙏',
            'extraterrestrialTip' => '👽',
            'eternalTip' => '♾️',
            default => '💛',
        };
    }

    /**
     * The description of the tip product.
     *
     * @param StoreProduct $storeProduct
     *
     * @return ?string
     */
    protected function descriptionFor(StoreProduct $storeProduct): ?string
    {
        return match ($this->tipKeyFor($storeProduct)) {
            'wolfTip' => __('A tip with unknown impact.'),
            'tigerTip' => __('A tip affecting a large number of people.'),
            'demonTip' => __('A tip endangering a city and its people.'),
            'dragonTip' => __('A tip affecting multiple cities.'),
            'godTip' => __('A tip affecting humanity’s survival.'),
            'extraterrestrialTip' => __('A tip affecting the universe’s survival.'),
            'eternalTip' => __('A tip affecting the multiverse’s survival.'),
            default => null,
        };
    }

    /**
     * The trailing identifier of the tip product.
     *
     * @param StoreProduct $storeProduct
     *
     * @return string
     */
    protected function tipKeyFor(StoreProduct $storeProduct): string
    {
        return (string) str($storeProduct->product_id)->afterLast('.');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.tip-jar.index');
    }
}
