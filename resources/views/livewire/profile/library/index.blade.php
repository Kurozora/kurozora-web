<main>
    <x-slot:title>
        {{ __(':x’s :y', ['x' => $user->username, 'y' => $this->titleSuffix]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Join :x and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s :y', ['x' => $user->username, 'y' => $this->titleSuffix]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Join :x and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="pt-4 pb-6">
        <div class="xl:safe-area-inset">
            <div class="flex flex-nowrap gap-4 justify-between pl-4 pr-4 text-center whitespace-nowrap overflow-x-scroll no-scrollbar">
                @foreach ($this->statusSelectArray as $key => $value)
                    <button
                        class="pl-4 pr-4 pb-2 border-b hover:border-tint"
                        :class="{'border-tint': '{{ strtolower($status) }}' === '{{ strtolower($value) }}', 'border-primary': '{{ strtolower($status) }}' !== '{{ strtolower($value) }}'}"
                        wire:click="$set('status', '{{ strtolower($value) }}')"
                        data-toggle="tab"
                    >{{ __($value) }}</button>
                @endforeach
            </div>
        </div>

        <div class="mt-8" wire:init="loadPage">
            <section class="xl:safe-area-inset">
                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomItem">
                            @svg('dice', 'fill-current', ['aria-labelledby' => $this->randomLabel, 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </section>

            @if (!empty($this->searchResults))
                @if (!empty($this->searchResults->total()))
                    <section class="mt-4 xl:safe-area-inset" wire:key="not-empty-{{ strtolower($status) }}">
                        @switch ($kind)
                            @case (\App\Enums\UserLibraryKind::Anime)
                                <x-rows.small-lockup :animes="$this->searchResults" :is-row="false" />
                                @break
                            @case (\App\Enums\UserLibraryKind::Manga)
                                <x-rows.small-lockup :mangas="$this->searchResults" :is-row="false" />
                                @break
                            @case (\App\Enums\UserLibraryKind::Game)
                                <x-rows.small-lockup :games="$this->searchResults" :is-row="false" />
                                @break
                        @endswitch

                        <div class="mt-4 pl-4 pr-4">
                            {{ $this->searchResults->links() }}
                        </div>
                    </section>
                @else
                    <x-empty-state wire:key="empty-{{ strtolower($status) }}" :image="$this->emptyImage" alt="Empty Library" :heading="$this->emptyHeading" :description="$this->emptyDescription" />
                @endif
            @elseif (!$readyToLoad)
                <section class="mt-4 pb-8 xl:safe-area-inset">
                    <x-skeletons.lockup-row class="pt-4" :kind="$kind" :is-row="false" :count="9" />
                </section>
            @endif
        </div>
    </div>
</main>
