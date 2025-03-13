<main>
    <x-slot:title>
        {{ __('Explore') }}
    </x-slot:title>

    <x-slot:meta>
        <meta property="og:title" content="{{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('home') }}">
        <x-misc.schema>
            "@type": "WebSite",
            "url": "{{ config('app.url') }}",
            "potentialAction": {
                "@type": "SearchAction",
                "target": {
                    "@type": "EntryPoint",
                    "urlTemplate": "{{ route('search.index') }}?q={search_term_string}&src={{ \App\Enums\SearchSource::Google }}"
                },
                "query-input": "required name=search_term_string"
            }
        </x-misc.schema>
    </x-slot:meta>

    <x-slot:appArgument>
        explore
    </x-slot:appArgument>

    <div>
{{--        <section>--}}
{{--            <a href="{{ route('recap.index') }}" wire:navigate>--}}
{{--                <x-picture>--}}
{{--                    <img class="w-full object-cover h-40 rounded-lg md:h-80" src="{{ asset('images/static/banners/kurozora_recap_2023.webp') }}" alt="Kurozora Recap 2023">--}}
{{--                </x-picture>--}}
{{--            </a>--}}
{{--        </section>--}}

{{--        <section>--}}
{{--            <x-picture>--}}
{{--                <img class="w-full object-cover h-32 rounded-lg sm:h-40 md:h-80" src="{{ asset('images/static/banners/games_now_on_kurozora.webp') }}" alt="Games and Manga tracking now available on Kurozora">--}}
{{--            </x-picture>--}}
{{--        </section>--}}

{{--        <section class="relative mb-8">--}}
{{--            <a href="{{ config('social.discord.url') }}" target="_blank" class="after:absolute after:inset-0">--}}
{{--                <x-picture>--}}
{{--                    <img class="h-32 w-full object-cover object-center rounded-lg shadow-lg sm:h-44" src="{{ asset('images/static/banners/kurozora_art_challenge_2022.webp') }}"  alt="Kurozora Art Challenge 2022" />--}}
{{--                </x-picture>--}}
{{--            </a>--}}
{{--        </section>--}}

        <section wire:init="loadPage">
            @foreach ($this->exploreCategories as $index => $exploreCategory)
                @switch($exploreCategory->type)
                @case(\App\Enums\ExploreCategoryTypes::MostPopularShows)
                    <section
                        class="relative"
                        x-data="carousel()"
                        x-init="startAutoScroll()"
                    >
                        <div
                            class="flex flex-nowrap snap-mandatory snap-x aspect-video overflow-x-scroll no-scrollbar natural-shadow-lg xl:rounded-b-2xl xl:ml-40 xl:mr-40"
                            x-ref="scrollContainer"
                            @mouseenter="pauseAutoScroll()"
                            @mouseleave="resumeAutoScroll()"
                            @scroll.passive="trackScrollPosition()"
                        >
                            @foreach ($exploreCategory->mostPopular(\App\Models\Anime::class)->exploreCategoryItems as $index => $categoryItem)
                                <x-lockups.banner-lockup :anime="$categoryItem->model" />
                            @endforeach
                        </div>

                        {{-- Play/Pause Button --}}
                        <div class="absolute top-4 right-4 xl:mr-40">
                            <button
                                class="inline-flex items-center pt-3 pr-3 pb-3 pl-3 bg-blur backdrop-blur border border-transparent rounded-full font-semibold text-xs uppercase tracking-widest shadow-md hover:opacity-75 active:opacity-50 focus:outline-none disabled:bg-secondary disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                                x-on:click="toggleAutoScroll()"
                            >
                                <template x-if="isPlaying">
                                    @svg('pause_fill', 'fill-current', ['width' => '24'])
                                </template>

                                <template x-if="!isPlaying">
                                    @svg('play_fill', 'fill-current', ['width' => '24'])
                                </template>
                            </button>
                        </div>
                    </section>

{{--                    <section class="relative pt-4 pb-8">--}}
{{--                        <x-section-nav class="flex flex-nowrap justify-between mb-5 pl-4 pr-4">--}}
{{--                            <x-slot:title>--}}
{{--                                {{ __('Art Contest ’24 Winners') }}--}}
{{--                            </x-slot:title>--}}

{{--                            <x-slot:description>--}}
{{--                                {{ __('Congrats to the winners 🎉') }}--}}
{{--                            </x-slot:description>--}}

{{--                            <x-slot:action>--}}
{{--                                <x-section-nav-link href="{{ config('social.discord.url') }}">{{ __('Join Art Contest ’25') }}</x-section-nav-link>--}}
{{--                            </x-slot:action>--}}
{{--                        </x-section-nav>--}}

