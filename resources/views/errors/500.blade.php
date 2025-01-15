<x-error-layout>
    <x-slot:title>
        {{ __('Internal Server Error') }}
    </x-slot:title>

    <div class="md:flex min-h-screen">
        <div class="w-full md:w-1/2 flex items-center justify-center">
            <div class="max-w-md m-8">
                <div class="text-5xl md:text-9xl font-black">500</div>

                <div class="w-16 h-1 bg-green-300 my-3 md:my-6"></div>

                <p class="text-secondary text-2xl md:text-3xl font-light mb-8 leading-normal max-w-prose">{{ __('No worries, it’s just the server dying. The crack team has been dispatched to bring it back to health.') }}</p>

                <x-outlined-link-button href="{{ url('/') }}" class="text-base font-bold py-3 px-6 border-2" color="green">
                    {{ __('Go Home') }}
                </x-outlined-link-button>
            </div>
        </div>

        <div class="relative pb-[100%] md:flex md:pb-0 md:min-h-screen w-full md:w-1/2">
            <div style="background-image: url({{ asset('images/illustrations/500.svg') }});"
                 class="absolute inset-0 bg-cover bg-no-repeat md:bg-left lg:bg-center">
            </div>
        </div>
    </div>
</x-error-layout>
