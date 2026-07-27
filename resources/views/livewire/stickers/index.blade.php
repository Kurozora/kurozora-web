<main>
    <x-slot:title>
        {{ __('Stickers') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Add Kuro-chan stickers to Telegram, Signal, and WhatsApp from :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Stickers') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Add Kuro-chan stickers to Telegram, Signal, and WhatsApp from :x, the largest, free online anime, manga, game & music database in the world.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('stickers/whatsapp/kurochan/tray.png') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('stickers.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        stickers
    </x-slot:appArgument>

    <div class="pt-4 pb-6">
        <section class="mb-4 xl:safe-area-inset">
            <div class="flex gap-1 pl-4 pr-4">
                <div class="flex flex-wrap items-center w-full">
                    <h1 class="text-2xl font-bold">{{ __('Stickers') }}</h1>
                </div>
            </div>
        </section>

        <section class="mb-4 xl:safe-area-inset">
            <div class="pl-4 pr-4">
                <h2 class="text-lg font-bold uppercase">{{ __('Add to Messaging App') }}</h2>
            </div>

            <div class="flex flex-wrap gap-4 pl-4 pr-4">
                <a
                    class="relative flex flex-col items-center gap-2 pt-2 pb-2 pl-2 pr-2 rounded-xl no-external-icon hover:bg-tertiary"
                    style="width: 128px"
                    href="{{ config('app.ios.messages_store_url') }}"
                    target="_blank"
                    rel="noopener"
                    title="{{ __('Add to Messages') }}"
                >
                    <img src="{{ asset('images/brands/messages.webp') }}" class="w-16 h-16 rounded-xl" alt="{{ __('Messages') }}">
                    <p class="text-center leading-tight line-clamp-2">{{ __('Messages') }}</p>
                </a>

                <a
                    class="relative flex flex-col items-center gap-2 pt-2 pb-2 pl-2 pr-2 rounded-xl no-external-icon hover:bg-tertiary"
                    style="width: 128px"
                    href="https://signal.art/addstickers/#pack_id=a132f9a6b200d8978a5f5396decefdde&pack_key=db8680ea74e6f0fcb294bbad8dee75b9f27735a3cf81eb98f9c362a322df3177"
                    target="_blank"
                    rel="noopener"
                    title="{{ __('Add to Signal') }}"
                >
                    <img src="{{ asset('stickers/signal/icon.png') }}" class="w-16 h-16 rounded-xl" alt="{{ __('Signal') }}">
                    <p class="text-center leading-tight line-clamp-2">{{ __('Signal') }}</p>
                </a>

                <a
                    class="relative flex flex-col items-center gap-2 pt-2 pb-2 pl-2 pr-2 rounded-xl no-external-icon hover:bg-tertiary"
                    style="width: 128px"
                    href="https://t.me/addstickers/KuroChanVT"
                    target="_blank"
                    rel="noopener"
                    title="{{ __('Add to Telegram') }}"
                >
                    <img src="{{ asset('stickers/telegram/icon.png') }}" class="w-16 h-16 rounded-xl" alt="{{ __('Telegram') }}">
                    <p class="text-center leading-tight line-clamp-2">{{ __('Telegram') }}</p>
                </a>

                <a
                    class="relative flex flex-col items-center gap-2 pt-2 pb-2 pl-2 pr-2 rounded-xl no-external-icon hover:bg-tertiary"
                    style="width: 128px"
                    href="kurozora://stickers/whatsapp"
                    title="{{ __('Add to WhatsApp') }}"
                >
                    <img src="{{ asset('stickers/whatsapp/kurochan/tray.png') }}" class="w-16 h-16 rounded-xl" alt="{{ __('WhatsApp') }}">
                    <p class="text-center leading-tight line-clamp-2">{{ __('WhatsApp') }}</p>
                </a>
            </div>

            <div class="mt-2 pl-4 pr-4">
                <p class="text-sm text-secondary">
                    {{ __('Adding to WhatsApp requires the :x iOS app.', ['x' => config('app.name')]) }}
                    <x-link target="_blank" href="{{ config('app.ios.store_url') }}">{{ __('Get it on the App Store') }}</x-link>
                </p>
            </div>
        </section>

        @if (count($stickers))
            <section class="xl:safe-area-inset">
                <div class="pl-4 pr-4">
                    <h2 class="text-lg font-bold uppercase">{{ __('Preview') }}</h2>
                </div>

                <div class="flex flex-wrap gap-4 pl-4 pr-4">
                    @foreach ($stickers as $sticker)
                        <div
                            class="relative flex flex-col items-center gap-2 pt-2 pb-2 pl-2 pr-2 rounded-xl hover:bg-tertiary"
                            style="width: 128px"
                            title="{{ $sticker['accessibility_text'] ?? '' }}"
                        >
                            <img
                                src="{{ $this->stickerUrl($sticker['image_file']) }}"
                                class="w-24 h-24"
                                alt="{{ $sticker['accessibility_text'] ?? '' }}"
                                loading="lazy"
                            >

                            <p class="text-center leading-tight line-clamp-1 text-sm">{{ collect($sticker['emojis'] ?? [])->implode(' ') }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</main>
