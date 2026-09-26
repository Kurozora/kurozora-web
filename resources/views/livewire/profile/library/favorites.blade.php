<main>
    <x-slot:title>
        {{ $this->title }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Join :x and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ $this->title }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Join :x and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <div class="pt-4 pb-6" wire:init="loadPage">
        <section class="mb-4 xl:safe-area-inset">
            <div>
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ $this->title }}</h1>
                    </div>

                    <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                    </div>
                </div>

                <x-search-bar>
                    <x-slot:rightBarButtonItems>
                        <x-square-button wire:click="randomItem">
                            @svg('dice', 'fill-current', ['aria-labelledby' => $this->randomLabel, 'width' => '28'])
                        </x-square-button>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>
            </div>
        </section>

        @if ($this->searchResults->count())
            <section class="mt-4 xl:safe-area-inset">
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
        @elseif (!$readyToLoad)
            <section class="xl:safe-area-inset">
                <x-skeletons.lockup-row :kind="$kind" :is-row="false" />
            </section>
        @else
            <x-empty-state :image="$this->emptyImage" :heading="$this->emptyHeading" :description="$this->emptyDescription" />
        @endif
    </div>
</main>
