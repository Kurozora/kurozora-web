<main
    x-data="{
        year: @entangle('year').live,
        loadingScreenEnabled: @entangle('loadingScreenEnabled').live,
        isNavOpen: false
    }"
    wire:init="loadPage"
>
    <x-slot:title>
        {{ __('Re:CAP :x', ['x' => $this->year]) }}
    </x-slot:title>

    <x-slot:description>
        {{ __('Look back at the top anime, manga, games and songs that defined your year. Discover your personalized :x Kurozora Re:CAP.', ['x' => $this->year]) }}
    </x-slot:description>

    <x-slot:meta>
        <meta property="og:title" content="{{ __('Re:CAP :x', ['x' => $this->year]) }} — {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('Look back at the top anime, manga, games and songs that defined your year. Discover your personalized :x Kurozora Re:CAP.', ['x' => $this->year]) }}" />
        <meta property="og:image" content="{{ asset('images/static/banners/kurozora_recap_23.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('recap.index') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        recap?year={{ $this->year }}&month={{ $this->month }}
    </x-slot:appArgument>

    @if ($this->recapYears->count())
        <header
            id="header"
            class="sticky top-0 bg-black/30 backdrop-blur backdrop-saturate-200 z-10"
        >
            <div class="relative flex flex-row items-center justify-between max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
                <div class="absolute top-0 left-0 bottom-0 right-0 blur backdrop-blur" style="z-index: -1;"></div>

                <div class="flex items-center justify-between gap-2">
                    {{-- Hamburger --}}
                    <div
                        class="-mr-2 flex items-center"
                        x-transition:enter="ease-out duration-150 delay-[50ms] transform sm:delay-300"
                        x-transition:enter-start="opacity-0 scale-75"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-200 delay-100 transform sm:delay-[50ms]"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-75"
                        x-on:click="isNavOpen = ! isNavOpen"
                    >
                        <button
                            class="inline-flex items-center justify-center pt-2 pr-2 pb-2 pl-2 rounded-md text-white focus:outline-none transition duration-150 ease-in-out"
                        >
                            <svg stroke="currentColor" fill="none" viewBox="0 0 24 24" width="24">
                                <path
                                    class="inline-flex transform origin-center"
                                    x-show="! isNavOpen"
                                    x-transition:enter="ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-75 rotate-180"
                                    x-transition:enter-end="opacity-100 scale-100 rotate-0"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100 scale-100 rotate-0"
                                    x-transition:leave-end="opacity-0 scale-75 rotate-180"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />

                                <path
                                    class="inline-flex transform origin-center"
                                    x-show="isNavOpen"
                                    x-transition:enter="ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-75 rotate-180"
                                    x-transition:enter-end="opacity-100 scale-100 rotate-0"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100 scale-100 rotate-0"
                                    x-transition:leave-end="opacity-0 scale-75 rotate-180"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <h1 class="text-white text-xl font-bold">{{ __('Re:CAP’:x', ['x' => substr($this->year, -2)]) }}</h1>
                </div>

                <div class="flex flex-row items-center gap-1.5">
                    <x-logo class="h-5 w-auto text-white" />
                    <p class="text-white text-xl">{{ config('app.name') }}</p>
                </div>
            </div>

            {{-- Responsive Navigation Menu --}}
            <div
                class="absolute pl-4 pr-4 w-full bg-black/30 backdrop-blur backdrop-saturate-200 rounded-b-2xl"
                x-show="isNavOpen"
                x-collapse.duration.400ms=""
                x-on:click="isNavOpen = !isNavOpen"
            >
                <div class="absolute top-0 left-0 bottom-0 right-0 blur backdrop-blur" style="z-index: -1;"></div>

                <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6 h-screen">
                    <ul class="m-0 mb-4">
                        <li class="pb-10">
                            <p class="text-2xl text-white font-semibold">{{ __('Select a year to see your recap') }}</p>
                        </li>

                        @foreach ($this->recapYears as $recap)
                            <li
                                id="recap{{ $recap->year }}"
                                wire:key="{{ uniqid($recap->year, true) }}"
                            >
                                <style>
                                    #recap{{ $recap->year }} button:hover {
                                        color: {{ $recap->background_color_2 }};
                                    }
                                </style>

                                <button
                                    class="flex w-full pt-6 text-6xl text-white font-semibold"
                                    x-bind:class="{
                                        'opacity-25 hover:opacity-100': year !== {{ $recap->year }}
                                    }"
                                    x-on:click="loadingScreenEnabled = true; year = {{ $recap->year }}"
                                >
                                    <p>{{ __('’:x', ['x' => substr($recap->year, -2)]) }}</p>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
            <div
                x-data="{
                    month: @entangle('month').live
                }"
                class="flex justify-between gap-2 whitespace-nowrap overflow-x-scroll no-scrollbar"
            >
                @if ($this->year !== now()->year || now()->month === 12)
                    <span wire:key="select-{{ $this->year }}">
                        <template x-if="month === null">
                            <x-tinted-pill-button>
                                <p class="pr-2 pl-2 text-base">{{ $this->year }}</p>
                            </x-tinted-pill-button>
                        </template>

                        <template x-if="month !== null">
                            <x-tinted-pill-button
                                color="transparent"
                                x-on:click="month = null"
                            >
                                <p class="pr-2 pl-2 text-base text-white">{{ $this->year }}</p>
                            </x-tinted-pill-button>
                        </template>
                    </span>
                @endif

                @foreach ($this->recapMonths as $recap)
                    <div wire:key="select-{{ $recap->year }}-{{ $recap->month }}">
                        <template x-if="month === {{ $recap->month }}">
                            <x-tinted-pill-button>
                                <p class="pr-2 pl-2 text-base">{{ substr($recap->month_name, 0, 3) }}</p>
                            </x-tinted-pill-button>
                        </template>

                        <template x-if="month !== {{ $recap->month }}">
                            <x-tinted-pill-button
                                color="transparent"
                                x-on:click="month = {{ $recap->month }}"
                            >
                                <p class="pr-2 pl-2 text-base text-white">{{ substr($recap->month_name, 0, 3) }}</p>
                            </x-tinted-pill-button>
                        </template>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div
        class="max-w-7xl mx-auto mb-8 pl-5 pr-5 pb-6 text-white sm:px-6"
        x-show="!loadingScreenEnabled"
    >
        <div class="fixed top-0 left-0 bottom-0 right-0 blur" style="background: url('{{ asset('images/static/star_bg_lg.jpg') }}') no-repeat center center; background-size: cover; transform: scale(1.1); z-index: -1;"></div>
        <div class="fixed top-0 left-0 bottom-0 right-0 blur backdrop-blur" style="z-index: -1;"></div>

        @if ($this->recaps->count())
            {{-- rand() is necessary to make it re-render and run x-init --}}
            <section
                class="mt-36"
                wire:key="{{ rand() }}"
                x-init="loadingScreenEnabled = false"
            >
                <div class="flex flex-col items-center mt-12">
                    <h2 class="max-w-sm text-2xl text-center font-semibold md:text-4xl">
                        @if ($this->year == now()->year)
                            {{ __('Series that defined your arc in :x', ['x' => now()->month($this->month)->monthName]) }}
                        @else
                            {{ __('Series that defined your arc in :x', ['x' => $this->year]) }}
                        @endif
                    </h2>
                </div>

                @foreach ($this->recaps as $recap)
                    @if ($recap->recapItems->count())
                        @switch($recap->type)
                            @case(\App\Models\Anime::class)
                                <article class="mt-5 md:mt-36">
                                    <div class="hidden md:block">
                                        <h2 class="font-semibold md:text-2xl">{{ __('Top Anime') }}</h2>
                                        <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_series_count]) }}</p>
                                    </div>

                                    <div class="flex flex-nowrap gap-4 mt-12">
                                        <div class="flex flex-col justify-between gap-2 w-full pt-5 pl-5 pb-5 pr-5 bg-gray-200/20 rounded-xl">
                                            <div class="block mb-4 md:hidden">
                                                <h2 class="font-semibold md:text-2xl">{{ __('Top Anime') }}</h2>
                                                <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_series_count]) }}</p>
                                            </div>

                                            <x-rows.small-lockup :animes="$recap->recapItems->pluck('model')" :tracking-enabled="false" :is-ranked="true" />
                                        </div>
                                    </div>
                                </article>
                                @break
                            @case(\App\Models\Manga::class)
                                <article class="mt-5 md:mt-36">
                                    <div class="hidden md:block">
                                        <h2 class="font-semibold md:text-2xl">{{ __('Top Manga') }}</h2>
                                        <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_series_count]) }}</p>
                                    </div>

                                    <div class="flex flex-nowrap gap-4 mt-12">
                                        <div class="flex flex-col justify-between gap-2 w-full pt-5 pl-5 pb-5 pr-5 bg-gray-200/20 rounded-xl">
                                            <div class="block mb-4 md:hidden">
                                                <h2 class="font-semibold md:text-2xl">{{ __('Top Manga') }}</h2>
                                                <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_series_count]) }}</p>
                                            </div>

                                            <x-rows.small-lockup :mangas="$recap->recapItems->pluck('model')" :tracking-enabled="false" :is-ranked="true" />
                                        </div>
                                    </div>
                                </article>
                                @break
                            @case(\App\Models\Game::class)
                                <article class="mt-5 md:mt-36">
                                    <div class="hidden md:block">
                                        <h2 class="font-semibold md:text-2xl">{{ __('Top Games') }}</h2>
                                        <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_series_count]) }}</p>
                                    </div>

                                    <div class="flex flex-nowrap gap-4 mt-12">
                                        <div class="flex flex-col justify-between gap-2 w-full pt-5 pl-5 pb-5 pr-5 bg-gray-200/20 rounded-xl">
                                            <div class="block mb-4 md:hidden">
                                                <h2 class="font-semibold md:text-2xl">{{ __('Top Games') }}</h2>
                                                <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_series_count]) }}</p>
                                            </div>

                                            <x-rows.small-lockup :games="$recap->recapItems->pluck('model')" :tracking-enabled="false" :is-ranked="true" />
                                        </div>
                                    </div>
                                </article>
                                @break
                            @case(\App\Models\Genre::class)
                                <article class="mt-5 md:mt-36">
                                    <div class="hidden md:block">
                                        <h2 class="font-semibold md:text-2xl">{{ __('Top Genres') }}</h2>
                                    </div>

                                    <div class="flex flex-nowrap gap-4 mt-12">
                                        <div class="flex flex-col justify-between gap-2 w-full pt-5 pl-5 pb-5 pr-5 bg-gray-200/20 rounded-xl">
                                            <div class="block mb-4 md:hidden">
                                                <h2 class="font-semibold md:text-2xl">{{ __('Top Genres') }}</h2>
                                            </div>

                                            @foreach ($recap->recapItems->take(5) as $key => $recapItem)
                                                <a class="flex flex-row items-center gap-2" href="{{ route('genres.details', $recapItem->model) }}">
                                                    <p class="leading-tight font-semibold break-all md:text-2xl">{{ $key + 1 }}</p>

                                                    <p class="font-bold text-2xl md:text-8xl">{{ $recapItem->model->name }}</p>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </article>
                                @break
                            @case(\App\Models\Theme::class)
                                <article class="mt-5 md:mt-36">
                                    <div class="hidden md:block">
                                        <h2 class="font-semibold md:text-2xl">{{ __('Top Themes') }}</h2>
                                    </div>

                                    <div class="flex flex-nowrap gap-4 mt-12">
                                        <div class="flex flex-col justify-between gap-2 w-full pt-5 pl-5 pb-5 pr-5 bg-gray-200/20 rounded-xl">
                                            <div class="block mb-4 md:hidden">
                                                <h2 class="font-semibold md:text-2xl">{{ __('Top Themes') }}</h2>
                                            </div>

                                            @foreach ($recap->recapItems->take(5) as $key => $recapItem)
                                                <a class="flex flex-row items-center gap-2" href="{{ route('themes.details', $recapItem->model) }}">
                                                    <p class="leading-tight font-semibold md:text-2xl">{{ $key + 1 }}</p>

                                                    <p class="font-bold text-2xl break-all md:text-8xl">{{ $recapItem->model->name }}</p>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </article>
                                @break
                            @default
                                @if (app()->isLocal())
                                    {{ 'Unhandled type: ' . $recap->type }}
                                @endif
                        @endswitch
                    @endif
                @endforeach
            </section>

            <section class="mt-5 md:mt-36">
                <div class="flex flex-col items-center mt-12">
                    <h2 class="max-w-sm text-2xl text-center font-semibold md:text-4xl">{{ __('These milestones marked your season finale') }}</h2>
                </div>

                <article class="flex flex-nowrap gap-4 mt-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar md:mt-12">
                    @foreach ($this->recaps as $recap)
                        @switch($recap->type)
                            @case(\App\Models\Anime::class)
                                @if ($recap->total_parts_duration)
                                    <x-lockups.milestone-lockup
                                        :recap="$recap"
                                        :title="__('Minutes Watched')"
                                        :progress-aria-label="__(':x Minutes', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))])"
                                        :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))"
                                        :progress-unit="__('Minutes')"
                                    />
                                @endif

                                @if ($recap->total_parts_count)
                                    <x-lockups.milestone-lockup
                                        :recap="$recap"
                                        :title="__('Episodes Watched')"
                                        :progress-aria-label="__(':x Episodes', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_count))])"
                                        :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_count))"
                                        :progress-unit="__('Episodes')"
                                        :media-collection="\App\Enums\MediaCollection::Banner"
                                    />
                                @endif
                                @break
                            @case(\App\Models\Manga::class)
                                @if ($recap->total_parts_duration)
                                    <x-lockups.milestone-lockup
                                        :recap="$recap"
                                        :title="__('Minutes Read')"
                                        :progress-aria-label="__(':x Minutes', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))])"
                                        :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))"
                                        :progress-unit="__('Minutes')"
                                    />
                                @endif

                                @if ($recap->total_parts_count)
                                    <x-lockups.milestone-lockup
                                        :recap="$recap"
                                        :title="__('Chapters Read')"
                                        :progress-aria-label="__(':x Chapters', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_count))])"
                                        :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_count))"
                                        :progress-unit="__('Chapters')" />
                                @endif
                                @break
                            @case(\App\Models\Games::class)
                                @if ($recap->total_parts_duration)
                                    <x-lockups.milestone-lockup
                                        :recap="$recap"
                                        :title="__('Minutes Played')"
                                        :progress-aria-label="__(':x Minutes', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))])"
                                        :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_duration / 60))"
                                        :progress-unit="__('Minutes')"
                                    />
                                @endif

                                @if ($recap->total_parts_count)
                                    <x-lockups.milestone-lockup
                                        :recap="$recap"
                                        :title="__('Games Played')"
                                        :progress-aria-label="__(':x Games', ['x' => number_format(round_to_nearest_quarter($recap->total_parts_count))])"
                                        :progress-count="number_format(round_to_nearest_quarter($recap->total_parts_count))"
                                        :progress-unit="__('Games')"
                                    />
                                @endif
                                @break
                            @default
                        @endswitch
                    @endforeach
                </article>

                @php($recap = $this->recaps->where('top_percentile', '!=', 0.00)->sortBy('top_percentile')->first())
                @if (!empty($recap))
                    <article class="flex flex-col gap-4 mt-12">
                        @switch($recap->type)
                            @case(\App\Models\Anime::class)
                                <x-lockups.milestone-lockup
                                    class="pb-5"
                                    style="min-width: 100%; max-width: 100%;"
                                    :recap="null"
                                    :title="__('You were in the top :x% of anime watchers this year.', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                    :progress-aria-label="__('Top :x% of anime watchers', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                    :progress-count="round($recap->top_percentile / 0.05) * 0.05 . '%'"
                                    :progress-unit="__('Top Anime Watcher')"
                                />
                                @break
                            @case(\App\Models\Manga::class)
                                <x-lockups.milestone-lockup
                                    class="pb-5"
                                    style="min-width: 100%; max-width: 100%;"
                                    :recap="null"
                                    :title="__('You were in the top :x% of manga readers this year.', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                    :progress-aria-label="__('Top :x% of manga readers', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                    :progress-count="round($recap->top_percentile / 0.05) * 0.05 . '%'"
                                    :progress-unit="__('Top Manga Reader')"
                                />
                                @break
                            @case(\App\Models\Games::class)
                                <x-lockups.milestone-lockup
                                    style="min-width: 100%; max-width: 100%;"
                                    :recap="null"
                                    :title="__('You were in the top :x% of game players this year.', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                    :progress-aria-label="__('Top :x% of game players', ['x' => round($recap->top_percentile / 0.05) * 0.05])"
                                    :progress-count="round($recap->top_percentile / 0.05) * 0.05 . '%'"
                                    :progress-unit="__('Top Game Player')"
                                />
                                @break
                            @default
                        @endswitch
                    </article>
                @endif
            </section>
        @elseif ($this->year === now()->year && $this->month === now()->month)
            <div class="flex flex-col items-center justify-center" style="height: calc(100vh - 180px);">
                <h2 class="max-w-sm text-center text-xl font-semibold md:max-w-2xl md:text-4xl">
                    @if (now()->month === 12)
                        {{ __(':x Re:CAP is still in progress. Check back in a week.', ['x' => now()->monthName]) }}
                    @else
                         {{ __(':x Re:CAP is still in progress. Check back in early :y.', ['x' => now()->monthName, 'y' => now()->addMonth()->monthName]) }}
                    @endif
                </h2>

                <x-link-button class="mt-12" href="/">{{ __('Keep Tracking on :x', ['x' => config('app.name')]) }}</x-link-button>
            </div>
        @endif
    </div>

    <div
        class="absolute top-0 bottom-0 left-0 right-0 max-w-7xl mx-auto mb-8 pl-5 pr-5 pb-6 text-white z-10 sm:px-6"
        x-show="loadingScreenEnabled"
    >
        <div class="fixed top-0 left-0 bottom-0 right-0 blur" style="background: url('{{ asset('images/static/star_bg_lg.jpg') }}') no-repeat center center; background-size: cover; transform: scale(1.1); z-index: -1;"></div>
        <div class="fixed top-0 left-0 bottom-0 right-0 blur backdrop-blur" style="z-index: -1;"></div>

        <div class="flex flex-col items-center justify-center w-full h-screen text-center">
            <p class="animate-pulse text-5xl font-black">{{ __('This is your Re:CAP.') }}</p>
        </div>
    </div>
</main>
