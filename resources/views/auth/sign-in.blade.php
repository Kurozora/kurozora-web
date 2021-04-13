<x-app-layout>
    <x-slot name="title">
        {{ __('Sign In') }}
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora ID') }}
        </h2>
    </x-slot>

    <div class="text-center mb-5">
        <p class="text-2xl font-bold">{{ __('Welcome to Kurozora!') }}</p>
        <p>{{ __('Sign in with your Kurozora ID to use the library and other Kurozora services.') }}</p>
    </div>

    <x-validation-errors class="mb-4" />

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('sign-in') }}">
        @csrf

        <div>
            <x-label for="email" value="{{ __('Kurozora ID') }}" />
            <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="{{ __('The cool Kurozora ID you claimed') }} 🙌" required autofocus />
        </div>

        <div class="mt-4">
            <x-label for="password" value="{{ __('Password') }}" />
            <x-input id="password" class="block mt-1 w-full" type="password" name="password" placeholder="{{ __('Your super secret password') }} 👀" required autocomplete="current-password" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="flex items-center">
                <input id="remember_me" type="checkbox" class="form-checkbox" name="remember">
                <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                    {{ __('Forgot your password? Let\'s reset it 📧') }}
                </a>

                <p>{{ __('or') }}</p>
            @endif

            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('sign-up') }}">
                {{ __('New to Kurozora? Join us 🔥') }}
            </a>

            <x-button class="ml-4">
                {{ __('Open sesame 👐') }}
            </x-button>
        </div>
    </form>
</x-app-layout>
