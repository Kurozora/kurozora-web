<x-error-layout>
    <x-slot:title>
        {{ __('Service Unavailable') }}
    </x-slot:title>

    <div class="md:flex min-h-screen">
        <div class="w-full md:w-1/2 flex items-center justify-center">
            <div class="max-w-md m-8">
                <div class="text-5xl md:text-9xl font-black">503</div>

                <div class="w-16 h-1 bg-tint my-3 md:my-6"></div>

                @if (app()->isDownForMaintenance())
                    <p class="text-secondary text-2xl md:text-3xl font-light mb-8 leading-normal max-w-prose">{{ __(':x is currently under maintenance. All services will be available shortly. If this continues for more than an hour, you can follow the status on Twitter.', ['x' => config('app.name')]) }}</p>

                    <x-link-button href="{{ config('social.twitter.url') }}">
                        {{ __('Go to Twitter') }}
                    </x-link-button>
                @else
                    @if (isset($exception) && $exception instanceof \Illuminate\Database\QueryException)
                        <p class="text-secondary text-2xl md:text-3xl font-light mb-8 leading-normal max-w-prose">{{ implode(' ', $exception->errorInfo ?? []) }}</p>
                    @endif

                    <p class="text-secondary text-2xl md:text-3xl font-light mb-8 leading-normal max-w-prose">{{ __('Our crack team is on the ca… couch. O-oh dear…') }}</p>

                    <x-link-button href="{{ url('/') }}">
                        {{ __('Go Home?') }}
                    </x-link-button>
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
