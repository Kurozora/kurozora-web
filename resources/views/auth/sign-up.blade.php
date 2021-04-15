<x-app-layout>
    <x-slot name="title">
        {{ __('Sign Up') }}
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora ID') }}
        </h2>
    </x-slot>

    <div class="mb-5 text-center">
        <p class="text-2xl font-bold">{{ __('New to Kurozora?') }}</p>
        <p>{{ __('Create an account and join the community.') }}</p>
    </div>

    <x-validation-errors class="mb-4" />

    <form method="POST" action="{{ route('sign-up') }}">
        @csrf

        <div>
            <x-label for="username" value="{{ __('Username') }}" />
            <x-input id="username" class="mt-1 block w-full" type="text" name="username" :value="old('username')" placeholder="{{ __('Pick a cool one') }} 🙉" required autofocus autocomplete="username" />
        </div>

        <div class="mt-4">
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" placeholder="{{ __('We all forget our passwords') }} 🙈" required />
        </div>

        <div class="mt-4">
            <x-label for="password" value="{{ __('Password') }}" />
            <x-input id="password" class="mt-1 block w-full" type="password" name="password" placeholder="{{ __('Make it super secret') }} 🙊" required autocomplete="new-password" />
        </div>

        <div class="mt-4">
            <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
            <x-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" placeholder="{{ __('But keep it memorable') }} 🐵" required autocomplete="new-password" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('sign-in') }}">
                {{ __('Already have an account? Let’s sign in') }} 🔥
            </a>

            <x-button class="ml-4">
                {{ __('Join') }} 🤗
            </x-button>
        </div>
    </form>
</x-app-layout>
