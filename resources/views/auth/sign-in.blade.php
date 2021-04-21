<x-app-layout>
    <x-slot name="title">
        {{ __('Sign In') }}
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora ID') }}
        </h2>
    </x-slot>

    <div class="max-w-prose w-screen my-auto mx-4 sm:mx-0 p-10 rounded-md shadow-lg">
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
                    <input id="remember_me" type="checkbox" class="h-4 w-4 rounded focus:ring-0 focus:ring-offset-0 text-orange-500 focus:border-orange-300" name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex flex-col items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                        {{ __('Forgot your password? Let\'s reset it 📧') }}
                    </a>

                    <p class="mt-4 tracking-wide font-black">{{ __('————— or —————') }}</p>
                @endif

                <a class="mt-4 underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('sign-up') }}">
                    {{ __('New to Kurozora? Join us 🔥') }}
                </a>

                <x-button class="mt-4">
                    {{ __('Open sesame 👐') }}
                </x-button>
            </div>
        </form>
    </div>
</x-app-layout>
