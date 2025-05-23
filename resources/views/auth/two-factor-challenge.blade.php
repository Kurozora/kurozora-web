<x-app-layout>
    <x-slot:title>
        {{ __('Two-Factor Authentication') }}
    </x-slot:title>

    <x-slot:header>
        <h2 class="text-2xl font-bold">
            {{ __(':x Account', ['x' => config('app.name')]) }}
        </h2>
    </x-slot:header>

    <div x-data="{ recovery: false }"
         class="flex flex-col justify-center max-w-prose w-screen h-full mx-auto pt-4 pb-6 pl-4 pr-4"
    >
        <div class="mb-5 text-center text-secondary" x-show="!recovery" x-cloak>
            <h1 class="text-2xl font-bold">{{ __('Enter Authorization Code') }}</h1>
            <p>{{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}</p>
        </div>

        <div class="mb-5 text-center text-secondary" x-show="recovery" x-cloak>
            <h1 class="text-2xl font-bold">{{ __('Enter Recovery Code') }}</h1>
            <p>{{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}</p>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('two-factor.update') }}">
            @csrf

            <div class="mt-4" x-show="!recovery" x-cloak>
                <x-label for="code" value="{{ __('Code') }}" />
                <x-input id="code" class="block mt-1 w-full" type="text" name="code" autofocus x-ref="code" autocomplete="one-time-code" />
            </div>

            <div class="mt-4" x-show="recovery" x-cloak>
                <x-label for="recovery_code" value="{{ __('Recovery Code') }}" />
                <x-input id="recovery_code" class="block mt-1 w-full" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" />
            </div>

            <div class="flex flex-col items-center justify-end mt-4">
                <x-simple-button
                    type="button"
                    x-show="! recovery"
                    x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() });"
                >
                    {{ __('Use a recovery code') }}
                </x-simple-button>

                <x-simple-button
                    type="button"
                    x-show="recovery"
                    x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() });"
                >
                    {{ __('Use an authentication code') }}
                </x-simple-button>

                <x-button class="mt-4">
                    {{ __('Sign in') }}
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
