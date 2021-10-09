<x-app-layout>
    <x-slot name="title">
        {{ __('Forgot Password') }}
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora ID') }}
        </h2>
    </x-slot>

    <div class="flex flex-col justify-center w-screen h-full max-w-prose mx-auto px-4 py-6 sm:px-6">
        <div class="text-center mb-5">
            <p class="text-2xl font-bold">{{ __('Forgot your password?') }}</p>
            <p>{{ __('Enter your Kurozora ID to continue.') }}</p>
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="block">
                <x-label for="email" value="{{ __('Email Address') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="flex flex-col items-center justify-end mt-4">
                <x-button>
                    {{ __('Email Password Reset Link') }}
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
