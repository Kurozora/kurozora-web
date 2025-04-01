<main>
    <x-slot:title>
        {{ __(':x’s Manga Library', ['x' => $user->username]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Join :x and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s Manga Library', ['x' => $user->username]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Join :x and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="pt-4 pb-6">
        <div class="flex flex-nowrap gap-4 justify-between pl-4 pr-4 text-center whitespace-nowrap overflow-x-scroll no-scrollbar">
            @foreach (\App\Enums\UserLibraryStatus::asMangaSelectArray() as $key => $value)
                <button
                    class="pl-4 pr-4 pb-2 border-b hover:border-tint"
                    :class="{'border-tint': '{{ strtolower($status) }}' === '{{ strtolower($value) }}', 'border-primary': '{{ strtolower($status) }}' !== '{{ strtolower($value) }}'}"
                    wire:click="$set('status', '{{ strtolower($value) }}')"
                    data-toggle="tab"
                >{{ __($value) }}</button>
            @endforeach
        </div>

        <div class="mt-8" wire:init="loadPage">
            <section>
                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomManga">
                            @svg('dice', 'fill-current', ['aria-labelledby' => 'random manga from ' . strtolower($status) . ' library', 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </section>

            @if (!empty($this->searchResults))
                @if (!empty($this->searchResults->total()))
                    <section class="mt-4" wire:key="not-empty-{{ strtolower($status) }}">
                        <x-rows.small-lockup :mangas="$this->searchResults" :is-row="false" />

                        <div class="mt-4 pl-4 pr-4">
                            {{ $this->searchResults->links() }}
                        </div>
                    </section>
                @else
                    <section class="flex flex-col items-center mt-4 text-center" wire:key="empty-{{ strtolower($status) }}">
                        <x-picture>
                            <img class="w-full max-w-sm" src="{{ asset('images/static/placeholders/empty_manga_library.webp') }}" alt="Empty Library" title="Empty Library">
                        </x-picture>

                        <p class="font-bold">{{ __('No Manga') }}</p>

                        <p class="text-sm text-secondary">{{ __('Add a manga to your :x list and it will show up here.', ['x' => strtolower($status)]) }}</p>
                    </section>
                @endif
            @elseif (!$readyToLoad)
                <section class="mt-4 pb-8">
                    <x-hr class="ml-4 mr-4 pb-5" />

                    <div class="flex flex-wrap gap-4 justify-between pl-4 pr-4">
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
        </div>
    </div>
</main>
