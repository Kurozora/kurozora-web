<main
    x-data="{
        year: @entangle('year'),
        loadingScreenEnabled: @entangle('loadingScreenEnabled'),
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
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
    </x-slot:meta>

    @if ($this->recapYears->count())
        <header
            id="header"
            class="sticky top-0 bg-black/30 backdrop-blur backdrop-saturate-200 z-10"
        >
            <div class="flex flex-row items-center justify-between max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
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
                <div class="fixed top-0 left-0 bottom-0 right-0 blur backdrop-blur" style="z-index: -1;"></div>

                <div class="max-w-7xl mx-auto pl-4 pr-4 py-6 sm:px-6">
                    <ul class="m-0 mb-4">
                        <li class="pb-10">
                            <p class="text-2xl text-white font-semibold">{{ __('Select a year to see your recap') }}</p>
                        </li>

                        @foreach($this->recapYears as $recapYear)
                            <li
                                wire:key="{{  uniqid($recapYear, true) }}"
                            >
                                <button
                                    class="flex w-full pt-6 text-6xl text-white font-semibold hover:text-orange-500"
                                    x-bind:class="{
                                        'opacity-25 hover:opacity-100': year !== {{ $recapYear }}
                                    }"
                                    x-on:click="loadingScreenEnabled = true; year = {{ $recapYear}}"
                                >
                                    <p>{{  __('’:x', ['x' => substr($recapYear, -2)]) }}</p>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </header>
    @endif

    <div
        class="max-w-7xl mx-auto mb-8 pl-5 pr-5 pb-6 text-white sm:px-6"
        x-show="!loadingScreenEnabled"
    >
        <div class="fixed top-0 left-0 bottom-0 right-0 blur" style="background: url('{{ asset('images/static/star_bg_lg.jpg') }}') no-repeat center center; background-size: cover; margin: -1rem; z-index: -1;"></div>
        <div class="fixed top-0 left-0 bottom-0 right-0 blur backdrop-blur" style="z-index: -1;"></div>

        @if ($this->recaps->count())
            {{-- rand() is necessary to make it re-render and run x-init --}}
            <section
                class="mt-36"
                wire:key="{{ rand() }}"
                x-init="loadingScreenEnabled = false"
            >
                <div class="flex flex-col items-center mt-12">
                    <h2 class="max-w-sm text-2xl text-center font-semibold md:text-4xl">{{ __('Series that defined your arc') }}</h2>
                </div>

                @foreach ($this->recaps as $recap)
                    @if ($recap->recapItems->count())
                        @switch($recap->type)
                            @case(\App\Models\Anime::class)
                                <article class="mt-5 md:mt-36">
                                    <div class="hidden md:block">
                                        <h2 class="font-semibold md:text-2xl">{{ __('Top Anime') }}</h2>
                                        <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_count]) }}</p>
                                    </div>

                                    <div class="flex flex-nowrap gap-4 mt-12">
                                        <div class="flex flex-col justify-between gap-2 w-full pt-5 pl-5 pb-5 pr-5 bg-gray-200/20 rounded-xl">
                                            <div class="block mb-4 md:hidden">
                                                <h2 class="font-semibold md:text-2xl">{{ __('Top Anime') }}</h2>
                                                <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_count]) }}</p>
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
                                        <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_count]) }}</p>
                                    </div>

                                    <div class="flex flex-nowrap gap-4 mt-12">
                                        <div class="flex flex-col justify-between gap-2 w-full pt-5 pl-5 pb-5 pr-5 bg-gray-200/20 rounded-xl">
                                            <div class="block mb-4 md:hidden">
                                                <h2 class="font-semibold md:text-2xl">{{ __('Top Manga') }}</h2>
                                                <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_count]) }}</p>
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
                                        <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_count]) }}</p>
                                    </div>

                                    <div class="flex flex-nowrap gap-4 mt-12">
                                        <div class="flex flex-col justify-between gap-2 w-full pt-5 pl-5 pb-5 pr-5 bg-gray-200/20 rounded-xl">
                                            <div class="block mb-4 md:hidden">
                                                <h2 class="font-semibold md:text-2xl">{{ __('Top Games') }}</h2>
                                                <p class="opacity-75 font-semibold md:text-2xl">{{ __(':x total series', ['x' => $recap->total_count]) }}</p>
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

                                            @foreach($recap->recapItems->take(5) as $key => $recapItem)
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

                                            @foreach($recap->recapItems->take(5) as $key => $recapItem)
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

                <article class="flex flex-nowrap gap-4 mt-4 snap-x overflow-x-scroll no-scrollbar md:mt-12">
                    @foreach ($this->recaps as $recap)
                        @if ($recap->total_duration)
                            <div class="flex flex-col items-center justify-between gap-6 w-full pt-5 pl-5 pr-5 bg-gray-200/20 rounded-xl overflow-hidden" style="min-width: 256px; max-width: 384px;" wire:key="{{ uniqid($recap->id, true) }}">
                                <div class="w-full">
                                    <h2 class="text-2xl font-semibold">{{ __('Milestone') }}</h2>

                                    @switch($recap->type)
                                        @case(\App\Models\Anime::class)
                                            <p class="text-2xl opacity-75 font-semibold">{{ __('Minutes Watched') }}</p>
                                            @break
                                        @case(\App\Models\Game::class)
                                            <p class="text-2xl opacity-75 font-semibold">{{ __('Minute Read') }}</p>
                                            @break
                                        @case(\App\Models\Manga::class)
                                            <p class="text-2xl opacity-75 font-semibold">{{ __('Minutes Played') }}</p>
                                            @break
                                        @default
                                            @break
                                    @endswitch
                                </div>

                                <div class="relative aspect-square" style="height: 232px" role="progressbar" aria-label="{{ __(':x Minutes', ['x' => number_format(round_to_nearest_quarter($recap->total_duration))]) }}">
                                    <svg class="absolute w-full h-full" xmlns="http://www.w3.org/2000/svg" id="svg4140" fill="none" version="1.1" viewBox="0 0 865 862" width="865" height="862" preserveAspectRatio="xMinYMin none"><mask id="path-1-inside-1_1454_36805" fill="#ffffff"><path id="path4096" d="M864.462 430.852c0 237.953-193.517 430.852-432.231 430.852C193.516 861.704 0 668.805 0 430.852 0 192.899 193.516 0 432.231 0c238.714 0 432.231 192.899 432.231 430.852Zm-803.9497 0c0 204.64 166.4237 370.533 371.7187 370.533 205.294 0 371.718-165.893 371.718-370.533 0-204.64-166.424-370.5327-371.718-370.5327-205.295 0-371.7187 165.8927-371.7187 370.5327Z"></path></mask><path id="path4099" stroke="#ff9300" stroke-linejoin="round" stroke-width="119.589" d="M864.462 430.852c0 237.953-193.517 430.852-432.231 430.852C193.516 861.704 0 668.805 0 430.852 0 192.899 193.516 0 432.231 0c238.714 0 432.231 192.899 432.231 430.852Zm-803.9497 0c0 204.64 166.4237 370.533 371.7187 370.533 205.294 0 371.718-165.893 371.718-370.533 0-204.64-166.424-370.5327-371.718-370.5327-205.295 0-371.7187 165.8927-371.7187 370.5327z" mask="url(#path-1-inside-1_1454_36805)" style="display: inline; stroke: #ff9300; stroke-opacity: 1;"></path><mask id="mask0_1454_36805" width="865" height="862" x="0" y="0" maskUnits="userSpaceOnUse" style="mask-type: alpha;"><mask id="path-2-inside-2_1454_36805" fill="#ff9300"><path id="path4101" d="M864.462 430.852c0 237.953-193.517 430.852-432.231 430.852C193.516 861.704 0 668.805 0 430.852 0 192.899 193.516 0 432.231 0c238.714 0 432.231 192.899 432.231 430.852Zm-803.9497 0c0 204.64 166.4237 370.533 371.7187 370.533 205.294 0 371.718-165.893 371.718-370.533 0-204.64-166.424-370.5327-371.718-370.5327-205.295 0-371.7187 165.8927-371.7187 370.5327Z"></path></mask><path id="path4104" stroke="#ff9300" stroke-linejoin="round" stroke-width="119.589" d="M864.462 430.852c0 237.953-193.517 430.852-432.231 430.852C193.516 861.704 0 668.805 0 430.852 0 192.899 193.516 0 432.231 0c238.714 0 432.231 192.899 432.231 430.852Zm-803.9497 0c0 204.64 166.4237 370.533 371.7187 370.533 205.294 0 371.718-165.893 371.718-370.533 0-204.64-166.424-370.5327-371.718-370.5327-205.295 0-371.7187 165.8927-371.7187 370.5327Z" mask="url(#path-2-inside-2_1454_36805)" style="stroke: #ff9300; stroke-opacity: 1;"></path></mask><g id="g4114" mask="url(#mask0_1454_36805)"><g id="g4112" filter="url(#filter0_d_1454_36805)"><mask id="path-3-inside-3_1454_36805" fill="#ff9300"><path id="path4107" d="M633.222 83.8497c8.355-14.4251 26.911-19.4647 40.741-10.1644C752.516 126.507 811.734 203.797 842.038 293.875c33.515 99.625 29.351 208.024-11.707 304.802-41.059 96.778-116.182 175.264-211.235 220.691-95.053 45.426-203.483 54.662-304.89 25.969C212.8 816.644 125.407 752 68.4726 663.568 11.5377 575.137-11.0149 469.013 5.05795 365.165 21.1308 261.317 74.7212 166.904 155.746 99.6883 229.012 38.9098 320.142 4.15851 414.826.356495 431.494-.312791 444.612 13.6683 444.118 30.302c-.494 16.6651-14.469 29.6592-31.159 30.5227-79.883 4.1328-156.607 33.8797-218.505 85.2273-69.681 57.805-115.7686 139.001-129.5913 228.31-13.8227 89.31 5.5726 180.576 54.5363 256.627C168.363 707.04 243.52 762.634 330.73 787.31c87.21 24.676 180.46 16.734 262.206-22.333 81.745-39.067 146.351-106.565 181.661-189.794 35.31-83.23 38.891-176.453 10.068-262.13-25.609-76.126-75.232-141.632-141.05-186.985-13.724-9.457-18.748-27.7932-10.393-42.2183Z"></path></mask><path id="path4110" stroke="url(#paint0_linear_1454_36805)" stroke-linejoin="round" stroke-width="119.589" d="M633.222 83.8497c8.355-14.4251 26.911-19.4647 40.741-10.1644C752.516 126.507 811.734 203.797 842.038 293.875c33.515 99.625 29.351 208.024-11.707 304.802-41.059 96.778-116.182 175.264-211.235 220.691-95.053 45.426-203.483 54.662-304.89 25.969C212.8 816.644 125.407 752 68.4726 663.568 11.5377 575.137-11.0149 469.013 5.05795 365.165 21.1308 261.317 74.7212 166.904 155.746 99.6883 229.012 38.9098 320.142 4.15851 414.826.356495 431.494-.312791 444.612 13.6683 444.118 30.302c-.494 16.6651-14.469 29.6592-31.159 30.5227-79.883 4.1328-156.607 33.8797-218.505 85.2273-69.681 57.805-115.7686 139.001-129.5913 228.31-13.8227 89.31 5.5726 180.576 54.5363 256.627C168.363 707.04 243.52 762.634 330.73 787.31c87.21 24.676 180.46 16.734 262.206-22.333 81.745-39.067 146.351-106.565 181.661-189.794 35.31-83.23 38.891-176.453 10.068-262.13-25.609-76.126-75.232-141.632-141.05-186.985-13.724-9.457-18.748-27.7932-10.393-42.2183Z" mask="url(#path-3-inside-3_1454_36805)" shape-rendering="crispEdges" style="stroke: url(&quot;#paint0_linear_1454_36805&quot;);"></path></g></g><defs id="defs4138"><linearGradient id="paint0_linear_1454_36805" x1="648.07965" x2="490.547" y1=".32203391" y2="132.622" gradientUnits="userSpaceOnUse"><stop id="stop4133" offset="0" stop-color="#ff9300" stop-opacity="0"></stop><stop id="stop4135" offset="1" stop-color="#ff9300" stop-opacity="1"></stop></linearGradient><filter id="filter0_d_1454_36805" width="922.344" height="919.26" x="-7.85433" y="-28.6087" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse"><feFlood id="feFlood4116" flood-opacity="0" result="BackgroundImageFix"></feFlood><feColorMatrix id="feColorMatrix4118" in="SourceAlpha" result="hardAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"></feColorMatrix><feOffset id="feOffset4120" dx="21.0844"></feOffset><feGaussianBlur id="feGaussianBlur4122" stdDeviation="14.4704"></feGaussianBlur><feComposite id="feComposite4124" in2="hardAlpha" operator="out"></feComposite><feColorMatrix id="feColorMatrix4126" type="matrix" values="0 0 0 0 0.573381 0 0 0 0 0.373381 0 0 0 0 0.373381 0 0 0 1 0"></feColorMatrix><feBlend id="feBlend4128" in2="BackgroundImageFix" mode="normal" result="effect1_dropShadow_1454_36805"></feBlend><feBlend id="feBlend4130" in="SourceGraphic" in2="effect1_dropShadow_1454_36805" mode="normal" result="shape"></feBlend></filter></defs></svg>

                                    <div class="flex flex-col items-center justify-center h-full pl-5 pr-5">
                                        <p class="text-2xl font-semibold">{{ number_format(round_to_nearest_quarter($recap->total_duration)) }}</p>
                                        <p class="font-semibold">{{ __('Minutes') }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-row gap-4" style="width: 150%;">
                                    @foreach($recap->recapItems->take(3) as $key => $recapItem)
                                        @switch($recap->type)
                                            @case(\App\Models\Anime::class)
                                                <a class="flex w-1/2" href="{{ route('anime.details', $recapItem->model) }}">
                                                    <picture class="relative rounded-lg overflow-hidden">
                                                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $recapItem->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $recapItem->model->title }} Poster" title="{{ $recapItem->model->title }}">

                                                        <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                                                    </picture>
                                                </a>
                                                @break
                                            @case(\App\Models\Manga::class)
                                                <a class="flex w-1/2" href="{{ route('manga.details', $recapItem->model) }}">
                                                    <picture class="relative rounded-lg overflow-hidden">
                                                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $recapItem->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $recapItem->model->title }} Poster" title="{{ $recapItem->model->title }}">

                                                        <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                                                    </picture>
                                                </a>
                                                @break
                                            @case(\App\Models\Game::class)
                                                <a class="flex w-1/2" href="{{ route('games.details', $recapItem->model) }}">
                                                    <picture class="relative rounded-lg overflow-hidden">
                                                        <img class="w-full h-full object-cover lazyload" data-sizes="auto" data-src="{{ $recapItem->model->getFirstMediaFullUrl(\App\Enums\MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp') }}" alt="{{ $recapItem->model->title }} Poster" title="{{ $recapItem->model->title }}">

                                                        <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-lg"></div>
                                                    </picture>
                                                </a>
                                                @break
                                        @endswitch
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </article>
            </section>
        @endif
    </div>

    <div
        class="absolute top-0 bottom-0 left-0 right-0 max-w-7xl mx-auto mb-8 pl-5 pr-5 pb-6 text-white z-10 sm:px-6"
        x-show="loadingScreenEnabled"
    >
        <div class="fixed top-0 left-0 bottom-0 right-0 blur" style="background: url('{{ asset('images/static/star_bg_lg.jpg') }}') no-repeat center center; background-size: cover; margin: -1rem; z-index: -1;"></div>
        <div class="fixed top-0 left-0 bottom-0 right-0 blur backdrop-blur" style="z-index: -1;"></div>

        <div class="flex flex-col items-center justify-center w-full h-screen text-center">
            <p class="animate-pulse text-5xl font-black">{{ __('This is your Re:CAP.') }}</p>
        </div>
    </div>
</main>
