<x-app-layout>
    <x-slot:title>
        {{ __('Verify Email') }}
    </x-slot:title>

    <x-slot:header>
        <h2 class="text-2xl font-bold">
            {{ __(':x Account', ['x' => config('app.name')]) }}
        </h2>
    </x-slot:header>

    <div class="flex flex-col justify-center w-screen h-full max-w-prose mx-auto pt-4 pb-6 pl-4 pr-4">
        <div class="text-center mb-5">
            <h1 class="text-2xl font-bold">{{ __('Thanks for signing up!') }}</h1>
            <p>{{ __('Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn’t receive the email, we will gladly send you another.') }}</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ __('A new verification link has been sent to the email address you provided during the sign up.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-honey recaptcha="verify_email_send" />

                <div>
                    <x-button type="submit">
                        {{ __('Resend Verification Email') }}
                    </x-button>
                </div>
            </form>

            <form method="POST" action="{{ route('sign-out') }}">
                @csrf

                <x-simple-button>
                    {{ __('Sign out') }}
                </x-simple-button>
            </form>
        </div>
    </div>
</x-app-layout>
