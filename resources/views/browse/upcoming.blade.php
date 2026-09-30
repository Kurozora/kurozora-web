<x-base-layout>
    <x-slot:title>
        {{ __('Upcoming') }} | {{ $noun }}
    </x-slot:title>

    <x-slot:description>
        {{ $description }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Upcoming') }} | {{ $noun }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ $description }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ $canonicalUrl }}">
    </x-slot:meta>

    <main>
        <div class="pt-4 pb-6" data-paginated="catalog">
            <section class="mb-4 xl:safe-area-inset">
                <div>
                    <div class="flex gap-1 pl-4 pr-4">
                        <div class="flex flex-wrap items-center w-full">
                            <h1 class="text-2xl font-bold">{{ $heading }}</h1>
                        </div>

                        <div class="flex flex-wrap flex-1 justify-end items-center w-full">
                        </div>
                    </div>

                    <x-search-bar :criteria="$criteria" :action="$canonicalUrl">
                        <x-slot:rightBarButtonItems>
                            <x-square-link href="{{ $randomUrl }}" wire:navigate>
                                @svg('dice', 'fill-current', ['aria-labelledby' => $randomLabel, 'width' => '28'])
                            </x-square-link>
                        </x-slot:rightBarButtonItems>
                    </x-search-bar>
                </div>
            </section>

            @if ($results->count())
                <section class="mt-4 xl:safe-area-inset">
                    @switch ($kind)
                        @case (\App\Enums\UserLibraryKind::Anime)
                            <x-rows.small-lockup :animes="$results" :is-row="false" />
                            @break
                        @case (\App\Enums\UserLibraryKind::Manga)
                            <x-rows.small-lockup :mangas="$results" :is-row="false" />
                            @break
                        @case (\App\Enums\UserLibraryKind::Game)
                            <x-rows.small-lockup :games="$results" :is-row="false" />
                            @break
                    @endswitch

                    <div class="mt-4 pl-4 pr-4">
                        {{ $results->links() }}
                    </div>
                </section>
            @else
                <x-empty-state :image="$emptyImage" :heading="$emptyHeading" :description="$emptyDescription" />
            @endif
        </div>
    </main>
</x-base-layout>