{{--                        <div class="flex flex-nowrap gap-4 pl-4 pr-4 snap-mandatory snap-x overflow-x-scroll no-scrollbar">--}}
{{--                            @foreach ($this->users as $user)--}}
{{--                                @php--}}
{{--                                    switch ($user->id) {--}}
{{--                                    case 385:--}}
{{--                                        $backgroundColor = '#FFE15D';--}}
{{--                                        $textColor = '#915606';--}}
{{--                                        $imageURL = asset('images/mintdango.webp');--}}
{{--                                        $text = '1st Place';--}}
{{--                                        break;--}}
{{--                                    case 363:--}}
{{--                                        $backgroundColor = '#ECECEC';--}}
{{--                                        $textColor = '#8E4D09';--}}
{{--                                        $imageURL = asset('images/theplazmabeast.webp');--}}
{{--                                        $text = '2nd Place';--}}
{{--                                        break;--}}
{{--                                    case 765:--}}
{{--                                        $backgroundColor = '#CD7F32';--}}
{{--                                        $textColor = '#402000';--}}
{{--                                        $imageURL = asset('images/lat.webp');--}}
{{--                                        $text = '3rd Place';--}}
{{--                                        break;--}}
{{--                                    default:--}}
{{--                                        $backgroundColor = '#FFFFFF';--}}
{{--                                        $textColor = '#000000';--}}
{{--                                        $imageURL = '';--}}
{{--                                        $text = '';--}}
{{--                                    }--}}
{{--                                @endphp--}}

{{--                                <a class="relative pb-2 snap-normal snap-center min-w-[18rem] md:min-w-[30rem]" href="{{ route('profile.details', $user) }}" wire:navigate>--}}
{{--                                    <div class="h-full rounded-lg shadow-sm overflow-hidden" style="background-color: {{ $backgroundColor }};">--}}
{{--                                        <div class="relative flex justify-center bg-gray-800">--}}
{{--                                            <picture class="relative overflow-hidden">--}}
{{--                                                <img class="w-full h-full object-cover" style="max-height: 16rem;" width="300" height="168" src="{{ $imageURL }}" alt="{{ $user->username }} art contest 2024 submission" title="{{ $user->username }} art contest 2024 submission">--}}

{{--                                                <div class="absolute top-0 left-0 h-full w-full border-4 border-solid border-black/20 rounded-lg"></div>--}}
{{--                                            </picture>--}}
{{--                                        </div>--}}

