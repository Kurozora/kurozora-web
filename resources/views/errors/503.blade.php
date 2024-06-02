<x-error-layout>
    <x-slot:title>
        {{ __('Service Unavailable') }}
    </x-slot:title>

    <div class="md:flex min-h-screen">
        <div class="w-full md:w-1/2 bg-white flex items-center justify-center">
            <div class="max-w-md m-8">
                <div class="text-black text-5xl md:text-9xl font-black">503</div>

                <div class="w-16 h-1 bg-violet-300 my-3 md:my-6"></div>

                @if(app()->isDownForMaintenance())
                    <p class="text-gray-500 text-2xl md:text-3xl font-light mb-8 leading-normal max-w-prose">{{ __('Kurozora is currently under maintenance. All services will be available shortly. If this continues for more than an hour, you can follow the status on Twitter.') }}</p>

                    <x-outlined-link-button href="{{ config('social.twitter.url') }}" class="text-base font-bold py-3 px-6 border-2" color="violet">
                        {{ __('Go to Twitter') }}
                    </x-outlined-link-button>
                @else
                    <p class="text-gray-500 text-2xl md:text-3xl font-light mb-8 leading-normal max-w-prose">{{ __('Our crack team is on the ca... couch. O-oh dear...') }}</p>

                    <x-outlined-link-button href="{{ url('/') }}" class="text-base font-bold py-3 px-6 border-2" color="violet">
                        {{ __('Go Home') }}
                    </x-outlined-link-button>
                @endif
            </div>
        </div>

        <div class="relative pb-[100%] md:flex md:pb-0 md:min-h-screen w-full md:w-1/2">
            <div style="background-image: url({{ asset('images/illustrations/503.svg') }});"
                 class="absolute inset-0 bg-cover bg-no-repeat md:bg-left lg:bg-center">
            </div>
        </div>
    </div>
</x-error-layout>
