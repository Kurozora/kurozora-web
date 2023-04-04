<main>
    <x-slot:title>
        {{ __('Kurozora Search') }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Search for anime, manga, games, characters, light novels, music, people, studios, and more...') }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Kurozora Search') }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Search for anime, manga, games, characters, light novels, music, people, studios, and more...') }}" />
        <meta property="og:image" content="{{ asset('images/static/placeholders/episode_banner.webp') }}" />
        <meta property="og:type" content="website" />
        <meta property="twitter:title" content="{{ __('Kurozora Search') }} — {{ config('app.name') }}" />
        <meta property="twitter:description" content="{{ __('Search for anime, manga, games, characters, light novels, music, people, studios, and more...') }}" />
        <meta property="twitter:card" content="summary_large_image" />
        <meta property="twitter:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="twitter:image:alt" content="{{ __('Search for anime, manga, games, characters, light novels, music, people, studios, and more...') }}" />
        <link rel="canonical" href="{{ route('search.index') }}">
        <meta name="robots" content="noindex, nofollow">
        <x-misc.schema>
            "@type":"WebSite",
            "url":"{{ config('app.url') }}",
            "potentialAction": {
            "@type":"SearchAction",
            "target":"{{ route('search.index') }}?q={search_term_string}&src=mc_google",
            "query-input": "required name=search_term_string"
        </x-misc.schema>
    </x-slot:meta>

    <x-slot:appArgument>
        search
    </x-slot:appArgument>

    <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
        <section class="mb-4">
            <div class="flex gap-1">
                <div class="flex flex-wrap items-center w-full">
                    <p class="text-2xl font-bold">{{ __('Search') }}</p>
                </div>

                <div class="flex flex-wrap justify-end items-center w-full">
                </div>
            </div>

            <div
                class="mt-4 justify-between"
                x-data="{
                    type: @entangle('type')
                }"
            >
                <x-search-bar searchModel="q">
                    <x-slot:rightBarButtonItems>
                        <div>
                            <x-select wire:model="scope">
                                @foreach(\App\Enums\SearchScope::asSelectArray() as $key => $value)
                                    <option value="{{ $key }}">{{ __($value) }}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </x-slot:rightBarButtonItems>
                </x-search-bar>

                <x-hr class="mt-4 mb-4" />

                <div class="flex gap-2 overflow-x-scroll no-scrollbar">
                    @foreach(\App\Enums\SearchType::asWebSelectArray($this->scope) as $key => $value)
                        <template x-if="type === '{{ $key }}'">
                            <x-button>{{ $value }}</x-button>
                        </template>

                        <template x-if="type !== '{{ $key }}'">
                            <x-outlined-button
                                x-on:click="type = '{{ $key }}'"
                            >{{ __($value) }}</x-outlined-button>
                        </template>
                    @endforeach
                </div>
            </div>
        </section>

        @if (empty($this->searchResults) || empty($this->searchResults->total()))
            <section class="mt-4">
                <ul class="flex flex-col gap-4 items-center mt-8">
                    @foreach ($this->searchSuggestions as $searchSuggestion)
                        <li>
                            <button class="pl-4 pr-4 pb-2 text-orange-500" wire:click="$set('q', '{{ $searchSuggestion }}')">
                                {{ $searchSuggestion }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </section>
        @else
            <section class="mt-4">
                @switch($this->type)
                    @case(\App\Enums\SearchType::Shows)
                        <x-rows.small-lockup :animes="$this->searchResults" :is-row="false"/>
                        @break
                    @case(\App\Enums\SearchType::Literatures)
                        <x-rows.small-lockup :mangas="$this->searchResults" :is-row="false"/>
                        @break
                    @case(\App\Enums\SearchType::Games)
                        <x-rows.small-lockup :games="$this->searchResults" :is-row="false"/>
                        @break
                    @case(\App\Enums\SearchType::Episodes)
                        <x-rows.episode-lockup :episodes="$this->searchResults" :is-row="false"/>
                        @break
                    @case(\App\Enums\SearchType::Characters)
                        <x-rows.character-lockup :characters="$this->searchResults" :is-row="false"/>
                        @break
                    @case(\App\Enums\SearchType::People)
                        <x-rows.person-lockup :people="$this->searchResults" :is-row="false"/>
                        @break
                    @case(\App\Enums\SearchType::Songs)
                        <x-rows.music-lockup :songs="$this->searchResults" :is-row="false"/>
                        @break
                    @case(\App\Enums\SearchType::Studios)
                        <x-rows.studio-lockup :studios="$this->searchResults" :is-row="false"/>
                        @break
                    @case(\App\Enums\SearchType::Users)
                        <x-rows.user-lockup :users="$this->searchResults" :is-row="false"/>
                        @break
                @endswitch
            </section>

            <section class="mt-4">
                {{ $this->searchResults->links() }}
            </section>
        @endif
    </div>
</main>
