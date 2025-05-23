<div
    class="xl:hidden"
    x-data="{
        isSearchEnabled: @entangle('isSearchEnabled').live,
        isNavOpen: false
    }"
>
    <nav class="relative bg-primary text-primary border-b border-black/20 z-[300]">
        {{-- Primary Navigation Menu --}}
        <div class="pl-4 pr-4">
            <div class="flex justify-between gap-2 h-12">
                {{-- Hamburger --}}
                <div
                    class="-mr-2 flex items-center md:hidden"
                    x-show="! isSearchEnabled"
                    x-transition:enter="ease-out duration-150 delay-[50ms] transform sm:delay-300"
                    x-transition:enter-start="opacity-0 scale-75"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200 delay-100 transform sm:delay-[50ms]"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-75"
                >
                    <button
                        class="inline-flex items-center justify-center pt-2 pr-2 pb-2 pl-2 rounded-md text-secondary hover:text-primary hover:bg-tertiary focus:bg-secondary focus:text-primary transition duration-150 ease-in-out"
                        x-on:click="isNavOpen = ! isNavOpen"
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
                                x-cloak
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

                <div class="flex gap-2 w-full">
                    {{-- Logo --}}
                    <a class="inline-flex items-center h-full m-auto text-secondary transition duration-150 ease-in-out hover:text-primary focus:text-primary md:hidden"
                       href="/"
                       wire:navigate
                       x-show="! isSearchEnabled"
                       x-transition:enter="ease-out duration-150 delay-100 transform sm:delay-[0ms]"
                       x-transition:enter-start="opacity-0 scale-75"
                       x-transition:enter-end="opacity-100 scale-100"
                       x-transition:leave="ease-in duration-200 delay-[50ms] transform sm:delay-[400ms]"
                       x-transition:leave-start="opacity-100 scale-100"
                       x-transition:leave-end="opacity-0 scale-75"
                    >
                        <x-app-icon />
                    </a>

                    {{-- Navigation Links --}}
                    <div class="flex items-center justify-between md:-my-px md:w-full">
                        {{-- Logo --}}
                        <a class="hidden md:inline-flex items-center h-full text-secondary transition duration-150 ease-in-out hover:text-primary focus:text-primary md:pt-1"
                           href="/"
                           wire:navigate
                           x-show="! isSearchEnabled"
                           x-transition:enter="ease-out duration-150 delay-100 transform sm:delay-[0ms]"
                           x-transition:enter-start="opacity-0 scale-75"
                           x-transition:enter-end="opacity-100 scale-100"
                           x-transition:leave="ease-in duration-200 delay-[50ms] transform sm:delay-[400ms]"
                           x-transition:leave-start="opacity-100 scale-100"
                           x-transition:leave-end="opacity-0 scale-75"
                        >
                            <x-app-icon />
                        </a>

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('home') }}" wire:navigate :active="request()->routeIs('home')"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-[50ms] transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 delay-[350ms] transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                        >
                            {{ __('Explore') }}
                        </x-nav-link>

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('anime.index') }}" wire:navigate :active="request()->routeIs('anime.index')"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-100 transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 delay-300 transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                        >
                            {{ __('Anime') }}
                        </x-nav-link>

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('manga.index') }}" wire:navigate :active="request()->routeIs('manga.index')"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-150 transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 delay-[250ms] transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                        >
                            {{ __('Manga') }}
                        </x-nav-link>

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('games.index') }}" wire:navigate :active="request()->routeIs('games.index')"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-100 transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 delay-300 transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                        >
                            {{ __('Game') }}
                        </x-nav-link>

