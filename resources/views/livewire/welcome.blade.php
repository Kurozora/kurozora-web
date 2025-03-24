<main>
    <x-slot:title>
        {{ __('Welcome') }}
    </x-slot:title>

    <x-slot:meta>
        <meta property="og:title" content="Welcome to {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('welcome') }}">
    </x-slot:meta>

    <x-slot:appArgument>
        welcome
    </x-slot:appArgument>

    <div>
        {{-- Hero Video --}}
        <section class="h-screen overflow-hidden" style="max-height: calc(100vh - 48px);">
            <div class="absolute left-0 h-screen w-full">
                <div class="absolute top-1/2 left-0 w-full text-white text-center z-10" style="transform: translateY(-50%); transition: opacity .6s,transform .6s;">
                    <h1 class="flex items-center justify-center space-x-1" style="text-shadow: 0 0 20px rgba(0,0,0,.6);">
                        {{-- Logo --}}
                        <x-logo class="relative h-6 md:h-8 w-auto" />
                        <span class="text-xl md:text-2xl lg:text-3xl font-semibold">{{ config('app.name') }}</span>
                    </h1>

                    <h2 class="mt-2 pb-2 px-5 text-5xl md:text-7xl lg:text-8xl font-bold md:px-0" style="text-shadow: 0 0 20px rgba(0,0,0,.6);">
                        <span>{{ __('Calling all') }} </span><span
                            class="relative inline-block md:w-[172px] lg:w-[231px]"
                            x-data="{
                                activeSlide: 0,
                            }"
                        >
                            <span
                                class="block top-0 left-0"
                                x-show="activeSlide === 0 && setTimeout(() => {activeSlide = 1}, 1000)"
                                :class="activeSlide === 0 ? 'relative' : 'absolute'"
                                x-transition:enter="transition duration-1000"
                                x-transition:enter-start="translate-y-full"
                                x-transition:enter-end="translate-y-0"
                                x-transition:leave="transition duration-1000"
                                x-transition:leave-start=""
                                x-transition:leave-end="-translate-y-full opacity-0"
                            >{{ __('watchers.') }}</span>
                            <span
                                class="block top-0 left-0"
                                x-show="activeSlide === 1 && setTimeout(() => {activeSlide = 2}, 1000)"
                                :class="activeSlide === 1 ? 'relative' : 'absolute'"
                                x-transition:enter="transition duration-1000"
                                x-transition:enter-start="translate-y-full"
                                x-transition:enter-end="translate-y-0"
                                x-transition:leave="transition duration-1000"
                                x-transition:leave-start=""
                                x-transition:leave-end="-translate-y-full opacity-0"
                            >{{ __('readers.') }}</span>
                            <span
                                class="block top-0 left-0"
                                x-show="activeSlide === 2 && setTimeout(() => {activeSlide = 3}, 1000)"
                                :class="activeSlide === 2 ? 'relative' : 'absolute'"
                                x-transition:enter="transition duration-1000"
                                x-transition:enter-start="translate-y-full"
                                x-transition:enter-end="translate-y-0"
                                x-transition:leave="transition duration-1000"
                                x-transition:leave-start=""
                                x-transition:leave-end="-translate-y-full opacity-0"
                            >{{ __('listeners.') }}</span>
                            <span
                                class="block top-0 left-0"
                                x-show="activeSlide === 3 && setTimeout(() => {activeSlide = 4}, 1000)"
                                :class="activeSlide === 3 ? 'relative' : 'absolute'"
                                x-transition:enter="transition duration-1000"
                                x-transition:enter-start="translate-y-full"
                                x-transition:enter-end="translate-y-0"
                                x-transition:leave="transition duration-1000"
                                x-transition:leave-start=""
                                x-transition:leave-end="-translate-y-full opacity-0"
                            >{{ __('players.') }}</span>
                            <span
                                class="block top-0 left-0"
                                x-show="activeSlide === 4"
                                :class="activeSlide === 4 ? 'relative' : ''"
                                x-transition:enter="transition duration-1000"
                                x-transition:enter-start="translate-y-full"
                                x-transition:enter-end="translate-y-0"
                                x-transition:leave="transition duration-1000"
                                x-transition:leave-start=""
                                x-transition:leave-end="-y-full opacity-0"
                            >{{ __('fans.') }}</span>
                        </span>
                    </h2>

                    <div class="flex flex-wrap gap-4 justify-center mt-12 pr-5 pl-5">
                        <x-link-button class="text-lg" href="{{ route('home') }}">
                            <span>{{ __('Visit the Website') }}</span>
                        </x-link-button>

                        <x-link-button class="text-lg" href="{{ config('app.ios.store_url') }}">
                            <span>{{ __('Download the App') }}</span>
                        </x-link-button>
                    </div>
                </div>
            </div>

            <div
                x-data="{isPlaying: true}"
            >
                <video
                    class="h-screen w-full object-cover"
                    src="{{ asset('videos/hero_video.mov') }}"
                    autoplay
                    playsinline
                    loop
                    muted
                    x-ref="hero_video"
                ></video>

                <div class="absolute top-0 left-0 h-screen w-full bg-black/20"></div>

                <div class="absolute bottom-4 right-4">
                    <button
                        class="inline-flex items-center pt-3 pr-3 pb-3 pl-3 bg-blur backdrop-blur border border-transparent rounded-full font-semibold text-xs uppercase tracking-widest shadow-md hover:bg-tint-800 hover:btn-text-tinted active:bg-tint active:btn-text-tinted focus:outline-none disabled:bg-secondary disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                        x-on:click="$refs.hero_video.paused ? $refs.hero_video.play() : $refs.hero_video.pause(); isPlaying = !$refs.hero_video.paused"
                    >
                        <template x-if="isPlaying">
                            @svg('pause_fill', 'fill-current', ['width' => '24'])
                        </template>

                        <template x-if="!isPlaying">
                            @svg('play_fill', 'fill-current', ['width' => '24'])
                        </template>
                    </button>
                </div>
            </div>
        </section>

        {{-- App Showcase --}}
        <section class="pl-4 pr-4 pt-36">
            <div class="flex flex-col items-center mx-auto max-w-2xl text-center">
                <img class="mt-10 mb-4" width="82" height="82" src="{{ asset('images/static/icon/app_icon.webp') }}" alt="{{ config('app.name') }}">

                <p class="my-2 text-xl font-semibold">{{ config('app.name') }}</p>

                <p class="text-4xl font-bold leading-tight tracking-tight md:text-6xl">{{ __('Discover, track, share. All in one place.') }}</p>
            </div>

            <div class="flex flex-col space-y-20 mt-10 overflow-hidden">
                <div class="max-w-2xl mx-auto text-center">
                    <p class="text-lg font-light md:text-2xl">{{ __(':x is the single destination for all the Japanese media you love — and all the ones you’ll love next. Discover new and classical anime, manga, music and games. Track what you’re watching and want to watch, and set your own watching goals — all in one app and across all your devices.', ['x' => config('app.name')]) }}</p>
                </div>

                <img src="{{ asset('images/static/promotional/kurozora_on_device.webp') }}" alt="{{ __(':x on device', ['x' => config('app.name')]) }}">
            </div>
        </section>

        {{-- Feature Showcase --}}
        <section class="pl-4 pr-4 pt-36 pb-10 bg-secondary">
            <div class="max-w-sm md:max-w-2xl m-auto">
                <ul class="m-auto text-center">
                    <span
                        x-data="{shown: false}"
                        x-intersect.full.once="shown = true"
                    >
                        <li
                            class="m-10 mb-0 text-2xl md:text-6xl font-bold leading-tight"
                            x-show="shown"
                            x-transition:enter="transition duration-1000 delay-100"
                            x-transition:enter-start="opacity-0 translate-y-full"
                            x-transition:enter-end="opacity-100 translate-y-0"
                        >
                            <span class="text-tint">{{ __('Biggest collection') }}</span> {{ __('of Japanese media — Anime, Manga, Music and Games.') }}
                        </li>
                    </span>
                    <span
                        x-data="{shown: false}"
                        x-intersect.full.once="shown = true"
                    >
                        <li
                            class="m-10 mb-0 text-2xl md:text-6xl font-bold leading-tight"
                            x-show="shown"
                            x-transition:enter="transition duration-1000 delay-200"
                            x-transition:enter-start="opacity-0 translate-y-full"
                            x-transition:enter-end="opacity-100 translate-y-0"
                        >
                            <span class="text-lime-500">{{ __('90,000+ Japanese media') }}</span> {{ __('with more added all the time.') }}
                        </li>
                    </span>
                    <span
                        x-data="{shown: false}"
                        x-intersect.full.once="shown = true"
                    >
                        <li
                            class="m-10 mb-0 text-2xl md:text-6xl font-bold leading-tight"
                            x-show="shown"
                            x-transition:enter="transition duration-1000 delay-[300ms]"
                            x-transition:enter-start="opacity-0 translate-y-full"
                            x-transition:enter-end="opacity-100 translate-y-0"
                        >
                            <span class="text-sky-500">{{ __('One subscription level.') }}</span> {{ __('No ads. Unlimited fun.') }}
                        </li>
                    </span>
                    <span
                        x-data="{shown: false}"
                        x-intersect.full.once="shown = true"
                    >
                        <li
                            class="m-10 mb-0 text-2xl md:text-6xl font-bold leading-tight"
                            x-show="shown"
                            x-transition:enter="transition duration-1000 delay-[400ms]"
                            x-transition:enter-start="opacity-0 translate-y-full"
                            x-transition:enter-end="opacity-100 translate-y-0"
                        >
                            <span class="text-pink-500">{{ __('Share :x', ['x' => config('app.name')]) }}</span>  {{ __('with your family.') }}
                        </li>
                    </span>
                </ul>

                <div
                    class="flex flex-wrap justify-between space-y-10 sm:space-y-0 mt-16"
                    x-data="{shown: false}"
                    x-intersect.full.once="shown = true"
                >
                    <div
                        class="flex flex-col flex-grow md:basis-1/3 items-center text-center"
                        x-show="shown"
                        x-transition:enter="transition duration-1000 delay-500"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                    >
                        <p class="font-semibold">{{ __('Free 7-days trial') }}</p>
                        <p class="text-lg font-bold">{{ __('$3.99 / mo.') }}</p>
                        <p class="mt-4 text-secondary">{{ __('A monthly subscription is just $3.99 per month after a free 7-day trial.') }}</p>

                        <x-link-button href="{{ config('app.ios.store_url') }}" class="mt-4">{{ __('Try it free') }}</x-link-button>
                    </div>

                    <div
                        class="flex flex-col flex-grow md:basis-1/3 items-center text-center"
                        x-show="shown"
                        x-transition:enter="transition duration-1000 delay-500"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                    >
                        <p class="font-semibold">{{ __('Free 14-days trial') }}</p>
                        <p class="text-lg font-bold">{{ __('$11.99 / 6 mo.') }}</p>
                        <p class="mt-4 text-secondary">{{ __('A half yearly subscription is just $11.99 per 6 months after a free 14-days trial.') }}</p>

                        <x-link-button href="{{ config('app.ios.store_url') }}" class="mt-4">{{ __('Try it free') }}</x-link-button>
                    </div>

                    <div
                        class="flex flex-col flex-grow md:basis-1/3 items-center text-center"
                        x-show="shown"
                         x-transition:enter="transition duration-1000 delay-500"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                    >
                        <p class="font-semibold">{{ __('Free 1-month trial') }}</p>
                        <p class="text-lg font-bold">{{ __('$18.99 / 12 mo.') }}</p>
                        <p class="mt-4 text-secondary">{{ __('A yearly subscription is just $18.99 per 12 months after a free 1-month trial.') }}</p>

                        <x-link-button href="{{ config('app.ios.store_url') }}" class="mt-4">{{ __('Try it free') }}</x-link-button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Anime Showcase --}}
        <section class="mt-36">
            <div class="flex flex-col items-center mx-auto pl-4 pr-4 max-w-2xl text-center">
                <p class="my-2 text-4xl font-bold leading-tight md:text-6xl">{{ __('Something for everyone.') }}</p>

                <p class="font-semibold md:text-xl">{{ __('Action, drama, ecchi, fantasy, space, thriller and more — with new amazing anime and updates added every week. Enjoy free, uninterrupted access to the anime you love and the ones you’re going to love.') }}</p>
            </div>

            <div
                class="relative mt-20"
                x-data="{
                    isPlaying: true,
                    isInReverse: false,
                    activeSlide: 0,
                    nextSlide: 1,
                    previousSlide: 4,
                    goToNextSlide() {
                        this.activeSlide = this.nextSlide
                    }
                }"
                x-init="
                    setInterval(() => { if (isPlaying) { goToNextSlide() } }, 5000)

                    $watch('activeSlide', (newValue, oldValue) => {
                        if (newValue >= 4) {
                            nextSlide = 0
                            previousSlide = 4
                        } else {
                            nextSlide = newValue + 1
                            previousSlide = oldValue
                        }
                    })
                "
            >
                <div class="relative flex h-screen w-full overflow-hidden">
                    <div
                        class="absolute h-full w-full"
                        style="background-color:#d3fbff;"
                        x-show="activeSlide === 0"
                        x-transition:enter="transition duration-1000"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition duration-1000"
                        x-transition:leave-start=""
                        x-transition:leave-end="-translate-x-full"
                    >
                        <div class="absolute top-0 left-0 bottom-0 right-0">
                            <img class="h-screen w-full object-cover lazyload" data-sizes="auto" data-src="https://cdn.kurozora.app/282337/ba723eab-e371-407b-b8c8-32a290a95141.webp?v=1724601483" alt="One Piece Banner Image" title="One Piece">

                            <div class="absolute top-0 left-0 right-0 h-screen" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0) 75%);"></div>
                        </div>

                        <div class="absolute bottom-4 left-4 right-4 m-auto max-w-7xl">
                            <p class="text-4xl text-white font-bold md:text-8xl">One Piece</p>
                            <p class="mt-4 text-white font-bold md:text-2xl">Action</p>
                            <x-link-button class="mt-8" href="/anime/one-piece">{{ __('Visit now') }}</x-link-button>
                        </div>
                    </div>
                    <div
                        class="absolute h-full w-full"
                        style="background-color:#ffffff;"
                        x-show="activeSlide === 1"
                        x-transition:enter="transition duration-1000"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition duration-1000"
                        x-transition:leave-start=""
                        x-transition:leave-end="-translate-x-full"
                    >
                        <div class="absolute top-0 left-0 bottom-0 right-0">
                            <img class="h-screen w-full object-cover lazyload" data-sizes="auto" data-src="https://cdn.kurozora.app/149812/0628d10a-bd75-4bab-81fa-7df7284c04d6.webp?v=1642184418" alt="Horimiya Banner Image" title="Horimiya">

                            <div class="absolute top-0 left-0 right-0 h-screen" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0) 75%);"></div>
                        </div>

                        <div class="absolute bottom-4 left-4 right-4 m-auto max-w-7xl">
                            <p class="text-4xl text-white font-bold md:text-8xl">Horimiya</p>
                            <p class="mt-4 text-white font-bold md:text-2xl">Romance</p>
                            <x-link-button class="mt-8" href="/anime/horimiya">{{ __('Visit now') }}</x-link-button>
                        </div>
                    </div>
                    <div
                        class="absolute h-full w-full"
                        style="background-color:#e2db3c;"
                        x-show="activeSlide === 2"
                        x-transition:enter="transition duration-1000"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition duration-1000"
                        x-transition:leave-start=""
                        x-transition:leave-end="-translate-x-full"
                    >
                        <div class="absolute top-0 left-0 bottom-0 right-0">
                            <img class="h-screen w-full object-cover lazyload" data-sizes="auto" data-src="https://cdn.kurozora.app/149152/23a88a10-cab6-4f1c-a4de-675ec9551e72.webp?v=1637193049" alt="Assassination Classroom Banner Image" title="Assassination Classroom">

                            <div class="absolute top-0 left-0 right-0 h-screen" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0) 75%);"></div>
                        </div>

                        <div class="absolute bottom-4 left-4 right-4 m-auto max-w-7xl">
                            <p class="text-4xl text-white font-bold md:text-8xl">Assassination Classroom</p>
                            <p class="mt-4 text-white font-bold md:text-2xl">School</p>
                            <x-link-button class="mt-8" href="/anime/assassination-classroom">{{ __('Visit now') }}</x-link-button>
                        </div>
                    </div>
                    <div
                        class="absolute h-full w-full"
                        style="background-color:#030005;"
                        x-show="activeSlide === 3"
                        x-transition:enter="transition duration-1000"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition duration-1000"
                        x-transition:leave-start=""
                        x-transition:leave-end="-translate-x-full"
                    >
                        <div class="absolute top-0 left-0 bottom-0 right-0">
                            <img class="h-screen w-full object-cover lazyload" data-sizes="auto" data-src="https://cdn.kurozora.app/149150/fe666971-78d6-4f40-8cd3-8b1428c4ce51.webp?v=1637192846" alt="Death Parade Banner Image" title="Death Parade">

                            <div class="absolute top-0 left-0 right-0 h-screen" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0) 75%);"></div>
                        </div>

                        <div class="absolute bottom-4 left-4 right-4 m-auto max-w-7xl">
                            <p class="text-4xl text-white font-bold md:text-8xl">Death Parade</p>
                            <p class="mt-4 text-white font-bold md:text-2xl">Thriller</p>
                            <x-link-button class="mt-8" href="/anime/death-parade">{{ __('Visit now') }}</x-link-button>
                        </div>
                    </div>
                    <div
                        class="absolute h-full w-full"
                        style="background-color:#f88e4e;"
                        x-show="activeSlide === 4"
                        x-transition:enter="transition duration-1000"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition duration-1000"
                        x-transition:leave-start=""
                        x-transition:leave-end="-translate-x-full"
                    >
                        <div class="absolute top-0 left-0 bottom-0 right-0">
                            <img class="h-screen w-full object-cover lazyload" data-sizes="auto" data-src="https://cdn.kurozora.app/149813/ad533ed0-9adb-4a4d-bcc0-7ff341b435e8.webp?v=1642184953" alt="Haikyu!! Banner Image" title="Haikyu!!">

                            <div class="absolute top-0 left-0 right-0 h-screen" style="background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0) 75%);"></div>
                        </div>

                        <div class="absolute bottom-4 left-4 right-4 m-auto max-w-7xl">
                            <p class="text-4xl text-white font-bold md:text-8xl">Haikyu!!</p>
                            <p class="mt-4 text-white font-bold md:text-2xl">Sports</p>
                            <x-link-button class="mt-8" href="/anime/haikyuu">{{ __('Visit now') }}</x-link-button>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-4 right-4">
                    <button
                        class="inline-flex items-center pt-3 pr-3 pb-3 pl-3 bg-blur backdrop-blur border border-transparent rounded-full font-semibold text-xs uppercase tracking-widest shadow-md hover:bg-tint-800 hover:btn-text-tinted active:bg-tint active:btn-text-tinted focus:outline-none disabled:bg-secondary disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
                        x-on:click="isPlaying = !isPlaying"
                    >
                        <template x-if="isPlaying">
                            @svg('pause_fill', 'fill-current', ['width' => '24'])
                        </template>

                        <template x-if="!isPlaying">
                            @svg('play_fill', 'fill-current', ['width' => '24'])
                        </template>
                    </button>
                </div>
            </div>
        </section>

        {{-- Call to Action --}}
        <section class="pl-4 pr-4 pt-36 pb-10 bg-secondary">
            <div class="flex flex-col items-center m-auto max-w-2xl text-center">
                <img class="mt-10 mb-4" width="82" height="82" src="{{ asset('images/static/icon/app_icon.webp') }}" alt="{{ config('app.name') }}">

                <p class="my-2 text-4xl font-bold leading-tight md:text-6xl">{{ __('Use :x anywhere you go.', ['x' => config('app.name')]) }}</p>

                <p class="font-semibold md:text-xl">{{ __('Explore on iPhone. Track on Mac. Share on iPad. Find the :x app on your favorite devices. Or use :x online at', ['x' => config('app.name')]) }} <x-link href="{{ route('home') }}">
                {{ config('app.domain') }}</x-link></p>
            </div>

            <div
                class="flex flex-wrap items-end justify-evenly m-auto mt-16 max-w-2xl sm:space-y-0"
                x-data="{shown: false}"
                x-intersect.full="shown = true"
            >
                <figure
                    class="flex flex-col basis-1/3 items-center text-center4 sm:basis-auto"
                    x-show="shown"
                    x-transition:enter="transition duration-1000"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                >
                    @svg('iphone_landscape', 'fill-current', ['width' => '64'])
                    <figcaption class="mb-6 text-sm font-semibold">iPhone & Android</figcaption>
                </figure>

                <figure
                    class="flex flex-col basis-1/3 items-center text-center4 sm:basis-auto"
                    x-show="shown"
                    x-transition:enter="transition duration-1000 delay-100"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                >
                    @svg('ipad_landscape', 'fill-current', ['width' => '64'])
                    <figcaption class="mb-6 text-sm font-semibold">iPad</figcaption>
                </figure>

                <figure
                    class="flex flex-col basis-1/3 items-center text-center4 sm:basis-auto"
                    x-show="shown"
                    x-transition:enter="transition duration-1000 delay-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                >
                    @svg('macbook', 'fill-current', ['width' => '64'])
                    <figcaption class="mb-6 text-sm font-semibold">Mac & Windows</figcaption>
                </figure>

                <figure
                    class="flex flex-col basis-1/3 items-center text-center4 sm:basis-auto"
                    x-show="shown"
                    x-transition:enter="transition duration-1000 delay-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                >
                    @svg('vision_pro', 'fill-current', ['width' => '64'])
                    <figcaption class="mb-6 text-sm font-semibold">Apple Vision Pro</figcaption>
                </figure>

                <figure
                    class="flex flex-col basis-1/3 items-center text-center4 sm:basis-auto"
                    x-show="shown"
                    x-transition:enter="transition duration-1000 delay-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                >
                    @svg('browser', 'fill-current', ['width' => '64'])
                    <figcaption class="mb-6 text-sm font-semibold">Web & PWA</figcaption>
                </figure>
            </div>

            <div class="flex flex-wrap gap-4 justify-center mt-12 pr-5 pl-5">
                <x-link-button class="text-lg" href="{{ route('home') }}">
                    <span>{{ __('Visit the Website') }}</span>
                </x-link-button>

                <x-link-button class="text-lg" href="{{ config('app.ios.store_url') }}">
                    <span>{{ __('Download The App') }}</span>
                </x-link-button>
            </div>
        </section>

        {{-- FAQ --}}
        <section class="mt-36 pl-4 pr-4">
            <div class="flex flex-col items-center m-auto max-w-2xl text-center">
                <p class="my-2 text-4xl font-bold leading-tight md:text-6xl">{{ __('Let’s go over it one last time.') }}</p>
            </div>

            <ul class="m-auto max-w-4xl">
                <li>
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('What is :x?', ['x' => config('app.name')]) }}</button>

                        <p
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            {{ __(':x is an open-source anime, manga, game and music discovery service that offers unlimited access to a growing collection of over 90,000 Japanese media combined — featuring new releases, award winners, and beloved favorites. :x also includes episodes, characters, voice actors, staff, studios, and much more — all without ads. You can track what you’re watching, reading, playing, and listening to on iPhone, iPad, iPod touch, Mac, and through any web browser.', ['x' => config('app.name')]) }}
                        </p>
                    </div>
                </li>
                <li class="border-t border-primary">
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('What’s included in :x?', ['x' => config('app.name')]) }}</button>

                        <div
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            <p>{{ __('In addition to anime, :x includes the biggest catalogue of manga, music and games.', ['x' => config('app.name')]) }}</p>
                            <br />
                            <p>{{ __('Here are some of the anime on :x. To see all 20,000+ anime, you can browse the Explore tab on the :x app or website.', ['x' => config('app.name')]) }}</p>

                            <div>
                                <ul class="list-disc">
                                    <li>
                                        <p><strong>One Piece</strong> (adventure) Following in the footsteps of his childhood hero, Monkey D. Luffy and his crew travel across the Grand Line, experiencing crazy adventures, unveiling dark mysteries and battling strong enemies, all in order to reach the most coveted of all fortunes — One Piece.</p>
                                    </li>
                                    <li>
                                        <p><strong>Horimiya</strong> (romance) Having opposite personalities yet sharing odd similarities, Izumi Miyamura and Kyouko Hori quickly become friends and often spend time together in Hori's home. As they both emerge from their shells, they share with each other a side of themselves concealed from the outside world.</p>
                                    </li>
                                    <li>
                                        <p><strong>Assassination Classroom</strong> (school) When a mysterious creature chops the moon down to a permanent crescent, the students of class 3-E find themselves confronted with an enormous task: assassinate the creature responsible for the disaster before Earth suffers a similar fate.</p>
                                    </li>
                                    <li>
                                        <p><strong>Detective Conan</strong> (mystery) After being forced to take an experimental drug formulated by the criminal Black Organisation, Shinichi Kudou, a famous high school detective finds himself in a seven-year-old child's body. Hiding his identity using the alias Edogawa Conan, the talented detective covertly investigates the organisation responsible for his current state, hoping to reverse the drug's effects someday.</p>
                                    </li>
                                    <li>
                                        <p><strong>Death Parade</strong> (thriller) After death, there is no heaven or hell, only a bar that stands between reincarnation and oblivion. Challenged with a random game, the recently deceased will wager their fate of either ascending into reincarnation or falling into the void.</p>
                                    </li>
                                    <li>
                                        <p><strong>Haikyu!!</strong> (sports) An exhilarating and emotional sports comedy following two determined athletes as they attempt to patch a heated rivalry in order to make their high school volleyball team the best in Japan.</p>
                                    </li>
                                    <li>
                                        <p><strong>My Hero Academia</strong> (super power) In a world with 80% of humanity possessing various super powers, Izuku Midoriya is born powerless. Since he was a child, the ambitious middle schooler has wanted nothing more than to be a hero. Izuku's unfair fate leaves him admiring heroes and taking notes on them whenever he can, until one day everything changes. With his bizarre but talented classmates and the looming threat of a villainous organization, Izuku will soon learn what it really means to be a hero.</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="border-t border-primary">
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('How often are new anime added?') }}</button>

                        <div
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            <p>{{ __('New anime, manga, games, music and other content updates are added to :x every day. To preview upcoming releases, look for the Upcoming section in the Explore tab on :x.', ['x' => config('app.name')]) }}</p>
                        </div>
                    </div>
                </li>
                <li class="border-t border-primary">
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('Where do I find :x?', ['x' => config('app.name')]) }}</button>

                        <div
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            <p>{{ __('You can download the app on the') }} <x-link target="_blank" href="{{ config('app.ios.store_url') }}">App Store</x-link> {{ __('on your favorite device. :x works on iPhone, iPad, iPod Touch, Mac, and Apple Vision Pro.', ['x' => config('app.name')]) }}</p>
                            <br />
                            <br />
                            <p>{{ __(':x can also be used on Android, Windows, Linux and all other devices through the website, or as a Progressive Web App (PWA), at', ['x' => config('app.name')]) }} <x-link href="{{ route('home') }}">{{ config('app.domain') }}</x-link>.</p>
                        </div>
                    </div>
                </li>
                <li class="border-t border-primary">
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('What does it cost?') }}</button>

                        <div
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            <p>{{ __(':x is free to download and use. However, some supplemental features can be unlocked using in-app purchases. The cost depends on which offer you choose. All offers unlock the same features.', ['x' => config('app.name')]) }}</p>
                            <br>
                            <br>
                            <p>{{ __('(1) Free to try for 1 week and $3.99 per month after that.') }}</p><br>
                            <p>{{ __('(2) Free to try for 14 days and $11.99 per 6 months after that. Save up to 50%.') }}</p><br>
                            <p>{{ __('(3) Free to try for 1 month and $18.99 per 12 months after that. Save up to 60%.') }}</p>
                        </div>
                    </div>
                </li>
                <li class="border-t border-primary">
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('What do I need to join :x?', ['x' => config('app.name')]) }}</button>

                        <div
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            <p>{{ __('You will need an iPhone, iPad, iPod Touch or a Mac with the latest operating system for the app. You can use any browser you prefer for the website. You can explore :x without an account, however some features might require an account to work.', ['x' => config('app.name')]) }}</p>
                        </div>
                    </div>
                </li>
                <li class="border-t border-primary">
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('Where can I find more about :x?', ['x' => config('app.name')]) }}</button>

                        <div
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            <p>{{ __('You can join the :x community on', ['x' => config('app.name')]) }} <x-link target="_blank" href="{{ config('social.discord.url') }}">Discord</x-link> {{ __('as well as follow us on') }} <x-link target="_blank" href="{{ config('social.twitter.url') }}">Twitter</x-link> {{ __('and') }} <x-link target="_blank" href="{{ config('social.instagram.url') }}">Instagram</x-link></p>
                        </div>
                    </div>
                </li>
            </ul>
        </section>
    </div>
</main>
