<x-error-layout>
    <x-slot name="title">
        {{ __('Forbidden') }}
    </x-slot>

    <div class="md:flex min-h-screen">
        <div class="w-full md:w-1/2 bg-white flex items-center justify-center">
            <div class="max-w-md m-8">
                <div class="text-black text-5xl md:text-9xl font-black">403</div>

                <div class="w-16 h-1 bg-blue-300 my-3 md:my-6"></div>

                <p class="text-grey-500 text-2xl md:text-3xl font-light mb-8 leading-normal">{{ __('You’re not authorized to view this page.') }}</p>

                <x-outlined-button href="{{ url('/') }}" class="text-blue-500 text-base font-bold py-3 px-6 border-2 border-blue-500 hover:bg-blue-400 hover:border-blue-400 focus:border-blue-600 focus:ring-blue active:bg-blue-600">
                    {{ __('Go Home?') }}
                </x-outlined-button>
            </div>
        </div>

        <div class="relative pb-[100%] md:flex md:pb-0 md:min-h-screen w-full md:w-1/2">
            <div style="background-image: url({{ asset('images/illustrations/403.svg') }});"
                 class="absolute inset-0 bg-cover bg-no-repeat md:bg-left lg:bg-center">
            </div>
        </div>
    </div>
</x-error-layout>
