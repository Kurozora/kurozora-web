<x-base-layout>
    <x-slot:title>
        {{ __(':x Search', ['x' => config('app.name')]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Search for anime, manga, games, characters, light novels, music, people, studios, and more…') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __(':x Search', ['x' => config('app.name')]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Search for anime, manga, games, characters, light novels, music, people, studios, and more…') }}" />
        <meta property="og:image" content="{{ asset('images/static/placeholders/episode_banner.webp') }}" />
        <meta property="og:type" content="website" />
        <meta property="twitter:title" content="{{ __(':x Search', ['x' => config('app.name')]) }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ __('Search for anime, manga, games, characters, light novels, music, people, studios, and more…') }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ __('Search for anime, manga, games, characters, light novels, music, people, studios, and more…') }}" />
        <link rel="canonical" href="{{ route('search.index') }}">
        <meta name="robots" content="noindex, nofollow">
        <x-misc.schema :data="$schema" />
    </x-slot:meta>

    <x-slot:appArgument>
        search
    </x-slot:appArgument>

    <main>
        <div class="pt-4 pb-6" data-paginated="search">
            <section class="mb-4 xl:safe-area-inset">
                <div class="flex gap-1 pl-4 pr-4">
                    <div class="flex flex-wrap items-center w-full">
                        <h1 class="text-2xl font-bold">{{ __('Search') }}</h1>
                    </div>

                    <div class="flex flex-wrap justify-end items-center w-full">
                    </div>
                </div>

                <div class="justify-between mt-4">
                    <x-search-bar :criteria="$criteria" :action="route('search.index')">
                        <x-slot:rightBarButtonItems>
                            <div>
                                <x-select name="scope">
                                    @foreach (\App\Enums\SearchScope::asSelectArray() as $key => $value)
                                        <option value="{{ $key === \App\Enums\SearchScope::Kurozora ? '' : $key }}" @selected($scope === $key)>{{ __($value) }}</option>
                                    @endforeach
                                </x-select>
                            </div>
                        </x-slot:rightBarButtonItems>
                    </x-search-bar>
                </div>
            </section>

            @if (empty($results) || empty($results->total()))
                <section class="mt-4 xl:safe-area-inset">
                    <ul class="flex flex-col gap-4 items-center justify-center mr-4" style="min-height: 50vh;">
                        @foreach ($suggestions as $suggestion)
                            <li>
                                <button class="pl-4 pr-4 pt-2 pb-2 text-tint" type="button" form="search-bar" value="{{ $suggestion }}" data-search-choice="q">
                                    {{ $suggestion }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @else
                <section class="mt-4 xl:safe-area-inset">
                    @switch($searchType)
                        @case(\App\Enums\SearchType::Shows)
                            <x-rows.small-lockup :animes="$results" :is-row="false"/>
                            @break
                        @case(\App\Enums\SearchType::Literatures)
                            <x-rows.small-lockup :mangas="$results" :is-row="false"/>
                            @break
                        @case(\App\Enums\SearchType::Games)
                            <x-rows.small-lockup :games="$results" :is-row="false"/>
                            @break
                        @case(\App\Enums\SearchType::Episodes)
                            <x-rows.episode-lockup :episodes="$results" :is-row="false"/>
                            @break
                        @case(\App\Enums\SearchType::Characters)
                            <x-rows.character-lockup :characters="$results" :is-row="false"/>
                            @break
                        @case(\App\Enums\SearchType::People)
                            <x-rows.person-lockup :people="$results" :is-row="false"/>
                            @break
                        @case(\App\Enums\SearchType::Songs)
                            <x-rows.music-lockup :songs="$results" :is-row="false"/>
                            @break
                        @case(\App\Enums\SearchType::Studios)
                            <x-rows.studio-lockup :studios="$results" :is-row="false"/>
                            @break
                        @case(\App\Enums\SearchType::Users)
                            <x-rows.user-lockup :users="$results" :is-row="false"/>
                            @break
                    @endswitch

                    <div class="mt-4 pl-4 pr-4">
                        {{ $results->links() }}
                    </div>
                </section>
            @endif
        </div>
    </main>
</x-base-layout>