{{--                        <x-nav-link class="hidden md:inline-flex" href="#"--}}
{{--                                    x-show="! isSearchEnabled"--}}
{{--                                    x-transition:enter="ease-out duration-150 delay-100 transform"--}}
{{--                                    x-transition:enter-start="opacity-0 scale-75"--}}
{{--                                    x-transition:enter-end="opacity-100 scale-100"--}}
{{--                                    x-transition:leave="ease-in duration-200 delay-300 transform"--}}
{{--                                    x-transition:leave-start="opacity-100 scale-100"--}}
{{--                                    x-transition:leave-end="opacity-0 scale-75"--}}
{{--                        >--}}
{{--                            {{ __('Live Action') }}--}}
{{--                        </x-nav-link>--}}

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('songs.index') }}" wire:navigate :active="request()->routeIs('songs.index')"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-200 transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 delay-200 transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                        >
                            {{ __('Songs') }}
                        </x-nav-link>

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('schedule') }}" wire:navigate :active="request()->routeIs('schedule')"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-200 transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 delay-200 transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                        >
                            {{ __('Schedule') }}
                        </x-nav-link>

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('charts.index') }}" wire:navigate :active="request()->routeIs('charts.index')"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-[250ms] transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 delay-150 transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                        >
                            {{ __('Charts') }}
                        </x-nav-link>

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('characters.index') }}" wire:navigate :active="request()->routeIs('characters.index')"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-300 transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 delay-100 transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                        >
                            {{ __('Characters') }}
                        </x-nav-link>

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('people.index') }}" wire:navigate :active="request()->routeIs('people.index')"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-[350ms] transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 delay-75 transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                        >
                            {{ __('People') }}
                        </x-nav-link>

                        <x-nav-link class="hidden md:inline-flex" href="{{ route('studios.index') }}" wire:navigate :active="request()->routeIs('studios.index')"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-[400ms] transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 delay-[50ms] transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                        >
                            {{ __('Studios') }}
                        </x-nav-link>

