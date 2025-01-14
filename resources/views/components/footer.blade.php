<footer>
    <div class="max-w-7xl mx-auto pl-4 pr-4 pt-10 pb-6 sm:px-6">
        <div class="flex flex-wrap">
            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Anime') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('anime.index') }}" wire:navigate>{{ __('Anime') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('anime.seasons.index') }}" wire:navigate>{{ __('Anime Seasons') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('anime.upcoming.index') }}" wire:navigate>{{ __('Upcoming Anime') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('anime.continuing.index') }}" wire:navigate>{{ __('Continuing Anime') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('anime.seasons.archive') }}" wire:navigate>{{ __('Anime Archive') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Manga') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('manga.index') }}" wire:navigate>{{ __('Manga') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('manga.seasons.index') }}" wire:navigate>{{ __('Manga Seasons') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('manga.upcoming.index') }}" wire:navigate>{{ __('Upcoming Manga') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('manga.continuing.index') }}" wire:navigate>{{ __('Continuing Manga') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('manga.seasons.archive') }}" wire:navigate>{{ __('Manga Archive') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Games') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('games.index') }}" wire:navigate>{{ __('Games') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('games.seasons.index') }}" wire:navigate>{{ __('Game Seasons') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('games.upcoming.index') }}" wire:navigate>{{ __('Upcoming Games') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('games.seasons.archive') }}" wire:navigate>{{ __('Games Archive') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Discover') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('songs.index') }}" wire:navigate>{{ __('Songs') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('schedule') }}" wire:navigate>{{ __('Schedule') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('charts.index') }}" wire:navigate>{{ __('Charts') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('genres.index') }}" wire:navigate>{{ __('Genres') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('themes.index') }}" wire:navigate>{{ __('Themes') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('characters.index') }}" wire:navigate>{{ __('Characters') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('people.index') }}" wire:navigate>{{ __('People') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('studios.index') }}" wire:navigate>{{ __('Studios') }}</x-footer-link>
                    </li>

{{--                    <li class="mt-2">--}}
{{--                        <x-footer-link href="{{ route('platforms.index') }}" wire:navigate>{{ __('Platforms') }}</x-footer-link>--}}
{{--                    </li>--}}
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Account') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('me') }}" wire:navigate>{{ __('Kurozora Profile') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('profile.settings') }}" wire:navigate>{{ __('Manage Your Settings') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('animelist') }}" wire:navigate>{{ __('Anime Library') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('mangalist') }}" wire:navigate>{{ __('Manga Library') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('gamelist') }}" wire:navigate>{{ __('Games Library') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Services') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="#">{{ __('Kurozora+') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ config('app.ios.store_url') }}" target="_blank">{{ __('Kurozora App') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ config('app.developer_url') }}" target="_blank">{{ __('Kurozora Developer') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('api') }}" target="_blank">{{ __('Kurozora API') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ config('app.status_url') }}" target="_blank">{{ __('Kurozora Status') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('About Kurozora') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('misc.team') }}" wire:navigate>{{ __('Kurozora Team') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('misc.projects') }}" wire:navigate>{{ __('Open-Source Projects') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('misc.contact') }}" wire:navigate>{{ __('Contact Kurozora') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('misc.press-kit') }}" wire:navigate>{{ __('Press-Kit') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Legal') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('legal.privacy-policy') }}" wire:navigate>{{ __('Privacy Policy') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('legal.terms-of-use') }}" wire:navigate>{{ __('Terms of Use') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Socials') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('social.discord.url') }}">Discord</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" rel="me" href="{{ config('social.fosstodon.url') }}">Fosstodon</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('social.instagram.url') }}">Instagram</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" rel="me" href="{{ config('social.mastodon.url') }}">Mastodon</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('social.reddit.url') }}">Reddit</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('social.twitter.url') }}">Twitter</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('social.youtube.url') }}">YouTube</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Support Us') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('services.ko-fi.url') }}">Ko-Fi</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('services.open_collective.url') }}">Open Collective</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('services.patreon.url') }}">Patreon</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('services.paypal.url') }}">PayPal</x-footer-link>
                    </li>
                </ul>
            </div>

            <x-hr class="mb-2" />

            <div class="w-full text-center md:text-left">
                <p class="text-sm text-secondary">Copyright © 2018-{{ now()->year }} {{ config('app.name') }}. {{ __('All rights reserved') }}</p>
            </div>
        </div>
    </div>
</footer>
