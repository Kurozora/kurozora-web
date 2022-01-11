<main>
    <x-slot name="title">
        {{ __('Welcome') }}
    </x-slot>

    <x-slot name="meta">
        <meta property="og:title" content="Welcome to {{ config('app.name') }}" />
        <meta property="og:description" content="{{ __('app.description') }}" />
        <meta property="og:image" content="{{ asset('images/static/promotional/social_preview_icon_only.webp') }}" />
        <meta property="og:type" content="website" />
        <link rel="canonical" href="{{ route('welcome') }}">
    </x-slot>

    <x-slot name="appArgument">
        welcome
    </x-slot>

    <div>
        {{-- Hero Video --}}
        <section class="h-screen max-h-[calc(100vh-64px)] bg-gray-200 overflow-hidden">
            <div class="w-full">
                <div class="absolute top-[50%] left-0 w-full text-white text-center z-10" style="transform: translateY(-50%); transition: opacity .6s,transform .6s;" data-anim-keyframe="{'start': '15vh', 'cssClass': 'hide', 'toggle': 'true'}">
                    <h1 class="flex items-center justify-center space-x-1 drop-shadow-[0_0_20px_rgba(0,0,0,.6)]">
                        {{-- Logo --}}
                        <x-logo class="relative h-6 md:h-8 w-auto" />
                        <span class="text-xl md:text-2xl lg:text-3xl font-semibold">Kurozora</span>
                    </h1>

                    <h2 class="mt-4 text-3xl md:text-6xl lg:text-8xl font-bold drop-shadow-[0_0_20px_rgba(0,0,0,.6)]" aria-label="Calling all puzzlers, athletes, wizards, warriors, players.">
                        <span>{{ __('Calling all') }}
                            <ul
                                class="inline-block m-0 mr-56"
                                x-data="{
                                    activeSlide: 0,
                                    slides: ['{{ __('watchers.') }}', '{{ __('readers.') }}', '{{ __('listeners.') }}', '{{ __('players.') }}', '{{ __('fans.') }}'],
                                }"
                                x-init=""
                            >
                                <template x-for="(slide, index) in slides" :key="index">
                                    <li
                                        class="block absolute top-0 bottom-0"
                                        x-show="activeSlide === index && index !== 5 && setTimeout(() => {activeSlide = activeSlide !== slides.length-1 ? activeSlide + 1 : 4}, 1000)"
                                        x-transition:enter="transition duration-1000"
                                        x-transition:enter-start="translate-y-full"
                                        x-transition:enter-end="translate-y-0"
                                        x-transition:leave="transition duration-1000"
                                        x-transition:leave-start=""
                                        x-transition:leave-end="-translate-y-full opacity-0"
                                        x-text="slide"
                                    >
                                    </li>
                                </template>
                            </ul>
                        </span>
                    </h2>

                    <p class="mt-12 space-x-4">
                        <x-link-button class="md:text-lg" href="{{ route('home') }}" aria-label="try apple arcade free">
                            <span class="icon-copy">{{ __('Try it free') }}</span>
                        </x-link-button>

                        <x-link-button class="md:text-lg" href="{{ config('app.ios.url') }}" aria-label="join kurozora beta">
                            <span class="icon-copy">{{ __('Join the beta') }}</span>
                        </x-link-button>
                    </p>
                </div>
            </div>

            <div
                x-data="{isPlaying: true}"
            >
                <video
                    class="h-screen w-full object-cover"
                    src="{{ asset('videos/hero_video.mov') }}"
                    autoplay
                    loop
                    muted
                    x-ref="hero_video"
                ></video>

                <div class="absolute top-0 left-0 h-full w-full bg-black/20"></div>

                <div class="absolute bottom-4 right-4">
                    <button
                        class="inline-flex items-center p-3 bg-white/60 backdrop-blur border border-transparent rounded-full font-semibold text-xs text-gray-500 uppercase tracking-widest shadow-md hover:opacity-75 active:opacity-50 focus:outline-none disabled:bg-gray-100 disabled:text-gray-300 disabled:cursor-default disabled:opacity-100 transition ease-in-out duration-150"
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

        {{-- Anime Showcase --}}
        <section class="mt-36">
            <div class="flex flex-col items-center mx-auto px-4 text-center sm:px-6">
                <img class="" width="82" height="82" src="{{ asset('images/static/icon/app_icon.webp') }}" alt="Kurozora">

                <p class="mt-4 text-xl font-semibold">{{ __('Kurozora') }}</p>

                <p class="max-w-2xl mt-2 text-6xl font-bold leading-tight">{{ __('Discover, track, share. All in one place.') }}</p>
            </div>

            <div class="flex flex-col mt-10 space-y-20 mb-5 md:flex-col-reverse md:space-y-reverse">
                <img src="{{ asset('images/static/placeholders/anime_banner.webp') }}" alt="">

                <div class="max-w-2xl mx-auto text-center">
                    <p class="text-2xl font-light">{{ __('Kurozora is the single destination for all the Japanese media you love — and all the ones you’ll love next. Discover new and classical anime, manga, music and games. Track what you’re watching and want to watch, and set your own watching goals — all in one app and across all your devices.') }}</p>
                </div>
            </div>
        </section>

        {{-- Anime Showcase --}}
        <section class="mt-36">
            <div class="max-w-3xl m-auto">
                <ul class="text-center">
                    <li class="m-10 mb-0 text-6xl font-bold leading-tight">
                        <span class="text-orange-500">{{ __('Biggest collection') }}</span> {{ __('of Japanse media — Anime, Manga, Music and Games.') }}
                    </li>
                    <li class="m-10 mb-0 text-6xl font-bold leading-tight">
                        <span class="text-lime-500">{{ __('20,000+ episodes') }}</span> {{ __('with more added all the time.') }}
                    </li>
                    <li class="m-10 mb-0 text-6xl font-bold leading-tight">
                        <span class="text-sky-500">{{ __('One subscription level.') }}</span> {{ __('No ads. Unlimited fun.') }}
                    </li>
                    <li class="m-10 mb-0 text-6xl font-bold leading-tight">
                        <span class="text-pink-500">{{ __('Share Kurozora') }}</span>  {{ __('with your family.') }}
                    </li>
                </ul>

                <div class="flex space-x-4 mt-16">
                    <div class="flex flex-col items-center text-center">
                        <p class="font-semibold">{{ __('Free 7-days trial') }}</p>
                        <p class="text-lg font-bold">{{ __('$3.99 / mo.') }}</p>
                        <p class="mt-4 text-gray-400">{{ __('After the free trial, get unlimited access to 20,000+ anime for the price of one anime box each month.') }}</p>

                        <x-button class="mt-4">{{ __('Try it free') }}*</x-button>
                    </div>

                    <div class="flex flex-col items-center text-center">
                        <p class="font-semibold">{{ __('Free 14-days trial') }}</p>
                        <p class="text-lg font-bold">{{ __('$11.99/ 6 mo.') }}</p>
                        <p class="mt-4 text-gray-400">{{ __('After the free trial, get unlimited access to 20,000+ anime for the price of one anime box each month.') }}</p>

                        <x-button class="mt-4">{{ __('Try it free') }}*</x-button>
                    </div>

                    <div class="flex flex-col items-center text-center">
                        <p class="font-semibold">{{ __('Free 1-month trial') }}</p>
                        <p class="text-lg font-bold">{{ __('$18.99 / 12 mo.') }}</p>
                        <p class="mt-4 text-gray-400">{{ __('After the free trial, get unlimited access to 20,000+ anime for the price of one anime box each month.') }}</p>

                        <x-button class="mt-4">{{ __('Try it free') }}*</x-button>
                    </div>
                </div>
            </div>
        </section>

        {{-- App Showcase --}}
        <section>
        </section>

        {{-- Call to Action --}}
        <section>
        </section>
    </div>
</main>
