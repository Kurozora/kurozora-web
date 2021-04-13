<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="/">
                        <x-logo class="block h-10 w-auto"/>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link href="{{ route('themes') }}" :active="request()->routeIs('themes')">
                        {{ __('Themes') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @auth
                            <button
                                class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out">
                                <img class="h-8 w-8 rounded-full object-cover"
                                     src="{{ Auth::user()->profile_image }}" alt="{{ Auth::user()->username }}"/>
                            </button>
                        @else
                            <button
                                class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>
                        @endauth
                    </x-slot>

                    <x-slot name="content">
                        <!-- Authentication -->
                        <div class="border-t border-gray-100"></div>
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
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('themes') }}" :active="request()->routeIs('themes')">
                {{ __('Themes') }}
            </x-responsive-nav-link>
        </div>

        <!-- Authentication -->
        <div class="pt-4 pb-1 border-t border-gray-200">
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
