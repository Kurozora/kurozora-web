<x-app-layout>
    <x-slot:title>
        {{ __('Forgot Password') }}
    </x-slot:title>

    <x-slot:header>
        <h2 class="text-2xl font-bold">
            {{ __(':x Account', ['x' => config('app.name')]) }}
        </h2>
    </x-slot:header>

    <div class="flex flex-col justify-center w-screen h-full max-w-prose mx-auto pt-4 pb-6 pl-4 pr-4">
        <div class="text-center mb-5">
            <h1 class="text-2xl font-bold">{{ __('Forgot your password?') }}</h1>
            <p>{{ __('Enter your :x Account to continue.', ['x' => config('app.name')]) }}</p>
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <x-honey recaptcha="forget_password" />

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
