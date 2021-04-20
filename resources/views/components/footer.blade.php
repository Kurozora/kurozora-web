<footer class="bg-gray-100 mt-4">
    <div class="max-w-7xl mx-auto px-6 pt-10 pb-6">
        <div class="flex flex-wrap">
            <div class="w-full md:w-1/5 text-center md:text-left">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Account') }}</h5>
                <ul class="m-0 mb-4">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('profile.settings') }}">{{ __('Hexarian Account') }}</x-footer-link>
                    </li>
                    <li class="mt-2">
                        <x-footer-link href="{{ route('profile.settings') }}">{{ __('Manage Your Settings') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-full md:w-1/5 text-center md:text-left">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('About Kurozora') }}</h5>
                <ul class="m-0 mb-4">
                    <li class="mt-2">
{{--                        <x-footer-link href="{{ route('misc.leadership') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Kurozora Leadership') }}</x-footer-link>--}}
                        <x-footer-link href="#" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Kurozora Leadership') }}</x-footer-link>
                    </li>
                    <li class="mt-2">
{{--                        <x-footer-link href="{{ route('misc.jobs') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Job Opportunities') }}</x-footer-link>--}}
                        <x-footer-link href="#" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Job Opportunities') }}</x-footer-link>
                    </li>
                    <li class="mt-2">
{{--                        <x-footer-link href="{{ route('misc.investors') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Investors') }}</x-footer-link>--}}
                        <x-footer-link href="#" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Investors') }}</x-footer-link>
                    </li>
                    <li class="mt-2">
{{--                        <x-footer-link href="{{ route('misc.contact') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Contact Kurozora') }}</x-footer-link>--}}
                        <x-footer-link href="#" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Contact Kurozora') }}</x-footer-link>
                    </li>
                </ul>
            </div>

            <div class="w-full md:w-1/5 text-center md:text-left">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Legal') }}</h5>
                <ul class="m-0 mb-4">
                    <li class="mt-2">
                        <x-footer-link href="{{ route('legal.privacy-policy') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Privacy Policy') }}</x-footer-link>
                    </li>
                    <li class="mt-2">
                        <x-footer-link href="{{ route('legal.terms-of-use') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Terms of Use') }}</x-footer-link>
                    </li>
                </ul>
            </div>
            <hr class="w-full mb-2" />
            <div class="w-full text-center md:text-left">
                <p class="text-sm text-gray-500">Copyright ©{{ now()->year }} Kurozora B.V. {{ __('All rights reserved') }}</p>
            </div>
        </div>
    </div>
</footer>
