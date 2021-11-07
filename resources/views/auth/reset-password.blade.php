<x-app-layout>
    <x-slot name="title">
        {{ __('Reset Password') }}
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora ID') }}
        </h2>
    </x-slot>

    <div class="flex flex-col justify-center h-full max-w-prose mx-auto px-4 py-6 sm:px-6">
        <div class="text-center mb-5">
            <p class="text-2xl font-bold">{{ __('New Kurozora ID Password') }}</p>
            <p>
                {{ __('Enter a new password for') }} <span class="font-bold">{{ $email }}</span>
            </p>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <x-honey recaptcha="reset_password" />

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <input id="email" class="block mt-1 w-full" type="hidden" name="email" value="{{ $request->email }}" />

            <div class="block">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="flex flex-col items-center justify-end mt-4">
                <x-button>
                    {{ __('Reset Password') }}
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