{{--                        <x-nav-link class="hidden md:inline-flex" href="{{ route('platforms.index') }}" wire:navigate :active="request()->routeIs('platforms.index')"--}}
{{--                                    x-show="! isSearchEnabled"--}}
{{--                                    x-transition:enter="ease-out duration-150 delay-[400ms] transform"--}}
{{--                                    x-transition:enter-start="opacity-0 scale-75"--}}
{{--                                    x-transition:enter-end="opacity-100 scale-100"--}}
{{--                                    x-transition:leave="ease-in duration-200 delay-[50ms] transform"--}}
{{--                                    x-transition:leave-start="opacity-100 scale-100"--}}
{{--                                    x-transition:leave-end="opacity-0 scale-75"--}}
{{--                        >--}}
{{--                            {{ __('Platforms') }}--}}
{{--                        </x-nav-link>--}}

                        {{-- Search --}}
                        <button class="inline-flex h-full w-8 items-center justify-center text-secondary cursor-pointer transition duration-150 ease-in-out hover:text-primary focus:text-primary"
                                x-show="! isSearchEnabled"
                                x-on:click="isNavOpen = false; isSearchEnabled = ! isSearchEnabled;"
                                x-transition:enter="ease-out duration-150 delay-150 transform sm:delay-300"
                                x-transition:enter-start="opacity-0 scale-75"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="ease-in duration-200 transform sm:delay-[50ms]"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-75"
                        >
                            @svg('magnifyingglass', 'fill-current', ['width' => '18'])
                        </button>

                        <x-dropdown id="nav-notification" align="right" width="64">
                            <x-slot:trigger>
                                <button
                                    class="inline-flex h-8 w-8 items-center justify-center text-secondary cursor-pointer transition duration-150 ease-in-out hover:text-primary focus:text-primary"
                                    x-show="! isSearchEnabled"
                                    wire:click="$dispatch('is-notifications-open', { 'isOpen': true })"
                                    x-transition:enter="ease-out duration-150 delay-[350ms] transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                                >
                                    @svg('app_badge', 'fill-current', ['width' => '18'])
                                </button>

                                <x-slot:content>
                                    <livewire:nav-notification />
                                </x-slot:content>
                            </x-slot:trigger>
                        </x-dropdown>

                        {{-- Settings Dropdown --}}
                        <x-dropdown id="more-settings" align="right" width="48" content-classes="hidden bg-secondary md:block">
                            <x-slot:trigger>
                                <button
                                    class="hidden md:flex text-sm border-2 border-transparent rounded-full transition duration-150 ease-in-out focus:border-primary"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-[350ms] transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                                >
                                    <div
                                        class="h-6 w-6 bg-cover rounded-full"
                                        style="background-image: url({{ $user?->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) ?? asset('images/static/placeholders/user_profile.webp') }});"
                                        alt="{{ $user?->username ?? __('Guest') }} {{ __('Profile') }}"
                                        title="{{ $user?->username ?? __('Guest') }}"
                                        role="img"
                                    ></div>
                                </button>
                            </x-slot:trigger>

                            <x-slot:content>
                                <x-dropdown-link href="{{ route('me') }}" wire:navigate>
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                @auth
                                    <div class="border-t border-primary"></div>

                                    {{-- Library --}}
                                    <div class="block pl-4 pr-4 pt-2 pb-2 text-xs text-secondary">
                                        {{ __('Library') }}
                                    </div>

                                    <x-dropdown-link href="{{ route('profile.anime.library', $user) }}" wire:navigate>
                                        {{ __('Anime Library') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link href="{{ route('profile.manga.library', $user) }}" wire:navigate>
                                        {{ __('Manga Library') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link href="{{ route('profile.games.library', $user) }}" wire:navigate>
                                        {{ __('Games Library') }}
                                    </x-dropdown-link>
                                @else
                                    {{-- Library --}}
                                    <x-dropdown-link href="{{ route('library.index') }}" wire:navigate>
                                        {{ __('Library') }}
                                    </x-dropdown-link>
                                @endauth

                                <div class="border-t border-primary"></div>

                                @auth
                                    {{-- Favorite Pages --}}
                                    <div class="block pl-4 pr-4 pt-2 pb-2 text-xs text-secondary">
                                        {{ __('Favorite') }}
                                    </div>

                                    <x-dropdown-link href="{{ route('profile.anime.favorites', $user) }}" wire:navigate>
                                        {{ __('Favorite Anime') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link href="{{ route('profile.manga.favorites', $user) }}" wire:navigate>
                                        {{ __('Favorite Manga') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link href="{{ route('profile.games.favorites', $user) }}" wire:navigate>
                                        {{ __('Favorite Game') }}
                                    </x-dropdown-link>

                                    <div class="border-t border-primary"></div>
                                @endauth

                                @auth
                                    {{-- Reminder Pages --}}
                                    <div class="block pl-4 pr-4 pt-2 pb-2 text-xs text-secondary">
                                        {{ __('Reminder') }}
                                    </div>

                                    <x-dropdown-link href="{{ route('profile.anime.reminders', $user) }}" wire:navigate>
                                        {{ __('Anime Reminders') }}
                                    </x-dropdown-link>

{{--                                    <x-dropdown-link href="{{ route('profile.manga.reminders', $user) }}">--}}
{{--                                        {{ __('Reminder Manga') }}--}}
{{--                                    </x-dropdown-link>--}}

{{--                                    <x-dropdown-link href="{{ route('profile.games.reminders', $user) }}">--}}
{{--                                        {{ __('Reminder Game') }}--}}
{{--                                    </x-dropdown-link>--}}

                                    <div class="border-t border-primary"></div>
                                @endauth

                                {{-- More Pages --}}
                                <div class="block pl-4 pr-4 pt-2 pb-2 text-xs text-secondary">
                                    {{ __('More') }}
                                </div>

                                <x-dropdown-link href="{{ route('app-icons.index') }}" wire:navigate>
                                    {{ __('App Icons') }}
                                </x-dropdown-link>

                                <x-dropdown-link href="{{ route('theme-store.index') }}" wire:navigate>
                                    {{ __('Theme Store') }}
                                </x-dropdown-link>

                                <div class="border-t border-primary"></div>

                                @auth
                                    {{-- Account Management --}}
                                    <div class="block pl-4 pr-4 pt-2 pb-2 text-xs text-secondary">
                                        {{ __('Manage Account') }}
                                    </div>

                                    <x-dropdown-link href="{{ route('profile.settings') }}" wire:navigate>
                                        {{ __('Settings') }}
                                    </x-dropdown-link>

                                    <div class="border-t border-primary"></div>
                                @endauth

                                {{-- Authentication --}}
                                @auth
                                    @if (session()->has('nova_impersonated_by'))
                                        <form method="POST" action="{{ route('impersonation.stop') }}">
                                            @method('DELETE')
                                            @csrf

                                            <x-dropdown-link href="{{ route('impersonation.stop') }}"
                                                             onclick="event.preventDefault(); this.closest('form').submit();">
                                                {{ __('Stop impersonation') }}
                                            </x-dropdown-link>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('sign-out') }}">
                                        @csrf

                                        <x-dropdown-link href="{{ route('sign-out') }}"
                                                         onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('Sign out') }}
                                        </x-dropdown-link>
                                    </form>
                                @else
                                    <x-dropdown-link href="{{ route('sign-in') }}" wire:navigate>
                                        {{ __('Sign in') }}
                                    </x-dropdown-link>

                                    @if (Route::has('sign-up'))
                                        <x-dropdown-link href="{{ route('sign-up') }}" wire:navigate>
                                            {{ __('Create Account') }}
                                        </x-dropdown-link>
                                    @endif
                                @endauth
                            </x-slot:content>
                        </x-dropdown>
                    </div>
                </div>
            </div>
        </div>

        {{-- Responsive Navigation Menu --}}
        <div
            class="block absolute pl-4 pr-4 w-full bg-primary rounded-b-2xl z-[300] md:hidden"
            x-cloak
            x-show="isNavOpen"
            x-collapse.duration.400ms=""
        >
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link href="{{ route('home') }}" wire:navigate :active="request()->routeIs('home')">
                    {{ __('Explore') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('anime.index') }}" wire:navigate :active="request()->routeIs('anime.index')">
                    {{ __('Anime') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('manga.index') }}" wire:navigate :active="request()->routeIs('manga.index')">
                    {{ __('Manga') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('games.index') }}" wire:navigate :active="request()->routeIs('games.index')">
                    {{ __('Game') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('songs.index') }}" wire:navigate :active="request()->routeIs('songs.index')">
                    {{ __('Songs') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('schedule') }}" wire:navigate :active="request()->routeIs('schedule')">
                    {{ __('Schedule') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('charts.index') }}" wire:navigate :active="request()->routeIs('charts.index')">
                    {{ __('Charts') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('characters.index') }}" wire:navigate :active="request()->routeIs('characters.index')">
                    {{ __('Characters') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('people.index') }}" wire:navigate :active="request()->routeIs('people.index')">
                    {{ __('People') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('studios.index') }}" wire:navigate :active="request()->routeIs('studios.index')">
                    {{ __('Studios') }}
                </x-responsive-nav-link>

{{--                <x-responsive-nav-link href="{{ route('platforms.index') }}" wire:navigate :active="request()->routeIs('platforms.index')">--}}
{{--                    {{ __('Platforms') }}--}}
{{--                </x-responsive-nav-link>--}}
            </div>

            {{-- Responsive Settings Options --}}
            <div class="pt-4 pb-1 border-t border-primary">
                @auth
                    <div class="flex items-center pl-4 pr-4 pb-4">
                        <div class="shrink-0">
                            <div class="h-10 w-10 bg-cover rounded-full" style="background-image: url({{ $user->getFirstMediaFullUrl(\App\Enums\MediaCollection::Profile()) }});" alt="{{ $user->username }}" role="img"></div>
                        </div>

                        <div class="ml-3">
                            <div class="font-medium text-base text-primary">{{ $user->username }}</div>
                            <div class="font-medium text-sm text-secondary">{{ $user->email }}</div>
                        </div>
                    </div>

                    {{-- Profile --}}
                    <div class="space-y-1">
                        <x-responsive-nav-link href="{{ route('me') }}" wire:navigate>
                            {{ __('Profile') }}
                        </x-responsive-nav-link>

                        <div class="border-t border-primary"></div>
                    </div>

                    {{-- Library --}}
                    <div class="space-y-1 pt-1">
                        <x-responsive-nav-link href="{{ route('profile.anime.library', $user) }}"
                                               wire:navigate
                                               :active="request()->routeIs('profile.anime.library')">
                            {{ __('Anime Library') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link href="{{ route('profile.manga.library', $user) }}"
                                               wire:navigate
                                               :active="request()->routeIs('profile.manga.library')">
                            {{ __('Manga Library') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link href="{{ route('profile.games.library', $user) }}"
                                               wire:navigate
                                               :active="request()->routeIs('profile.games.library')">
                            {{ __('Games Library') }}
                        </x-responsive-nav-link>

                        <div class="border-t border-primary"></div>
                    </div>
                @else
                    <div class="space-y-1 pt-1">
                        <x-responsive-nav-link href="{{ route('me') }}" wire:navigate>
                            {{ __('Profile') }}
                        </x-responsive-nav-link>

                        {{-- Library --}}
                        <x-responsive-nav-link href="{{ route('library.index') }}"
                                               wire:navigate
                                               :active="request()->routeIs('library.index')">
                            {{ __('Library') }}
                        </x-responsive-nav-link>

                        <div class="border-t border-primary"></div>
                    </div>
                @endauth

                @auth
                    {{-- Favorite Pages --}}
                    <div class="space-y-1 pt-1">
                        <x-responsive-nav-link href="{{ route('profile.anime.favorites', $user) }}"
                                               wire:navigate
                                               :active="request()->routeIs('profile.anime.favorites', $user)">
                            {{ __('Favorite Anime') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link href="{{ route('profile.manga.favorites', $user) }}"
                                               wire:navigate
                                               :active="request()->routeIs('profile.manga.favorites', $user)">
                            {{ __('Favorite Manga') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link href="{{ route('profile.games.favorites', $user) }}"
                                               wire:navigate
                                               :active="request()->routeIs('profile.games.favorites', $user)">
                            {{ __('Favorite Game') }}
                        </x-responsive-nav-link>

                        <div class="border-t border-primary"></div>
                    </div>
                @endauth

                @auth
                    {{-- Reminder Pages --}}
                    <div class="space-y-1 pt-1">
                        <x-responsive-nav-link href="{{ route('profile.anime.reminders', $user) }}"
                                               wire:navigate
                                               :active="request()->routeIs('profile.anime.reminders', $user)">
                            {{ __('Anime Reminders') }}
                        </x-responsive-nav-link>

{{--                        <x-responsive-nav-link href="{{ route('profile.manga.reminders', $user) }}"--}}
{{--                                               wire:navigate--}}
{{--                                               :active="request()->routeIs('profile.manga.reminders', $user)">--}}
{{--                            {{ __('Manga Reminders') }}--}}
{{--                        </x-responsive-nav-link>--}}

{{--                        <x-responsive-nav-link href="{{ route('profile.games.reminders', $user) }}"--}}
{{--                                               wire:navigate--}}
{{--                                               :active="request()->routeIs('profile.games.reminders', $user)">--}}
{{--                            {{ __('Game Reminders') }}--}}
{{--                        </x-responsive-nav-link>--}}

                        <div class="border-t border-primary"></div>
                    </div>
                @endauth

                {{-- More Pages --}}
                <div class="space-y-1 pt-1">
                    <x-responsive-nav-link href="{{ route('app-icons.index') }}"
                                           wire:navigate
                                           :active="request()->routeIs('app-icons.index')">
                        {{ __('App Icons') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link href="{{ route('theme-store.index') }}"
                                           wire:navigate
                                           :active="request()->routeIs('theme-store.index')">
                        {{ __('Theme Store') }}
                    </x-responsive-nav-link>

                    <div class="border-t border-primary"></div>
                </div>

                {{-- Account Management --}}
                @auth
                    <div class="space-y-1 pt-1">
                        <x-responsive-nav-link href="{{ route('profile.settings') }}"
                                               wire:navigate
                                               :active="request()->routeIs('profile.settings')">
                            {{ __('Settings') }}
                        </x-responsive-nav-link>

                        <div class="border-t border-primary"></div>
                    </div>
                @endauth

                {{-- Authentication --}}
                <div class="space-y-1 pt-1">
                    @auth
                        @if (session()->has('nova_impersonated_by'))
                            <form method="POST" action="{{ route('impersonation.stop') }}">
                                @method('DELETE')
                                @csrf

                                <x-responsive-nav-link href="{{ route('impersonation.stop') }}"
                                                       onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Stop impersonation') }}
                                </x-responsive-nav-link>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('sign-out') }}">
                            @csrf

                            <x-responsive-nav-link href="{{ route('sign-out') }}"
                                                   onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Sign out') }}
                            </x-responsive-nav-link>
                        </form>
                    @else
                        <x-responsive-nav-link href="{{ route('sign-in') }}" wire:navigate>
                            {{ __('Sign in') }}
                        </x-responsive-nav-link>

                        @if (Route::has('sign-up'))
                            <x-responsive-nav-link href="{{ route('sign-up') }}" wire:navigate>
                                {{ __('Create Account') }}
                            </x-responsive-nav-link>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Responsive Overlay --}}
    <div
        class="fixed inset-0 transform transition-all z-[299] md:hidden"
        x-cloak
        x-show="isNavOpen"
        x-on:click="isNavOpen = false;"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-black opacity-75"></div>
    </div>

    {{-- Nav Search --}}
    <livewire:nav-search />
</div>
