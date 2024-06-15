<x-app-layout>
    <x-slot:title>
        {{ __('Reset Password') }}
    </x-slot:title>

    <x-slot:header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora Account') }}
        </h2>
    </x-slot:header>

    <div class="flex flex-col justify-center h-full max-w-prose mx-auto pl-4 pr-4 py-6 sm:px-6">
        <div class="text-center mb-5">
            <h1 class="text-2xl font-bold">{{ __('New Kurozora Account Password') }}</h1>
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
