<main>
    <x-slot:title>
        {{ __(':x’s Game Library', ['x' => $user->username]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Join Kurozora and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s Game Library', ['x' => $user->username]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Join Kurozora and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <div class="flex flex-nowrap gap-4 justify-between text-center whitespace-nowrap overflow-x-scroll no-scrollbar">
            @foreach (\App\Enums\UserLibraryStatus::asGameSelectArray() as $key => $value)
                <button
                    class="pl-4 pr-4 pb-2 border-b-2 hover:border-tint"
                    :class="{'border-tint': '{{ strtolower($status) }}' === '{{ strtolower($value) }}', 'border-gray-300': '{{ strtolower($status) }}' !== '{{ strtolower($value) }}'}"
                    wire:click="$set('status', '{{ strtolower($value) }}')"
                    data-toggle="tab"
                >{{ __($value) }}</button>
            @endforeach
        </div>

        <section class="mt-8" wire:init="loadPage">
            <section>
                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomGame">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random game from ' . strtolower($status) . ' library', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </section>

            @if (!empty($this->searchResults))
                @if (!empty($this->searchResults->total()))
                    <section class="mt-4" wire:key="not-empty-{{ strtolower($status) }}">
                        <x-rows.small-lockup :games="$this->searchResults" :is-row="false" />

                        <div class="mt-4">
                            {{ $this->searchResults->links() }}
                        </div>
                    </section>
                @else
                    <section class="flex flex-col items-center mt-4 text-center" wire:key="empty-{{ strtolower($status) }}">
                        <x-picture>
                            <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_game_library.webp') }}" alt="Empty Library" title="Empty Library">
                        </x-picture>

                        <p class="font-bold">{{ __('No Games') }}</p>

                        <p class="text-sm text-secondary">{{ __('Add a game to your :x list and it will show up here.', ['x' => strtolower($status)]) }}</p>
                    </section>
                @endif
            @elseif (!$readyToLoad)
                <section class="mt-4 pt-5 pb-8 border-t-2 border-primary">
                    <div class="flex gap-4 justify-between flex-wrap">
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                        <div class="bg-secondary w-64 rounded-md md:w-80 flex-grow" style="height: 168px;"></div>
                        <div class="w-64 md:w-80 flex-grow"></div>
                        <div class="w-64 md:w-80 flex-grow"></div>
                    </div>
                </section>
            @endif
        </section>
    </div>
</main>
