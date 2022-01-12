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
            <div class="absolute left-0 h-screen w-full">
                <div class="absolute top-1/2 left-0 w-full text-white text-center z-10" style="transform: translateY(-50%); transition: opacity .6s,transform .6s;">
                    <h1 class="flex items-center justify-center space-x-1" style="text-shadow: 0 0 20px rgba(0,0,0,.6);">
                        {{-- Logo --}}
                        <x-logo class="relative h-6 md:h-8 w-auto" />
                        <span class="text-xl md:text-2xl lg:text-3xl font-semibold">Kurozora</span>
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

                    <div class="mt-12 space-y-4 md:space-x-4 md:space-y-0">
                        <x-link-button class="md:text-lg" href="{{ route('home') }}" aria-label="visit kurozora website">
                            <span>{{ __('Visit the website') }}</span>
                        </x-link-button>

                        <x-link-button class="md:text-lg" href="{{ config('app.ios.url') }}" aria-label="download kurozora app">
                            <span>{{ __('Download the app') }}</span>
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
        <section class="mt-36 px-4 sm:px-6">
            <div class="flex flex-col items-center mx-auto max-w-2xl text-center">
                <img class="m-10 mb-0" width="82" height="82" src="{{ asset('images/static/icon/app_icon.webp') }}" alt="Kurozora">

                <p class="my-2 text-xl font-semibold">{{ __('Kurozora') }}</p>

                <p class="text-4xl font-bold leading-tight tracking-tight md:text-6xl">{{ __('Discover, track, share. All in one place.') }}</p>
            </div>

            <div class="flex flex-col space-y-20 my-10 md:flex-col-reverse md:space-y-reverse md:mb-0">
                <img src="{{ asset('images/static/promotional/kurozora_on_device.webp') }}" alt="">

                <div class="max-w-2xl mx-auto text-center">
                    <p class="text-lg font-light md:text-2xl">{{ __('Kurozora is the single destination for all the Japanese media you love — and all the ones you’ll love next. Discover new and classical anime, manga, music and games. Track what you’re watching and want to watch, and set your own watching goals — all in one app and across all your devices.') }}</p>
                </div>
            </div>
        </section>

        {{-- Feature Showcase --}}
        <section class="px-4 pt-36 pb-10 bg-gray-100 sm:px-6">
            <div class="max-w-sm md:max-w-2xl m-auto">
                <ul class="m-auto text-center">
                    <li class="m-10 mb-0 text-2xl md:text-6xl font-bold leading-tight">
                        <span class="text-orange-500">{{ __('Biggest collection') }}</span> {{ __('of Japanse media — Anime, Manga, Music and Games.') }}
                    </li>
                    <li class="m-10 mb-0 text-2xl md:text-6xl font-bold leading-tight">
                        <span class="text-lime-500">{{ __('20,000+ anime') }}</span> {{ __('with more added all the time.') }}
                    </li>
                    <li class="m-10 mb-0 text-2xl md:text-6xl font-bold leading-tight">
                        <span class="text-sky-500">{{ __('One subscription level.') }}</span> {{ __('No ads. Unlimited fun.') }}
                    </li>
                    <li class="m-10 mb-0 text-2xl md:text-6xl font-bold leading-tight">
                        <span class="text-pink-500">{{ __('Share Kurozora') }}</span>  {{ __('with your family.') }}
                    </li>
                </ul>

                <div class="flex flex-wrap justify-between space-y-8 sm:space-y-0 mt-16">
                    <div class="flex flex-col flex-grow md:basis-1/3 items-center text-center">
                        <p class="font-semibold">{{ __('Free 7-days trial') }}</p>
                        <p class="text-lg font-bold">{{ __('$3.99 / mo.') }}</p>
                        <p class="mt-4 text-gray-400">{{ __('A monthly subscription is just $3.99 per month after a free 7-day trial.') }}</p>

                        <x-link-button href="{{ config('app.ios.url') }}" class="mt-4">{{ __('Try it free') }}</x-link-button>
                    </div>

                    <div class="flex flex-col flex-grow md:basis-1/3 items-center text-center">
                        <p class="font-semibold">{{ __('Free 14-days trial') }}</p>
                        <p class="text-lg font-bold">{{ __('$11.99 / 6 mo.') }}</p>
                        <p class="mt-4 text-gray-400">{{ __('A half yearly subscription is just $11.99 per 6 months after a free 14-days trial.') }}</p>

                        <x-link-button href="{{ config('app.ios.url') }}" class="mt-4">{{ __('Try it free') }}</x-link-button>
                    </div>

                    <div class="flex flex-col flex-grow md:basis-1/3 items-center text-center">
                        <p class="font-semibold">{{ __('Free 1-month trial') }}</p>
                        <p class="text-lg font-bold">{{ __('$18.99 / 12 mo.') }}</p>
                        <p class="mt-4 text-gray-400">{{ __('A yearly subscription is just $18.99 per 12 months after a free 1-month trial.') }}</p>

                        <x-link-button href="{{ config('app.ios.url') }}" class="mt-4">{{ __('Try it free') }}</x-link-button>
                    </div>
                </div>
            </div>
        </section>

        {{-- App Showcase --}}
        <section>
        </section>

        {{-- Call to Action --}}
        <section class="mt-36 px-4 pb-10 sm:px-6">
            <div class="flex flex-col items-center m-auto max-w-2xl text-center">
                <img class="m-10 mb-0" width="82" height="82" src="{{ asset('images/static/icon/app_icon.webp') }}" alt="Kurozora">

                <p class="my-2 text-4xl font-bold leading-tight md:text-6xl">{{ __('Use Kurozora anywhere you go.') }}</p>

                <p class="font-semibold md:text-xl">{{ __('Explore on iPhone. Track on Mac. Share on iPad. Find the Kurozora app on your favorite Apple devices. Or use Kurozora online at') }} <x-link href="{{ route('home') }}">
                {{ config('app.domain') }}</x-link></p>
            </div>

            <div class="flex flex-wrap space-y-4 m-auto mt-16 max-w-2xl sm:space-y-0">
                <div class="flex flex-col basis-1/2 items-center text-center sm:basis-1/4">
                    @svg('iphone_landscape', 'fill-current', ['width' => '82'])
                    <p class="font-semibold">iPhone</p>
                </div>

                <div class="flex flex-col basis-1/2 items-center text-center sm:basis-1/4">
                    @svg('ipad_landscape', 'fill-current', ['width' => '82'])
                    <p class="font-semibold">iPad</p>
                </div>

                <div class="flex flex-col basis-1/2 items-center text-center sm:basis-1/4">
                    @svg('macbook', 'fill-current', ['width' => '82'])
                    <p class="font-semibold">Mac</p>
                </div>

                <div class="flex flex-col basis-1/2 items-center text-center sm:basis-1/4">
                    @svg('browser', 'fill-current', ['width' => '82'])
                    <p class="font-semibold">Web</p>
                </div>
            </div>

            <div class="mt-12 space-y-4 md:space-x-4 md:space-y-0 text-center">
                <x-link-button class="md:text-lg" href="{{ route('home') }}" aria-label="visit kurozora website">
                    <span>{{ __('Visit the website') }}</span>
                </x-link-button>

                <x-link-button class="md:text-lg" href="{{ config('app.ios.url') }}" aria-label="download kurozora app">
                    <span>{{ __('Download the app') }}</span>
                </x-link-button>
            </div>
        </section>

        <section class="mt-36 px-4 sm:px-6">
            <div class="flex flex-col items-center m-auto max-w-2xl text-center">
                <p class="my-2 text-4xl font-bold leading-tight md:text-6xl">{{ __('Let’s go over it one last time.') }}</p>
            </div>

            <ul class="m-auto px-4 max-w-4xl">
                <li>
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('What is Kurozora?') }}</button>

                        <p
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            {{ __('Kurozora is an anime discovering service that offers unlimited access to a growing collection of over 20,000 anime — featuring new releases, award winners, and beloved favorites. Kurozora also includes manga, and anime related music and games — all without ads. You can track anime on iPhone, iPad, iPod touch, Mac, and through any web browser.') }}
                        </p>
                    </div>
                </li>
                <li class="border-t">
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('What’s included in Kurozora?') }}</button>

                        <div
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            <p>{{ __('In addition to anime, Kurozora includes the biggest catalogue of manga, music and games.') }}</p>
                            <br />
                            <p>{{ __('Here are some of the anime on Kurozora. To see all 20,000+ anime, you can browse the Explore tab on the Kurozora app or website.') }}</p>

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
                <li class="border-t">
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
                            <p>{{ __('New anime and other content updates are added to Kurozora every week. To preview upcoming releases, look for the Upcoming Shows section in the Explore tab on Kurozora.') }}</p>
                        </div>
                    </div>
                </li>
                <li class="border-t">
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('Where do I find Kurozora?') }}</button>

                        <div
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            <p>{{ __('You can join the beta of Kurozora through') }} <x-link target="_blank" href="{{ config('app.ios.url') }}">TestFlight</x-link> {{ __('on your favorite Apple device. Kurozora works on iPhone, iPad, iPod Touch and Mac.') }}</p>
                            <br />
                            <br />
                            <p>{{ __('Kurozora can also be used on all other devices through the website at') }} <x-link href="{{ route('home') }}">kurozora.app</x-link>.</p>
                        </div>
                    </div>
                </li>
                <li class="border-t">
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
                            <p>{{ 'Kurozora is free to download and use. However, some supplemental features can be unlocked using in-app purchases. The cost depends on which offer you choose. All offers unlock the same features.' }}</p>
                            <br>
                            <br>
                            <p>{{ __('(1) Free to try for 1 week and $3.99 per month after that.') }}</p><br>
                            <p>{{ __('(2) Free to try for 14 days and $11.99 per 6 months after that. Save up to 50%.') }}</p><br>
                            <p>{{ __('(3) Free to try for 1 month and $18.99 per 12 months after that. Save up to 60%.') }}</p>
                        </div>
                    </div>
                </li>
                <li class="border-t">
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('What do I need to join Kurozora?') }}</button>

                        <div
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            <p>{{ __('You will need an iPhone, iPad, iPod Touch or a Mac with the latest operating system for the app. You can use any browser you prefer for the website. You can explore Kurozora without an account, however some features might require an account to work.') }}</p>
                        </div>
                    </div>
                </li>
                <li class="border-t">
                    <div x-data="{ expanded: false }">
                        <button
                            class="flex mb-2 py-6 w-full text-xl text-unset font-semibold"
                            @click="expanded = ! expanded"
                        >{{ __('Where can I find more about Kurozora?') }}</button>

                        <div
                            class="pb-8"
                            x-show="expanded"
                            x-collapse
                        >
                            <p>{{ __('You can join the Kurozora community on') }} <x-link target="_blank" href="https://discord.gg/f3QFzGqsah">Discord</x-link> {{ __('as well as follow us on') }} <x-link target="_blank" href="https://twitter.com/KurozoraApp">Twitter</x-link> {{ __('and') }} <x-link target="_blank" href="https://instagram.com/kurozora_app">Instagram</x-link></p>
                        </div>
                    </div>
                </li>
            </ul>
        </section>
    </div>
</main>
