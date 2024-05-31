<div
    class="hidden md:flex bg-white border-e border-black"
    style="min-width: 18rem;"
>
    <nav class="flex flex-col ml-4 mr-4 w-full">
        <x-sidebar-nav-link href="{{ route('home') }}" wire:navigate :active="request()->routeIs('home')">
            @svg('bell_fill', 'fill-current', ['width' => '18']) {{ __('Explore') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link href="{{ route('anime.index') }}"  wire:navigate :active="request()->routeIs('anime.index')">
            @svg('bell_fill', 'fill-current', ['width' => '18']) {{ __('Anime') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link href="{{ route('manga.index') }}"  wire:navigate :active="request()->routeIs('manga.index')">
            @svg('bell_fill', 'fill-current', ['width' => '18']) {{ __('Manga') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link href="{{ route('games.index') }}"  wire:navigate :active="request()->routeIs('games.index')">
           @svg('bell_fill', 'fill-current', ['width' => '18']) {{ __('Game') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link href="{{ route('genres.index') }}"  wire:navigate :active="request()->routeIs('genres.index')">
           @svg('bell_fill', 'fill-current', ['width' => '18']) {{ __('Genres') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link href="{{ route('characters.index') }}"  wire:navigate :active="request()->routeIs('characters.index')">
           @svg('bell_fill', 'fill-current', ['width' => '18']) {{ __('Characters') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link href="{{ route('people.index') }}"  wire:navigate :active="request()->routeIs('people.index')">
           @svg('bell_fill', 'fill-current', ['width' => '18']) {{ __('People') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link href="{{ route('studios.index') }}"  wire:navigate :active="request()->routeIs('studios.index')">
           @svg('bell_fill', 'fill-current', ['width' => '18']) {{ __('Studios') }}
        </x-sidebar-nav-link>

        <x-sidebar-nav-link href="{{ config('app.ios.store_url') }}" :external="true">
            @svg('bell_fill', 'fill-current', ['width' => '18']) App
        </x-sidebar-nav-link>
    </nav>
</div>
