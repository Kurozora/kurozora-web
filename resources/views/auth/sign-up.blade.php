<x-app-layout>
    <x-slot:title>
        {{ __('Sign Up') }}
    </x-slot:title>

    <x-slot:header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kurozora ID') }}
        </h2>
    </x-slot:header>

    <div class="flex flex-col justify-center w-screen h-full max-w-prose mx-auto pl-4 pr-4 py-6 sm:px-6">
        <div class="mb-5 text-center">
            <p class="text-2xl font-bold">{{ __('New to Kurozora?') }}</p>
            <p>{{ __('Create an account and join the community.') }}</p>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('sign-up') }}">
            @csrf
            <x-honey recaptcha="sign_up" />

            <section class="space-y-4" >
                <div>
                    <x-label for="username" value="{{ __('Username') }}" />
                    <x-input id="username" class="mt-1 block w-full" type="text" name="username" :value="old('username')" placeholder="{{ __('Pick a cool one') }} 🙉" required autofocus autocomplete="username" />
                </div>

                <div>
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" placeholder="{{ __('We all forget our passwords') }} 🙈" required />
                </div>

                <div>
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" class="mt-1 block w-full" type="password" name="password" placeholder="{{ __('Make it super secret') }} 🙊" required autocomplete="new-password" />
                </div>

                <div>
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                    <x-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" placeholder="{{ __('But keep it memorable') }} 🐵" required autocomplete="new-password" />
                </div>
            </section>

            <section class="mt-8 text-center">
                <p class="text-gray-500 text-sm">
                    {{ __('Your Kurozora ID information is used to allow you to sign in securely and access your data. Kurozora records certain usage data for security and support purposes.') }} <x-link href="{{ route('legal.privacy-policy') }}">{{ __('See how your data is managed.') }}</x-link>
                </p>
            </section>

            <section class="flex flex-col items-center justify-end gap-4 mt-8 text-center">
                <x-button>
                    {{ __('Join') }} 🤗
                </x-button>

                <p class="tracking-wide font-black">{{ __('———— or ————') }}</p>

                <x-link class="text-sm" href="{{ route('sign-in') }}">
                    {{ __('Already have an account? Let’s sign in') }} 🔥
                </x-link>
            </section>
        </form>
    </div>
</x-app-layout>
