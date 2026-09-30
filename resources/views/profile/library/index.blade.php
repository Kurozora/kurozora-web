<x-base-layout>
    <x-slot:title>
        {{ __(':x’s :y', ['x' => $user->username, 'y' => $titleSuffix]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Join :x and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.', ['x' => config('app.name')]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x’s :y', ['x' => $user->username, 'y' => $titleSuffix]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Join :x and build your own anime, manga and game library for free. Keep track of the series you love, and the ones you will love next.', ['x' => config('app.name')]) }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    <main>
        <div class="pt-4 pb-6" data-paginated="library">
            <div class="xl:safe-area-inset">
                <div class="flex flex-nowrap gap-4 justify-between pl-4 pr-4 text-center whitespace-nowrap overflow-x-scroll no-scrollbar">
                    @foreach ($statuses as $value)
                        @php
                            $slug = strtolower($value);
                        @endphp

                        <button
                            class="pl-4 pr-4 pb-2 border-b hover:border-tint {{ $status === $slug ? 'border-tint' : 'border-primary' }}"
                            type="button"
                            form="search-bar"
                            value="{{ $slug === $defaultStatus ? '' : $slug }}"
                            data-search-choice="status"
                            data-toggle="tab"
                        >{{ __($value) }}</button>
                    @endforeach
                </div>
            </div>

            <div class="mt-8">
                <section class="xl:safe-area-inset">
                    <x-search-bar :criteria="$criteria" :action="$canonicalUrl" :hidden="['status' => $status === $defaultStatus ? '' : $status]">
                        <x-slot:rightBarButtonItems>
                            <x-square-link href="{{ $randomUrl }}?status={{ $status }}" wire:navigate>
                                @svg('dice', 'fill-current', ['aria-labelledby' => $randomLabel, 'width' => '28'])
                            </x-square-link>
                        </x-slot:rightBarButtonItems>
                    </x-search-bar>
                </section>

                @if ($results->total())
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
                    <x-empty-state :image="$emptyImage" alt="Empty Library" :heading="$emptyHeading" :description="$emptyDescription" />
                @endif
            </div>
        </div>
    </main>
</x-base-layout>
