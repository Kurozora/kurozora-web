<main>
    <x-slot:title>
        {{ __('Tip Jar') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('We rely on your support to develop :x. If you find it to be useful to you, please consider supporting us by leaving a tip in the :x Tip Jar. We would like to keep working on and improving :x, so any amount is incredibly appreciated. Please know that even if you don’t tip we’re still grateful that you use this app.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Tip Jar') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('We rely on your support to develop :x. If you find it to be useful to you, please consider supporting us by leaving a tip in the :x Tip Jar. We would like to keep working on and improving :x, so any amount is incredibly appreciated. Please know that even if you don’t tip we’re still grateful that you use this app.', ['x' => config('app.name')]) }}" />
        <link rel="canonical" href="{{ route('tip-jar') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        tip-jar
    </x-slot:appArgument>

    <div class="pt-4 pb-6">
        <section class="xl:safe-area-inset">
            <div class="pl-4 pr-4">
                <x-purchase.header
                    :primary="__(':x Tip Jar', ['x' => config('app.name')])"
                    :secondary="$this->headerSecondary"
                />

                <p class="pb-6 text-sm text-secondary text-center max-w-2xl mx-auto">
                    {{ __('Purchases are made in the :x app.', ['x' => config('app.name')]) }}
                    <x-simple-link href="{{ config('app.ios.store_url') }}" target="_blank">{{ __('Get the app') }}</x-simple-link>
                </p>
            </div>
        </section>

        <section class="xl:safe-area-inset">
            <div class="flex flex-col gap-2 pl-4 pr-4">
                @foreach ($this->tipRows as $tipRow)
                    <x-purchase.button-row
                        :emoji="$tipRow['emoji']"
                        :primary="$tipRow['name']"
                        :secondary="$tipRow['secondary']"
                    >
                        <x-link-button href="{{ config('app.ios.store_url') }}" target="_blank">{{ $tipRow['buttonLabel'] }}</x-link-button>
                    </x-purchase.button-row>
                @endforeach
            </div>
        </section>

        <section class="pt-6 xl:safe-area-inset">
            <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
                @foreach ($this->features as $productFeature)
                    <x-purchase.feature-card
                        class="w-64 md:w-80 flex-grow"
                        :image="$productFeature['image']"
                        :title="$productFeature['title']"
                        :description="$productFeature['description']"
                    />
                @endforeach

                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
                <div class="w-64 md:w-80 flex-grow"></div>
            </div>
        </section>

        <section class="xl:safe-area-inset">
            <div class="pl-4 pr-4">
                <x-purchase.footer>
                    <x-link-button class="whitespace-nowrap" href="{{ config('app.ios.store_url') }}" target="_blank">
                        {{ __('Restore Purchase') }}
                    </x-link-button>
                </x-purchase.footer>
            </div>
        </section>
    </div>
</main>
