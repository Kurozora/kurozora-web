@php
    $searchQuery ??= '';
    $searchResults ??= [];
    $quickLinks ??= \App\Http\Controllers\Web\SearchSuggestionsController::quickLinks();
@endphp

            @if (!empty($searchResults))
                @foreach ($searchResults as $searchResult)
                    <x-search-header>
                        <x-slot:title>
                            {{ $searchResult['title'] }}
                        </x-slot:title>

                        <x-slot:action>
                            <x-section-nav-link href="{{ route('search.index', ['q' => $searchQuery, 'type' => $searchResult['search_type']]) }}">{{ __('See All') }}</x-section-nav-link>
                        </x-slot:action>
                    </x-search-header>

                    <div class="mt-4">
                        @switch($searchResult['type'])
                            @case(\App\Models\Anime::TABLE_NAME)
                                <x-rows.small-lockup :safe-area-inset-enabled="false" :animes="$searchResult['results']" />
                            @break
                            @case(\App\Models\Manga::TABLE_NAME)
                                <x-rows.small-lockup :safe-area-inset-enabled="false" :mangas="$searchResult['results']" />
                            @break
                            @case(\App\Models\Game::TABLE_NAME)
                                <x-rows.small-lockup :safe-area-inset-enabled="false" :games="$searchResult['results']" />
                            @break
                            @case(\App\Models\Episode::TABLE_NAME)
                                <x-rows.episode-lockup :safe-area-inset-enabled="false" :episodes="$searchResult['results']" />
                            @break
                            @case(\App\Models\Character::TABLE_NAME)
                                <x-rows.character-lockup :safe-area-inset-enabled="false" :characters="$searchResult['results']" />
                            @break
                            @case(\App\Models\Person::TABLE_NAME)
                                <x-rows.person-lockup :safe-area-inset-enabled="false" :people="$searchResult['results']" />
                            @break
                            @case(\App\Models\Studio::TABLE_NAME)
                                <x-rows.studio-lockup :safe-area-inset-enabled="false" :studios="$searchResult['results']" />
                            @break
                            @case(\App\Models\User::TABLE_NAME)
                                <x-rows.user-lockup :safe-area-inset-enabled="false" :users="$searchResult['results']" />
                            @break
                            @case(\App\Models\Song::TABLE_NAME)
                                <x-rows.music-lockup :safe-area-inset-enabled="false" :songs="$searchResult['results']" />
                            @break
                        @endswitch

                        <x-hr class="mt-4 mb-4 ml-4 mr-4" />
                    </div>
                @endforeach
            @endif

            @if (empty($searchResults) && !empty($searchQuery))
                <p class="text-sm text-secondary text-center font-bold">{{ __('No search results found :(') }}</p>
            @endif

            {{-- Quick Links --}}
            @if (!empty($quickLinks) && empty($searchResults))
                <x-search-header
                    x-show="isSearchEnabled"
                    x-transition:enter="ease duration-[400ms] transform"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <x-slot:title>
                        {{ __('Quick Links') }}
                    </x-slot:title>
                </x-search-header>

                <ul class="space-y-4 ml-4 mr-4">
                    @foreach ($quickLinks as $key => $quickLink)
                        <li
                            x-show="isSearchEnabled"
                            x-bind:style="isSearchEnabled ? 'transition-duration: {{ $key * 50 + 500 }}ms;' : ''"
                            x-transition:enter="ease duration-100 transform"
                            x-transition:enter-start="opacity-0 translate-x-8"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                        >
                            @if (isset($quickLink['link']))
                                <x-footer-link
                                    class="inline-block w-full"
                                    href="{{ $quickLink['link'] }}"
                                >
                                    {{ $quickLink['title'] }}
                                </x-footer-link>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
