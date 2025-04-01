<aside class="hidden flex-col w-64 h-screen bg-primary z-[999] overflow-hidden xl:fixed xl:flex">
    <nav class="flex flex-col h-full border-e border-primary">
        <div class="flex flex-col gap-4 mt-4 mr-4 ml-4">
            <a
                class="inline-flex items-center w-full space-x-2 text-secondary transition duration-150 ease-in-out hover:text-primary focus:text-primary"
                href="/"
                wire:navigate
                x-transition:enter="ease-out duration-150 delay-100 transform sm:delay-[0ms]"
                x-transition:enter-start="opacity-0 scale-75"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200 delay-[50ms] transform sm:delay-[400ms]"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-75"
            >
                <x-app-icon />

                <p class="text-2xl font-bold">{{ config('app.name') }}</p>
            </a>

            <div class="w-full">
                <form
                    id="search"
                    class="relative flex items-center gap-2 w-full"
                    action="{{ route('search.index') }}"
                    method="get"
                    x-transition:enter="ease duration-[500ms] delay-300 transform"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    {{-- Search icon --}}
                    <div class="absolute left-0 flex pl-4 h-full text-secondary sm:pl-3">
                        @svg('magnifyingglass', 'fill-current', ['width' => '14'])
                    </div>

                    {{-- Search field --}}
                    <x-input
                        class="pr-8 pl-8 h-8 w-full text-sm"
                        type="search"
                        name="q"
                        placeholder="{{ [__('Search'), '⌘+K, ctrl+K or /'][array_rand([0,1])] }}"
                        x-ref="search"
                    />
                </form>
            </div>
        </div>

        <div class="flex flex-col justify-between w-full h-full overflow-y-scroll">
            <div class="mt-4 mr-4 ml-4">
                <section>
                    <div>
                        <h2 class="text-secondary text-sm font-semibold">{{ config('app.name') }}</h2>
                    </div>

                    <x-sidebar-nav-link href="{{ route('home') }}" wire:navigate :active="request()->routeIs('home')">
                        @svg('house', 'fill-current', ['width' => '18']) {{ __('Explore') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('anime.index') }}" wire:navigate :active="request()->routeIs('anime.index')">
                        @svg('tv', 'fill-current', ['width' => '18'])  {{ __('Anime') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('manga.index') }}" wire:navigate :active="request()->routeIs('manga.index')">
                        @svg('book', 'fill-current', ['width' => '18'])  {{ __('Manga') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('games.index') }}" wire:navigate :active="request()->routeIs('games.index')">
                        @svg('gamecontroller', 'fill-current', ['width' => '18']) {{ __('Game') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="#">
                        @svg('person_tv', 'fill-current', ['width' => '18']) {{ __('Live Action') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('songs.index') }}" wire:navigate :active="request()->routeIs('songs.index')">
                        @svg('music_note', 'fill-current', ['width' => '18']) {{ __('Songs') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('schedule') }}" wire:navigate :active="request()->routeIs('schedule')">
                        @svg('calendar', 'fill-current', ['width' => '18']) {{ __('Schedule') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('charts.index') }}" wire:navigate :active="request()->routeIs('charts.index')">
                        @svg('chart_bar', 'fill-current', ['width' => '18']) {{ __('Charts') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('characters.index') }}" wire:navigate :active="request()->routeIs('characters.index')">
                        @svg('totoro', 'fill-current', ['width' => '18']) {{ __('Characters') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('people.index') }}" wire:navigate :active="request()->routeIs('people.index')">
                        @svg('person', 'fill-current', ['width' => '18']) {{ __('People') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('studios.index') }}" wire:navigate :active="request()->routeIs('studios.index')">
                        @svg('building_2', 'fill-current', ['width' => '18']) {{ __('Studios') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('platforms.index') }}" wire:navigate :active="request()->routeIs('platforms.index')">
                        @svg('tv_and_mediabox', 'fill-current', ['width' => '18']) {{ __('Platforms') }}
                    </x-sidebar-nav-link>
                </section>

                <section>
                    <div>
                        <h2 class="text-secondary text-sm font-semibold">{{ __('Library') }}</h2>
                    </div>

                    <x-sidebar-nav-link href="{{ route('profile.anime.library', $user) }}" wire:navigate :active="request()->routeIs('profile.anime.library', $user)">
                        @svg('tv', 'fill-current', ['width' => '18']) {{ __('Anime') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('profile.manga.library', $user) }}" wire:navigate :active="request()->routeIs('profile.manga.library', $user)">
                        @svg('book', 'fill-current', ['width' => '18']) {{ __('Manga') }}
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link href="{{ route('profile.games.library', $user) }}" wire:navigate :active="request()->routeIs('profile.games.library', $user)">
                        @svg('gamecontroller', 'fill-current', ['width' => '18']) {{ __('Games') }}
                    </x-sidebar-nav-link>
                </section>
            </div>

            <a href="{{ empty($user) ? route('sign-in') : route('me') }}" class="flex items-center pl-4 pt-4 pr-4 pb-4" wire:navigate>
                @auth
                    <div class="shrink-0">
                        <x-picture>
                            <img
                                class="w-10 h-10 object-cover rounded-full lazyload"
                                data-sizes="auto"
                                data-src="{{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }}"
                                alt="{{ $user->username }} Profile Image"
                                title="{{ $user->username }}"
                            >

                            <div class="absolute top-0 left-0 h-full w-full border border-solid border-black/20 rounded-full"></div>
                        </x-picture>
                    </div>

                    <div class="ml-3">
                        <div class="font-medium text-base text-primary">{{ $user->username }}</div>
                    </div>
                @else
                    <div class="shrink-0">
                        <div
                            class="h-10 w-10 bg-cover rounded-full"
                            style="background-image: url({{ $user?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/user_profile.webp') }});"
                            alt="{{ $user?->username ?? __('Guest') }} {{ __('Profile') }}"
                            title="{{ $user?->username ?? __('Guest') }}"
                            role="img"
                        ></div>
                    </div>

                    <div class="ml-3">
                        <div class="font-medium text-base text-primary">{{ __('Sign In') }}</div>
                    </div>
                @endauth
            </a>
        </div>
    </nav>
</aside>
