<footer class="bg-gray-100">
    <div class="max-w-7xl mx-auto pl-4 pr-4 pt-10 pb-6 sm:px-6">
        <div class="flex flex-wrap">
            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Anime') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('anime.index') }}">{{ __('Anime') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('anime.seasons.index') }}">{{ __('Anime Seasons') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('anime.seasons.archive') }}">{{ __('Anime Archive') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Manga') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('manga.index') }}">{{ __('Manga') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('manga.seasons.index') }}">{{ __('Manga Seasons') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('manga.seasons.archive') }}">{{ __('Manga Archive') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Discover') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('genres.index') }}">{{ __('Genres') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('themes.index') }}">{{ __('Themes') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('characters.index') }}">{{ __('Characters') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('people.index') }}">{{ __('People') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('studios.index') }}">{{ __('Studios') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Account') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('me') }}">{{ __('Kurozora Profile') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('profile.settings') }}">{{ __('Manage Your Settings') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('animelist') }}">{{ __('Anime Library') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('mangalist') }}">{{ __('Manga Library') }}</x-footer-link>
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
                        <x-footer-link href="{{ route('misc.team') }}">{{ __('Kurozora Team') }}</x-footer-link>
                    </li>

{{--                    <li class="mt-2">--}}
{{--                        <x-footer-link href="{{ route('misc.jobs') }}">{{ __('Job Opportunities') }}</x-footer-link>--}}
{{--                    </li>--}}

{{--                    <li class="mt-2">--}}
{{--                        <x-footer-link href="{{ route('misc.investors') }}">{{ __('Investors') }}</x-footer-link>--}}
{{--                    </li>--}}

                    <li class="mt-2">
                        <x-footer-link href="{{ route('misc.contact') }}">{{ __('Contact Kurozora') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('misc.press-kit') }}">{{ __('Press-Kit') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Legal') }}</h5>
                <ul class="m-0 mb-4 list-none">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('legal.privacy-policy') }}">{{ __('Privacy Policy') }}</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link href="{{ route('legal.terms-of-use') }}">{{ __('Terms of Use') }}</x-footer-link>
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
                        <x-footer-link target="_blank" href="{{ config('social.instagram.url') }}">Instagram</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('social.reddit.url') }}">Reddit</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('social.twitter.url') }}">Twitter</x-footer-link>
                    </li>

                    <li class="mt-2">
                        <x-footer-link target="_blank" href="{{ config('social.twitter.url') }}">YouTube</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-1/2 sm:w-1/3 text-center md:w-1/4 md:text-left lg:w-1/6">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Support Us') }}</h5>
                <ul class="m-0 mb-4 list-none">
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
                <p class="text-sm text-gray-500">Copyright © 2018-{{ now()->year }} Redark. {{ __('All rights reserved') }}</p>
            </div>
        </div>
    </div>
</footer>
