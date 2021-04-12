<footer class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-6 pt-10 pb-6">
        <div class="flex flex-wrap">
            <div class="w-full md:w-1/5 text-center md:text-left">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('About Kurozora') }}</h5>
                <ul class="mb-4">
{{--                    <li class="mt-2">--}}
{{--                        <a href="{{ route('misc.leadership') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Kurozora Leadership') }}</a>--}}
{{--                    </li>--}}
{{--                    <li class="mt-2">--}}
{{--                        <a href="{{ route('misc.jobs') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Job Opportunities') }}</a>--}}
{{--                    </li>--}}
{{--                    <li class="mt-2">--}}
{{--                        <a href="{{ route('misc.investors') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Investors') }}</a>--}}
{{--                    </li>--}}
{{--                    <li class="mt-2">--}}
{{--                        <a href="{{ route('misc.contact') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Contact Kurozora') }}</a>--}}
{{--                    </li>--}}
                </ul>
            </div>
            <div class="w-full md:w-1/5 text-center md:text-left">
                <h5 class="uppercase text-sm mb-2 font-semibold">{{ __('Legal') }}</h5>
                <ul class="mb-4">
                    <li class="mt-2">
                        <a href="{{ route('legal.privacy-policy') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Privacy Policy') }}</a>
                    </li>
                    <li class="mt-2">
                        <a href="{{ route('legal.terms-of-services') }}" class="hover:underline text-sm text-gray-400 hover:text-blue-500">{{ __('Terms of Services') }}</a>
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