{{--                                        <div class="flex flex-row px-6 py-6">--}}
{{--                                            <div class="flex justify-center mb-3 mr-2" style="max-height: 6rem;">--}}
{{--                                                <picture class="relative w-16 h-16 rounded-full shadow-lg overflow-hidden">--}}
{{--                                                    <img class="w-full h-full object-cover" width="160" height="160" src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}" alt="{{ $user->username }} Profile Image" title="{{ $user->username }}">--}}

{{--                                                    <div class="absolute top-0 left-0 h-full w-full border-2 border-solid border-black/20 rounded-full"></div>--}}
{{--                                                </picture>--}}
{{--                                            </div>--}}

{{--                                            <div class="flex flex-col">--}}
{{--                                                <h2 class="text-xl font-medium">{{ $user->username }}</h2>--}}

{{--                                                <span class="block" style="color: {{ $textColor }};">{{ $text }}</span>--}}

{{--                                                @if ($user->id == 385)--}}
{{--                                                    <ul class="list-disc block">--}}
{{--                                                        <li>Kurozora+ (6 month)</li>--}}
{{--                                                        <li>Kurozora PRO</li>--}}
{{--                                                        <li>$100 Gift Card</li>--}}
{{--                                                        <li>Art Contest ’24 Achievement</li>--}}
{{--                                                    </ul>--}}
{{--                                                @elseif ($user->id == 363)--}}
{{--                                                    <ul class="list-disc block">--}}
{{--                                                        <li>Kurozora+ (1 month)</li>--}}
{{--                                                        <li>Kurozora PRO</li>--}}
{{--                                                        <li>$75 Gift Card</li>--}}
{{--                                                        <li>Art Contest ’24 Achievement</li>--}}
{{--                                                    </ul>--}}
{{--                                                @elseif ($user->id == 765)--}}
{{--                                                    <ul class="list-disc block">--}}
{{--                                                        <li>Kurozora PRO</li>--}}
{{--                                                        <li>$50 Gift Card</li>--}}
{{--                                                        <li>Art Contest ’24 Achievement</li>--}}
{{--                                                    </ul>--}}
{{--                                                @endif--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </a>--}}
{{--                            @endforeach--}}
{{--                        </div>--}}
{{--                    </section>--}}
                    @break
                @default
                    <section>
                        <livewire:components.explore-category-section :index="$index" :exploreCategory="$exploreCategory" />
                    </section>
                @endswitch
            @endforeach
        </section>

        @if (!$readyToLoad)
            <section class="pb-6">
                <x-skeletons.small-lockup />
                <x-skeletons.small-lockup />
                <x-skeletons.small-lockup />
                <x-skeletons.small-lockup />
            </section>
        @endif

        @guest
            <section>
                <a href="{{ route('recap.index') }}" wire:navigate>
                    <x-picture>
                        <img class="h-40 w-full object-cover md:h-80" src="{{ asset('images/static/banners/kurozora_recap.webp') }}" alt="Kurozora Recap {{ now()->year }}">
                    </x-picture>
                </a>
            </section>
        @endguest

        <section class="pt-4 pb-8">
            <x-section-nav class="mb-5 flex flex-nowrap justify-between">
                <x-slot:title>
                    {{ __('More to Explore') }}
                </x-slot>
            </x-section-nav>

            <div class="grid gap-4 pl-4 pr-4 md:grid-cols-3">
                <x-simple-link href="{{ route('anime.seasons.index') }}" wire:navigate class="w-full justify-between rounded-lg bg-secondary pb-4 pl-4 pr-4 pt-4 text-sm hover:bg-tertiary active:bg-secondary" :hover-underline-enabled="false">
                    <span>
                        {{ __('Browse by Season') }}
                    </span>

                    @svg('chevron_forward', 'fill-current', ['width' => 12])
                </x-simple-link>

                <x-simple-link href="{{ route('genres.index') }}" wire:navigate class="w-full justify-between rounded-lg bg-secondary pb-4 pl-4 pr-4 pt-4 text-sm hover:bg-tertiary active:bg-secondary" :hover-underline-enabled="false">
                    <span>
                        {{ __('Browse by Genre') }}
                    </span>

                    @svg('chevron_forward', 'fill-current', ['width' => 12])
                </x-simple-link>

                <x-simple-link href="{{ route('themes.index') }}" wire:navigate class="w-full justify-between rounded-lg bg-secondary pb-4 pl-4 pr-4 pt-4 text-sm hover:bg-tertiary active:bg-secondary" :hover-underline-enabled="false">
                    <span>
                        {{ __('Browse by Theme') }}
                    </span>

                    @svg('chevron_forward', 'fill-current', ['width' => 12])
                </x-simple-link>

                <x-simple-link href="{{ route('schedule') }}" wire:navigate class="w-full justify-between rounded-lg bg-secondary pb-4 pl-4 pr-4 pt-4 text-sm hover:bg-tertiary active:bg-secondary" :hover-underline-enabled="false">
                    <span>
                        {{ __('Broadcast Schedule') }}
                    </span>

                    @svg('chevron_forward', 'fill-current', ['width' => 12])
                </x-simple-link>

                <x-simple-link href="{{ route('charts.index') }}" wire:navigate class="w-full justify-between rounded-lg bg-secondary pb-4 pl-4 pr-4 pt-4 text-sm hover:bg-tertiary active:bg-secondary" :hover-underline-enabled="false">
                    <span>
                        {{ __('Charts') }}
                    </span>

                    @svg('chevron_forward', 'fill-current', ['width' => 12])
                </x-simple-link>
            </div>
        </section>
    </div>

    <script>
        function carousel() {
            return {
                isPlaying: true,
                activeSlide: 0,
                scrollContainer: null,
                interval: null,
                totalSlides: 0,
                itemWidth: 0,

                startAutoScroll() {
                    this.scrollContainer = this.$refs.scrollContainer
                    let items = this.scrollContainer.children
                    this.totalSlides = items.length
                    this.itemWidth = items[0].offsetWidth

                    this.interval = setInterval(() => this.goToNextSlide(), 5000)
                },

                goToNextSlide() {
                    if (!this.isPlaying) return

                    this.activeSlide++

                    // If we reach the last item, smoothly scroll back to the first
                    if (this.activeSlide >= this.totalSlides) {
                        this.activeSlide = 0
                        this.scrollContainer.scrollTo({ left: 0, behavior: 'smooth' })
                    } else {
                        let nextItem = this.scrollContainer.children[this.activeSlide]
                        this.scrollContainer.scrollTo({
                            left: nextItem.offsetLeft,
                            behavior: 'smooth'
                        })
                    }
                },

                pauseAutoScroll() {
                    clearInterval(this.interval)
                },

                resumeAutoScroll() {
                    if (this.isPlaying) {
                        this.interval = setInterval(() => this.goToNextSlide(), 5000)
                    }
                },

                toggleAutoScroll() {
                    this.isPlaying = !this.isPlaying
                    if (this.isPlaying) {
                        this.resumeAutoScroll()
                    } else {
                        this.pauseAutoScroll()
                    }
                },

                trackScrollPosition() {
                    let items = this.scrollContainer.children
                    let closestIndex = Array.from(items).findIndex(item =>
                        Math.abs(item.offsetLeft - this.scrollContainer.scrollLeft) < item.offsetWidth / 2
                    )

                    if (closestIndex !== -1) {
                        this.activeSlide = closestIndex
                    }
                }
            };
        }
    </script>
</main>
