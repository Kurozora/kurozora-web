<div
    x-data="{
        isSearchEnabled: @entangle('isSearchEnabled'),
        isNavOpen: false
    }"
>
    <nav class="relative bg-white border-b border-gray-100 z-[300]">
        {{-- Primary Navigation Menu --}}
        <div class="max-w-7xl mx-auto pl-4 pr-4 sm:px-6">
            <div class="flex justify-between h-16">
                {{-- Hamburger --}}
                <div
                    class="-mr-2 flex items-center sm:hidden"
                    x-show="! isSearchEnabled"
                    x-transition:enter="ease-out duration-150 delay-[50ms] transform sm:delay-300"
                    x-transition:enter-start="opacity-0 scale-75"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200 delay-100 transform sm:delay-[50ms]"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-75"
                >
                    <button
                        class="inline-flex items-center justify-center pt-2 pr-2 pb-2 pl-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
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

                <div class="flex w-full justify-between">
                    {{-- Left Side --}}
                    <span class="flex w-full">
                        {{-- Logo --}}
                        <a class="inline-flex items-center m-auto text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none focus:text-gray-700 sm:my-0 sm:ml-0 sm:mr-8 sm:pt-1"
                           href="/"
                           x-show="! isSearchEnabled"
                           x-transition:enter="ease-out duration-150 delay-100 transform sm:delay-[0ms]"
                           x-transition:enter-start="opacity-0 scale-75"
                           x-transition:enter-end="opacity-100 scale-100"
                           x-transition:leave="ease-in duration-200 delay-[50ms] transform sm:delay-[400ms]"
                           x-transition:leave-start="opacity-100 scale-100"
                           x-transition:leave-end="opacity-0 scale-75"
                        >
                            <x-logo class="block h-9 w-auto" />
                        </a>

                        {{-- Navigation Links --}}
                        <div class="hidden sm:flex sm:justify-between sm:-my-px sm:w-full md:w-auto md:space-x-8">
                            <x-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')"
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

                            <x-nav-link href="{{ route('anime.index') }}" :active="request()->routeIs('anime.index')"
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

                            <x-nav-link href="{{ route('genres.index') }}" :active="request()->routeIs('genres.index')"
                                        x-show="! isSearchEnabled"
                                        x-transition:enter="ease-out duration-150 delay-150 transform"
                                        x-transition:enter-start="opacity-0 scale-75"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="ease-in duration-200 delay-[250ms] transform"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-75"
                            >
                                {{ __('Genres') }}
                            </x-nav-link>

                            <x-nav-link href="{{ route('characters.index') }}" :active="request()->routeIs('characters.index')"
                                        x-show="! isSearchEnabled"
                                        x-transition:enter="ease-out duration-150 delay-200 transform"
                                        x-transition:enter-start="opacity-0 scale-75"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="ease-in duration-200 delay-200 transform"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-75"
                            >
                                {{ __('Characters') }}
                            </x-nav-link>

                            <x-nav-link href="{{ route('people.index') }}" :active="request()->routeIs('people.index')"
                                        x-show="! isSearchEnabled"
                                        x-transition:enter="ease-out duration-150 delay-[250ms] transform"
                                        x-transition:enter-start="opacity-0 scale-75"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="ease-in duration-200 delay-150 transform"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-75"
                            >
                                {{ __('People') }}
                            </x-nav-link>

                            <x-nav-link href="{{ route('studios.index') }}" :active="request()->routeIs('studios.index')"
                                        x-show="! isSearchEnabled"
                                        x-transition:enter="ease-out duration-150 delay-300 transform"
                                        x-transition:enter-start="opacity-0 scale-75"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="ease-in duration-200 delay-100 transform"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-75"
                            >
                                {{ __('Studios') }}
                            </x-nav-link>

                            <x-nav-link href="{{ config('app.ios.store_url') }}"
                                        target="_blank"
                                        x-show="! isSearchEnabled"
                                        x-transition:enter="ease-out duration-150 delay-[350ms] transform"
                                        x-transition:enter-start="opacity-0 scale-75"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="ease-in duration-200 delay-75 transform"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-75"
                            >
                                App
                            </x-nav-link>
                        </div>
                    </span>

                    {{-- Right Side --}}
                    <div class="flex items-center sm:justify-between sm:ml-8 sm:-my-px sm:space-x-8">
                        {{-- Search --}}
                        <a class="inline-flex items-center text-gray-500 cursor-pointer transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none focus:text-gray-700"
                           x-show="! isSearchEnabled"
                           x-on:click="isNavOpen = false; isSearchEnabled = ! isSearchEnabled;"
                           x-transition:enter="ease-out duration-150 delay-150 transform dm:delay-300"
                           x-transition:enter-start="opacity-0 scale-75"
                           x-transition:enter-end="opacity-100 scale-100"
                           x-transition:leave="ease-in duration-200 transform sm:delay-[50ms]"
                           x-transition:leave-start="opacity-100 scale-100"
                           x-transition:leave-end="opacity-0 scale-75"
                        >
                            @svg('magnifyingglass', 'fill-current', ['width' => '18'])
                        </a>

                        {{-- Settings Dropdown --}}
                        <x-dropdown align="right" width="48">
                            <x-slot:trigger>
                                <button
                                    class="hidden sm:flex text-sm border-2 border-transparent rounded-full transition duration-150 ease-in-out focus:outline-none focus:border-gray-300"
                                    x-show="! isSearchEnabled"
                                    x-transition:enter="ease-out duration-150 delay-[350ms] transform"
                                    x-transition:enter-start="opacity-0 scale-75"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200 transform"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-75"
                                >
                                    <div
                                        class="h-8 w-8 bg-cover rounded-full"
                                        style="background-image: url({{ auth()->user()?->profile_image_url ?? asset('images/static/placeholders/user_profile.webp') }});"
                                        alt="{{ auth()->user()?->username ?? __('Guest') }} {{ __('Profile') }}"
                                        title="{{ auth()->user()?->username ?? __('Guest') }}"
                                        role="img"
                                    ></div>
                                </button>
                            </x-slot:trigger>

                            <x-slot:content>
                                <x-dropdown-link href="{{ route('me') }}">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                {{-- Library --}}
                                <x-dropdown-link href="{{ route('library.index') }}">
                                    {{ __('Library') }}
                                </x-dropdown-link>

                                <div class="border-t border-gray-100"></div>

                                {{-- More Pages --}}
                                <div class="block pl-4 pr-4 pt-2 pb-2 text-xs text-gray-400">
                                    {{ __('More') }}
                                </div>

                                <x-dropdown-link href="{{ route('theme-store.index') }}">
                                    {{ __('Theme Store') }}
                                </x-dropdown-link>

                                <div class="border-t border-gray-100"></div>

                                @auth
                                    {{-- Account Management --}}
                                    <div class="block pl-4 pr-4 pt-2 pb-2 text-xs text-gray-400">
                                        {{ __('Manage Account') }}
                                    </div>

                                    <x-dropdown-link href="{{ route('profile.settings') }}">
                                        {{ __('Settings') }}
                                    </x-dropdown-link>

                                    <div class="border-t border-gray-100"></div>
                                @endauth

                                {{-- Authentication --}}
                                @auth
                                    <form method="POST" action="{{ route('sign-out') }}">
                                        @csrf

                                        <x-dropdown-link href="{{ route('sign-out') }}"
                                                         onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('Sign out') }}
                                        </x-dropdown-link>
                                    </form>
                                @else
                                    <x-dropdown-link href="{{ route('sign-in') }}">
                                        {{ __('Sign in') }}
                                    </x-dropdown-link>

                                    @if (Route::has('sign-up'))
                                        <x-dropdown-link href="{{ route('sign-up') }}">
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
            class="block absolute pl-4 pr-4 w-full bg-white rounded-b-2xl z-[300] sm:hidden"
            x-show="isNavOpen"
            x-collapse.duration.400ms=""
        >
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
                    {{ __('Explore') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('anime.index') }}" :active="request()->routeIs('anime.index')">
                    {{ __('Anime') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('genres.index') }}" :active="request()->routeIs('genres.index')">
                    {{ __('Genres') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('characters.index') }}" :active="request()->routeIs('characters.index')">
                    {{ __('Characters') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('people.index') }}" :active="request()->routeIs('people.index')">
                    {{ __('People') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('studios.index') }}" :active="request()->routeIs('studios.index')">
                    {{ __('Studios') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ config('app.ios.store_url') }}" target="_blank">
                    App
                </x-responsive-nav-link>
            </div>

            {{-- Responsive Settings Options --}}
            <div class="pt-4 pb-1 border-t border-gray-200">
                @auth
                    <div class="flex items-center pl-4 pr-4 pb-4">
                        <div class="shrink-0">
                            <div class="h-10 w-10 bg-cover rounded-full" style="background-image: url({{ auth()->user()->profile_image_url }});" alt="{{ auth()->user()->username }}" role="img"></div>
                        </div>

                        <div class="ml-3">
                            <div class="font-medium text-base text-gray-800">{{ auth()->user()->username }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                @endauth

                <div class="space-y-1">
                    <x-responsive-nav-link href="{{ route('me') }}">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    {{-- Library --}}
                    <x-responsive-nav-link href="{{ route('library.index') }}">
                        {{ __('Library') }}
                    </x-responsive-nav-link>

                    <div class="border-t border-gray-100"></div>
                </div>

                {{-- Themes --}}
                <div class="space-y-1">
                    <x-responsive-nav-link href="{{ route('theme-store.index') }}"
                                           :active="request()->routeIs('theme-store.index')">
                        {{ __('Theme Store') }}
                    </x-responsive-nav-link>

                    <div class="border-t border-gray-100"></div>
                </div>

                {{-- Account Management --}}
                @auth
                    <div class="space-y-1">
                        <x-responsive-nav-link href="{{ route('profile.settings') }}"
                                               :active="request()->routeIs('profile.settings')">
                            {{ __('Settings') }}
                        </x-responsive-nav-link>

                        <div class="border-t border-gray-100"></div>
                    </div>
                @endauth

                {{-- Authentication --}}
                <div class="space-y-1">
                    @auth
                        <form method="POST" action="{{ route('sign-out') }}">
                            @csrf

                            <x-responsive-nav-link href="{{ route('sign-out') }}" onclick="event.preventDefault();
                                                                                this.closest('form').submit();">
                                {{ __('Sign out') }}
                            </x-responsive-nav-link>
                        </form>
                    @else
                        <x-responsive-nav-link href="{{ route('sign-in') }}">
                            {{ __('Sign in') }}
                        </x-responsive-nav-link>

                        @if (Route::has('sign-up'))
                            <x-responsive-nav-link href="{{ route('sign-up') }}">
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
        class="fixed inset-0 transform transition-all z-[299] sm:hidden"
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
